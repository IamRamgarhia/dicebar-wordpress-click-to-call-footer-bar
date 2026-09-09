<?php
/**
 * Remove what the plugin created.
 *
 * Honours the "keep my settings" checkbox: people deactivate to test a
 * conflict and are rightly furious to lose their configuration.
 *
 * @package TapBar
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Delete this site's options unless the owner asked to keep them.
 *
 * @return void
 */
function tbar_uninstall_site() {
	$settings = get_option( 'tbar_settings' );

	if ( is_array( $settings ) && ! empty( $settings['keep_settings_on_delete'] ) ) {
		return;
	}

	delete_option( 'tbar_settings' );
	delete_option( 'tbar_version' );
}

if ( is_multisite() ) {
	foreach ( get_sites( array( 'fields' => 'ids' ) ) as $tbar_site_id ) {
		switch_to_blog( $tbar_site_id );
		tbar_uninstall_site();
		restore_current_blog();
	}
} else {
	tbar_uninstall_site();
}
