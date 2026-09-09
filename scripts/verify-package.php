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
 * @package FooterBar
 */

$fbar_slug    = 'footer-bar-mobile-action-bar';
$fbar_archive = __DIR__ . '/../dist/' . $fbar_slug . '.zip';

if ( ! file_exists( $fbar_archive ) ) {
	WP_CLI::error( 'No archive at dist/' . $fbar_slug . '.zip. Run npm run build:zip first.' );
}

$fbar_zip    = new ZipArchive();
$fbar_opened = $fbar_zip->open( $fbar_archive );

if ( true !== $fbar_opened ) {
	WP_CLI::error( 'ZipArchive refused the file, code ' . $fbar_opened . '.' );
}

$fbar_separators = array();

// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- numFiles is PHP's own ZipArchive property.
for ( $fbar_i = 0; $fbar_i < $fbar_zip->numFiles; $fbar_i++ ) {
	$fbar_name = $fbar_zip->getNameIndex( $fbar_i );

	// chr( 92 ) is a backslash. Written this way because a literal one in a
	// string is exactly the character most likely to be mangled by whatever
	// writes this file.
	if ( false !== strpos( $fbar_name, chr( 92 ) ) ) {
		$fbar_separators[] = $fbar_name;
	}
}

if ( $fbar_separators ) {
	$fbar_zip->close();
	WP_CLI::error(
		"Entry names contain backslashes, which the ZIP spec forbids:\n  " .
		implode( "\n  ", $fbar_separators )
	);
}

$fbar_destination = sys_get_temp_dir() . '/fbar-verify-' . getmypid();

if ( ! $fbar_zip->extractTo( $fbar_destination ) ) {
	$fbar_zip->close();
	WP_CLI::error( 'Extraction failed.' );
}

$fbar_zip->close();

$fbar_main = $fbar_destination . '/' . $fbar_slug . '/' . $fbar_slug . '.php';

if ( ! file_exists( $fbar_main ) ) {
	WP_CLI::error( 'Extracted tree has no ' . $fbar_slug . '/' . $fbar_slug . '.php — this is the error WordPress reports.' );
}

$fbar_headers = get_file_data(
	$fbar_main,
	array(
		'Name'        => 'Plugin Name',
		'Version'     => 'Version',
		'TextDomain'  => 'Text Domain',
		'RequiresWP'  => 'Requires at least',
		'RequiresPHP' => 'Requires PHP',
	)
);

foreach ( array( 'Name', 'Version', 'TextDomain' ) as $fbar_required ) {
	if ( '' === $fbar_headers[ $fbar_required ] ) {
		WP_CLI::error( 'The main file has no ' . $fbar_required . ' header.' );
	}
}

if ( $fbar_headers['TextDomain'] !== $fbar_slug ) {
	WP_CLI::error( 'Text Domain is "' . $fbar_headers['TextDomain'] . '" but must equal the slug "' . $fbar_slug . '".' );
}

$fbar_readme = $fbar_destination . '/' . $fbar_slug . '/readme.txt';

if ( file_exists( $fbar_readme ) && preg_match( '/^Stable tag:\s*(\S+)/m', file_get_contents( $fbar_readme ), $fbar_m ) ) {
	if ( $fbar_m[1] !== $fbar_headers['Version'] ) {
		WP_CLI::error( 'readme.txt Stable tag ' . $fbar_m[1] . ' does not match header Version ' . $fbar_headers['Version'] . '.' );
	}
}

WP_CLI::success(
	sprintf(
		'Archive unpacks correctly. %s %s, text domain %s, needs WP %s and PHP %s.',
		$fbar_headers['Name'],
		$fbar_headers['Version'],
		$fbar_headers['TextDomain'],
		$fbar_headers['RequiresWP'],
		$fbar_headers['RequiresPHP']
	)
);
