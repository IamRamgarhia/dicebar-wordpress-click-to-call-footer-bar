<?php
/**
 * Tests for the plugin bootstrap.
 *
 * @package TapBar
 */

/**
 * Tests the constants, the PHP version guard, and the consistency between
 * the plugin header, the readme stable tag and the text domain.
 */
class BootstrapTest extends WP_UnitTestCase {

	/**
	 * Loads the admin plugin functions before each test.
	 *
	 * The get_plugin_data() function is an admin function and the test suite
	 * does not load wp-admin includes for us.
	 */
	public function set_up() {
		parent::set_up();

		// get_plugin_data() is an admin function and the test suite does not
		// load wp-admin includes for us.
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	/**
	 * The plugin defines its public constants.
	 */
	public function test_constants_are_defined() {
		$this->assertTrue( defined( 'TBAR_VERSION' ) );
		$this->assertTrue( defined( 'TBAR_MIN_PHP' ) );
		$this->assertTrue( defined( 'TBAR_FILE' ) );
		$this->assertTrue( defined( 'TBAR_PATH' ) );
		$this->assertTrue( defined( 'TBAR_URL' ) );
		$this->assertSame( 1, TBAR_SCHEMA_VERSION );
	}

	/**
	 * TBAR_PATH ends with a trailing slash and points at the plugin file.
	 */
	public function test_path_constant_has_trailing_slash() {
		$this->assertStringEndsWith( '/', TBAR_PATH );
		$this->assertFileExists( TBAR_PATH . 'tapbar-mobile-action-bar.php' );
	}

	/**
	 * The PHP guard rejects versions below the floor.
	 */
	public function test_php_guard_rejects_versions_below_the_floor() {
		$this->assertFalse( tbar_php_is_supported( '7.2.0' ) );
		$this->assertFalse( tbar_php_is_supported( '7.3.33' ) );
	}

	/**
	 * The PHP guard accepts the floor version and anything newer.
	 */
	public function test_php_guard_accepts_the_floor_and_above() {
		$this->assertTrue( tbar_php_is_supported( '7.4.0' ) );
		$this->assertTrue( tbar_php_is_supported( '8.3.0' ) );
	}

	/**
	 * The plugin header Version matches the TBAR_VERSION constant.
	 */
	public function test_version_header_matches_the_version_constant() {
		$data = get_plugin_data( TBAR_FILE, false, false );
		$this->assertSame( TBAR_VERSION, $data['Version'] );
	}

	/**
	 * The readme's Stable tag matches the TBAR_VERSION constant.
	 */
	public function test_readme_stable_tag_matches_the_version_constant() {
		$readme = file_get_contents( TBAR_PATH . 'readme.txt' );
		$this->assertSame( 1, preg_match( '/^Stable tag:\s*(\S+)/m', $readme, $m ) );
		$this->assertSame( TBAR_VERSION, $m[1] );
	}

	/**
	 * The plugin header Text Domain matches the plugin slug.
	 */
	public function test_text_domain_header_matches_the_slug() {
		$data = get_plugin_data( TBAR_FILE, false, false );
		$this->assertSame( 'tapbar-mobile-action-bar', $data['TextDomain'] );
	}
}
