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

$tbar_slug    = 'tapbar-mobile-action-bar';
$tbar_archive = __DIR__ . '/../dist/' . $tbar_slug . '.zip';

if ( ! file_exists( $tbar_archive ) ) {
	WP_CLI::error( 'No archive at dist/' . $tbar_slug . '.zip. Run npm run build:zip first.' );
}

$tbar_zip    = new ZipArchive();
$tbar_opened = $tbar_zip->open( $tbar_archive );

if ( true !== $tbar_opened ) {
	WP_CLI::error( 'ZipArchive refused the file, code ' . $tbar_opened . '.' );
}

$tbar_separators = array();

// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- numFiles is PHP's own ZipArchive property.
for ( $tbar_i = 0; $tbar_i < $tbar_zip->numFiles; $tbar_i++ ) {
	$tbar_name = $tbar_zip->getNameIndex( $tbar_i );

	// chr( 92 ) is a backslash. Written this way because a literal one in a
	// string is exactly the character most likely to be mangled by whatever
	// writes this file.
	if ( false !== strpos( $tbar_name, chr( 92 ) ) ) {
		$tbar_separators[] = $tbar_name;
	}
}

if ( $tbar_separators ) {
	$tbar_zip->close();
	WP_CLI::error(
		"Entry names contain backslashes, which the ZIP spec forbids:\n  " .
		implode( "\n  ", $tbar_separators )
	);
}

$tbar_destination = sys_get_temp_dir() . '/tbar-verify-' . getmypid();

if ( ! $tbar_zip->extractTo( $tbar_destination ) ) {
	$tbar_zip->close();
	WP_CLI::error( 'Extraction failed.' );
}

$tbar_zip->close();

$tbar_main = $tbar_destination . '/' . $tbar_slug . '/' . $tbar_slug . '.php';

if ( ! file_exists( $tbar_main ) ) {
	WP_CLI::error( 'Extracted tree has no ' . $tbar_slug . '/' . $tbar_slug . '.php — this is the error WordPress reports.' );
}

$tbar_headers = get_file_data(
	$tbar_main,
	array(
		'Name'        => 'Plugin Name',
		'Version'     => 'Version',
		'TextDomain'  => 'Text Domain',
		'RequiresWP'  => 'Requires at least',
		'RequiresPHP' => 'Requires PHP',
	)
);

foreach ( array( 'Name', 'Version', 'TextDomain' ) as $tbar_required ) {
	if ( '' === $tbar_headers[ $tbar_required ] ) {
		WP_CLI::error( 'The main file has no ' . $tbar_required . ' header.' );
	}
}

if ( $tbar_headers['TextDomain'] !== $tbar_slug ) {
	WP_CLI::error( 'Text Domain is "' . $tbar_headers['TextDomain'] . '" but must equal the slug "' . $tbar_slug . '".' );
}

$tbar_readme = $tbar_destination . '/' . $tbar_slug . '/readme.txt';

if ( file_exists( $tbar_readme ) && preg_match( '/^Stable tag:\s*(\S+)/m', file_get_contents( $tbar_readme ), $tbar_m ) ) {
	if ( $tbar_m[1] !== $tbar_headers['Version'] ) {
		WP_CLI::error( 'readme.txt Stable tag ' . $tbar_m[1] . ' does not match header Version ' . $tbar_headers['Version'] . '.' );
	}
}

WP_CLI::success(
	sprintf(
		'Archive unpacks correctly. %s %s, text domain %s, needs WP %s and PHP %s.',
		$tbar_headers['Name'],
		$tbar_headers['Version'],
		$tbar_headers['TextDomain'],
		$tbar_headers['RequiresWP'],
		$tbar_headers['RequiresPHP']
	)
);
