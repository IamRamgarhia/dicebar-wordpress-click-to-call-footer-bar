<?php
/**
 * Remove what the plugin created.
 *
 * Honours the "keep my settings" checkbox: people deactivate to test a
 * conflict and are rightly furious to lose their configuration.
 *
 * @package MobileBottomBar
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Delete this site's options unless the owner asked to keep them.
 *
 * @return void
 */
function mbbar_uninstall_site() {
	$settings = get_option( 'mbbar_settings' );

	if ( is_array( $settings ) && ! empty( $settings['keep_settings_on_delete'] ) ) {
		return;
	}

	delete_option( 'mbbar_settings' );
	delete_option( 'mbbar_version' );
}

if ( is_multisite() ) {
	foreach ( get_sites( array( 'fields' => 'ids' ) ) as $mbbar_site_id ) {
		switch_to_blog( $mbbar_site_id );
		mbbar_uninstall_site();
		restore_current_blog();
	}
} else {
	mbbar_uninstall_site();
}
