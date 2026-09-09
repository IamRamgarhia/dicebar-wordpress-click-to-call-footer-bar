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
 * @package TapBar
 */

$slug    = 'tapbar-mobile-action-bar';
$archive = __DIR__ . '/../dist/' . $slug . '.zip';

if ( ! file_exists( $archive ) ) {
	WP_CLI::error( 'No archive at dist/' . $slug . '.zip. Run npm run build:zip first.' );
}

$zip    = new ZipArchive();
$opened = $zip->open( $archive );

if ( true !== $opened ) {
	WP_CLI::error( 'ZipArchive refused the file, code ' . $opened . '.' );
}

$separators = array();

for ( $i = 0; $i < $zip->numFiles; $i++ ) {
	$name = $zip->getNameIndex( $i );

	// chr( 92 ) is a backslash. Written this way because a literal one in a
	// string is exactly the character most likely to be mangled by whatever
	// writes this file.
	if ( false !== strpos( $name, chr( 92 ) ) ) {
		$separators[] = $name;
	}
}

if ( $separators ) {
	$zip->close();
	WP_CLI::error(
		"Entry names contain backslashes, which the ZIP spec forbids:\n  " .
		implode( "\n  ", $separators )
	);
}

$destination = sys_get_temp_dir() . '/tbar-verify-' . getmypid();

if ( ! $zip->extractTo( $destination ) ) {
	$zip->close();
	WP_CLI::error( 'Extraction failed.' );
}

$zip->close();

$main = $destination . '/' . $slug . '/' . $slug . '.php';

if ( ! file_exists( $main ) ) {
	WP_CLI::error( 'Extracted tree has no ' . $slug . '/' . $slug . '.php — this is the error WordPress reports.' );
}

$headers = get_file_data(
	$main,
	array(
		'Name'       => 'Plugin Name',
		'Version'    => 'Version',
		'TextDomain' => 'Text Domain',
		'RequiresWP' => 'Requires at least',
		'RequiresPHP' => 'Requires PHP',
	)
);

foreach ( array( 'Name', 'Version', 'TextDomain' ) as $required ) {
	if ( '' === $headers[ $required ] ) {
		WP_CLI::error( 'The main file has no ' . $required . ' header.' );
	}
}

if ( $headers['TextDomain'] !== $slug ) {
	WP_CLI::error( 'Text Domain is "' . $headers['TextDomain'] . '" but must equal the slug "' . $slug . '".' );
}

$readme = $destination . '/' . $slug . '/readme.txt';

if ( file_exists( $readme ) && preg_match( '/^Stable tag:\s*(\S+)/m', file_get_contents( $readme ), $m ) ) {
	if ( $m[1] !== $headers['Version'] ) {
		WP_CLI::error( 'readme.txt Stable tag ' . $m[1] . ' does not match header Version ' . $headers['Version'] . '.' );
	}
}

WP_CLI::success(
	sprintf(
		'Archive unpacks correctly. %s %s, text domain %s, needs WP %s and PHP %s.',
		$headers['Name'],
		$headers['Version'],
		$headers['TextDomain'],
		$headers['RequiresWP'],
		$headers['RequiresPHP']
	)
);
