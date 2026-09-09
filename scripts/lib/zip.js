/**
 * A minimal, spec-correct ZIP writer.
 *
 * This exists because PowerShell 5.1's Compress-Archive writes Windows path
 * separators into entry names. The ZIP specification (APPNOTE 4.4.17.1) requires
 * forward slashes, so an archive built that way extracts on Linux as a single
 * flat file literally named "plugin\file.php" instead of a directory. WordPress
 * then reports "The plugin file does not exist", which is a true statement about
 * a broken archive and tells you nothing about why.
 *
 * Writing the archive here removes the dependency on whatever packaging tool
 * happens to be installed, and makes the output byte-identical on Windows,
 * macOS and CI.
 *
 * @package DiceBar
 */

'use strict';

const zlib = require( 'zlib' );

const LOCAL_HEADER = 0x04034b50;
const CENTRAL_HEADER = 0x02014b50;
const END_OF_CENTRAL = 0x06054b50;
const DEFLATED = 8;
const STORED = 0;

/**
 * CRC-32 lookup table, built once.
 *
 * @type {Int32Array}
 */
const crcTable = ( () => {
	const table = new Int32Array( 256 );

	for ( let index = 0; index < 256; index++ ) {
		let value = index;

		for ( let bit = 0; bit < 8; bit++ ) {
			value = value & 1 ? 0xedb88320 ^ ( value >>> 1 ) : value >>> 1;
		}

		table[ index ] = value;
	}

	return table;
} )();

/**
 * CRC-32 of a buffer.
 *
 * Node exposes zlib.crc32 on recent versions; the table above covers the rest.
 *
 * @param {Buffer} buffer Bytes to checksum.
 * @return {number} Unsigned 32-bit checksum.
 */
const crc32 = ( buffer ) => {
	if ( typeof zlib.crc32 === 'function' ) {
		return zlib.crc32( buffer ) >>> 0;
	}

	let value = -1;

	for ( let index = 0; index < buffer.length; index++ ) {
		value = crcTable[ ( value ^ buffer[ index ] ) & 0xff ] ^ ( value >>> 8 );
	}

	return ( value ^ -1 ) >>> 0;
};

/**
 * A date as a DOS time and date pair.
 *
 * ZIP stores timestamps in the MS-DOS format: two-second resolution, and years
 * counted from 1980.
 *
 * @param {Date} date Timestamp.
 * @return {{time: number, date: number}} DOS encoded pair.
 */
const dosTimestamp = ( date ) => {
	const year = Math.max( date.getFullYear(), 1980 );

	return {
		time:
			( date.getHours() << 11 ) |
			( date.getMinutes() << 5 ) |
			( Math.floor( date.getSeconds() / 2 ) & 0x1f ),
		date: ( ( year - 1980 ) << 9 ) | ( ( date.getMonth() + 1 ) << 5 ) | date.getDate(),
	};
};

/**
 * Build a ZIP archive.
 *
 * Entry names are normalised to forward slashes, which is the whole point of
 * this module. A name ending in a slash is written as a directory entry.
 *
 * @param {Array<{name: string, data: Buffer|null, mode?: number, date?: Date}>} entries Files and directories, in order.
 * @return {Buffer} The archive.
 */
const build = ( entries ) => {
	const locals = [];
	const centrals = [];
	let offset = 0;

	for ( const entry of entries ) {
		const isDirectory = entry.name.endsWith( '/' );
		const name = Buffer.from( entry.name.replace( /\\/g, '/' ), 'utf8' );
		const raw = isDirectory ? Buffer.alloc( 0 ) : entry.data;
		const stamp = dosTimestamp( entry.date || new Date() );

		const method = isDirectory || raw.length === 0 ? STORED : DEFLATED;
		const body = method === DEFLATED ? zlib.deflateRawSync( raw, { level: 9 } ) : raw;
		const checksum = crc32( raw );

		const local = Buffer.alloc( 30 );
		local.writeUInt32LE( LOCAL_HEADER, 0 );
		local.writeUInt16LE( 20, 4 );
		local.writeUInt16LE( 0, 6 );
		local.writeUInt16LE( method, 8 );
		local.writeUInt16LE( stamp.time, 10 );
		local.writeUInt16LE( stamp.date, 12 );
		local.writeUInt32LE( checksum, 14 );
		local.writeUInt32LE( body.length, 18 );
		local.writeUInt32LE( raw.length, 22 );
		local.writeUInt16LE( name.length, 26 );
		local.writeUInt16LE( 0, 28 );

		locals.push( local, name, body );

		const central = Buffer.alloc( 46 );
		central.writeUInt32LE( CENTRAL_HEADER, 0 );
		// Version made by: 3 (Unix) in the high byte, so the permission bits below
		// are honoured by extractors that read them.
		central.writeUInt16LE( ( 3 << 8 ) | 20, 4 );
		central.writeUInt16LE( 20, 6 );
		central.writeUInt16LE( 0, 8 );
		central.writeUInt16LE( method, 10 );
		central.writeUInt16LE( stamp.time, 12 );
		central.writeUInt16LE( stamp.date, 14 );
		central.writeUInt32LE( checksum, 16 );
		central.writeUInt32LE( body.length, 20 );
		central.writeUInt32LE( raw.length, 24 );
		central.writeUInt16LE( name.length, 28 );
		central.writeUInt16LE( 0, 30 );
		central.writeUInt16LE( 0, 32 );
		central.writeUInt16LE( 0, 34 );
		central.writeUInt16LE( 0, 36 );

		const mode = entry.mode || ( isDirectory ? 0o755 : 0o644 );
		const external = ( ( ( isDirectory ? 0o040000 : 0o100000 ) | mode ) << 16 ) >>> 0;

		central.writeUInt32LE( isDirectory ? external | 0x10 : external, 38 );
		central.writeUInt32LE( offset, 42 );

		centrals.push( central, name );

		offset += local.length + name.length + body.length;
	}

	const centralBuffer = Buffer.concat( centrals );
	const end = Buffer.alloc( 22 );

	end.writeUInt32LE( END_OF_CENTRAL, 0 );
	end.writeUInt16LE( 0, 4 );
	end.writeUInt16LE( 0, 6 );
	end.writeUInt16LE( entries.length, 8 );
	end.writeUInt16LE( entries.length, 10 );
	end.writeUInt32LE( centralBuffer.length, 12 );
	end.writeUInt32LE( offset, 16 );
	end.writeUInt16LE( 0, 20 );

	return Buffer.concat( [ Buffer.concat( locals ), centralBuffer, end ] );
};

module.exports = { build, crc32 };
