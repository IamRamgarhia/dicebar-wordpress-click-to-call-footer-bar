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
 * @package MobileBottomBar
 */

$mbbar_slug    = 'mobile-bottom-bar';
$mbbar_archive = __DIR__ . '/../dist/' . $mbbar_slug . '.zip';

if ( ! file_exists( $mbbar_archive ) ) {
	WP_CLI::error( 'No archive at dist/' . $mbbar_slug . '.zip. Run npm run build:zip first.' );
}

$mbbar_zip    = new ZipArchive();
$mbbar_opened = $mbbar_zip->open( $mbbar_archive );

if ( true !== $mbbar_opened ) {
	WP_CLI::error( 'ZipArchive refused the file, code ' . $mbbar_opened . '.' );
}

$mbbar_separators = array();

// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- numFiles is PHP's own ZipArchive property.
for ( $mbbar_i = 0; $mbbar_i < $mbbar_zip->numFiles; $mbbar_i++ ) {
	$mbbar_name = $mbbar_zip->getNameIndex( $mbbar_i );

	// chr( 92 ) is a backslash. Written this way because a literal one in a
	// string is exactly the character most likely to be mangled by whatever
	// writes this file.
	if ( false !== strpos( $mbbar_name, chr( 92 ) ) ) {
		$mbbar_separators[] = $mbbar_name;
	}
}

if ( $mbbar_separators ) {
	$mbbar_zip->close();
	WP_CLI::error(
		"Entry names contain backslashes, which the ZIP spec forbids:\n  " .
		implode( "\n  ", $mbbar_separators )
	);
}

$mbbar_destination = sys_get_temp_dir() . '/mbbar-verify-' . getmypid();

if ( ! $mbbar_zip->extractTo( $mbbar_destination ) ) {
	$mbbar_zip->close();
	WP_CLI::error( 'Extraction failed.' );
}

$mbbar_zip->close();

$mbbar_main = $mbbar_destination . '/' . $mbbar_slug . '/' . $mbbar_slug . '.php';

if ( ! file_exists( $mbbar_main ) ) {
	WP_CLI::error( 'Extracted tree has no ' . $mbbar_slug . '/' . $mbbar_slug . '.php — this is the error WordPress reports.' );
}

$mbbar_headers = get_file_data(
	$mbbar_main,
	array(
		'Name'        => 'Plugin Name',
		'Version'     => 'Version',
		'TextDomain'  => 'Text Domain',
		'RequiresWP'  => 'Requires at least',
		'RequiresPHP' => 'Requires PHP',
	)
);

foreach ( array( 'Name', 'Version', 'TextDomain' ) as $mbbar_required ) {
	if ( '' === $mbbar_headers[ $mbbar_required ] ) {
		WP_CLI::error( 'The main file has no ' . $mbbar_required . ' header.' );
	}
}

if ( $mbbar_headers['TextDomain'] !== $mbbar_slug ) {
	WP_CLI::error( 'Text Domain is "' . $mbbar_headers['TextDomain'] . '" but must equal the slug "' . $mbbar_slug . '".' );
}

$mbbar_readme = $mbbar_destination . '/' . $mbbar_slug . '/readme.txt';

if ( file_exists( $mbbar_readme ) && preg_match( '/^Stable tag:\s*(\S+)/m', file_get_contents( $mbbar_readme ), $mbbar_m ) ) {
	if ( $mbbar_m[1] !== $mbbar_headers['Version'] ) {
		WP_CLI::error( 'readme.txt Stable tag ' . $mbbar_m[1] . ' does not match header Version ' . $mbbar_headers['Version'] . '.' );
	}
}

WP_CLI::success(
	sprintf(
		'Archive unpacks correctly. %s %s, text domain %s, needs WP %s and PHP %s.',
		$mbbar_headers['Name'],
		$mbbar_headers['Version'],
		$mbbar_headers['TextDomain'],
		$mbbar_headers['RequiresWP'],
		$mbbar_headers['RequiresPHP']
	)
);
