<?php
/**
 * Verify the built archive unpacks the way WordPress will unpack it.
 *
 * Run with: npm run verify:zip
 *
 * WordPress unpacks a plugin with unzip_file(), which uses PHP's ZipArchive.
 * Building the archive with a tool that writes Windows path separators produces
 * something every Windows extractor opens happily and PHP flattens into a
 * single file with a backslash in its name. The install then fails with
 * "The plugin file does not exist." This script reproduces that exact path so
 * the failure is caught here rather than in somebody's dashboard.
 *
 * @package DiceBar
 */

$dicebar_slug    = 'dicebar';
$dicebar_archive = __DIR__ . '/../dist/' . $dicebar_slug . '.zip';

if ( ! file_exists( $dicebar_archive ) ) {
	WP_CLI::error( 'No archive at dist/' . $dicebar_slug . '.zip. Run npm run build:zip first.' );
}

$dicebar_zip    = new ZipArchive();
$dicebar_opened = $dicebar_zip->open( $dicebar_archive );

if ( true !== $dicebar_opened ) {
	WP_CLI::error( 'ZipArchive refused the file, code ' . $dicebar_opened . '.' );
}

$dicebar_separators = array();

// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- numFiles is PHP's own ZipArchive property.
for ( $dicebar_i = 0; $dicebar_i < $dicebar_zip->numFiles; $dicebar_i++ ) {
	$dicebar_name = $dicebar_zip->getNameIndex( $dicebar_i );

	// chr( 92 ) is a backslash. Written this way because a literal one in a
	// string is exactly the character most likely to be mangled by whatever
	// writes this file.
	if ( false !== strpos( $dicebar_name, chr( 92 ) ) ) {
		$dicebar_separators[] = $dicebar_name;
	}
}

if ( $dicebar_separators ) {
	$dicebar_zip->close();
	WP_CLI::error(
		"Entry names contain backslashes, which the ZIP spec forbids:\n  " .
		implode( "\n  ", $dicebar_separators )
	);
}

$dicebar_destination = sys_get_temp_dir() . '/dicebar-verify-' . getmypid();

if ( ! $dicebar_zip->extractTo( $dicebar_destination ) ) {
	$dicebar_zip->close();
	WP_CLI::error( 'Extraction failed.' );
}

$dicebar_zip->close();

$dicebar_main = $dicebar_destination . '/' . $dicebar_slug . '/' . $dicebar_slug . '.php';

if ( ! file_exists( $dicebar_main ) ) {
	WP_CLI::error( 'Extracted tree has no ' . $dicebar_slug . '/' . $dicebar_slug . '.php — this is the error WordPress reports.' );
}

$dicebar_headers = get_file_data(
	$dicebar_main,
	array(
		'Name'        => 'Plugin Name',
		'Version'     => 'Version',
		'TextDomain'  => 'Text Domain',
		'RequiresWP'  => 'Requires at least',
		'RequiresPHP' => 'Requires PHP',
	)
);

foreach ( array( 'Name', 'Version', 'TextDomain' ) as $dicebar_required ) {
	if ( '' === $dicebar_headers[ $dicebar_required ] ) {
		WP_CLI::error( 'The main file has no ' . $dicebar_required . ' header.' );
	}
}

if ( $dicebar_headers['TextDomain'] !== $dicebar_slug ) {
	WP_CLI::error( 'Text Domain is "' . $dicebar_headers['TextDomain'] . '" but must equal the slug "' . $dicebar_slug . '".' );
}

$dicebar_readme = $dicebar_destination . '/' . $dicebar_slug . '/readme.txt';

if ( file_exists( $dicebar_readme ) && preg_match( '/^Stable tag:\s*(\S+)/m', file_get_contents( $dicebar_readme ), $dicebar_m ) ) {
	if ( $dicebar_m[1] !== $dicebar_headers['Version'] ) {
		WP_CLI::error( 'readme.txt Stable tag ' . $dicebar_m[1] . ' does not match header Version ' . $dicebar_headers['Version'] . '.' );
	}
}

WP_CLI::success(
	sprintf(
		'Archive unpacks correctly. %s %s, text domain %s, needs WP %s and PHP %s.',
		$dicebar_headers['Name'],
		$dicebar_headers['Version'],
		$dicebar_headers['TextDomain'],
		$dicebar_headers['RequiresWP'],
		$dicebar_headers['RequiresPHP']
	)
);
