<?php
/**
 * Remove what the plugin created.
 *
 * Honours the "keep my settings" checkbox: people deactivate to test a
 * conflict and are rightly furious to lose their configuration.
 *
 * @package FooterBar
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Delete this site's options unless the owner asked to keep them.
 *
 * @return void
 */
function fbar_uninstall_site() {
	$settings = get_option( 'fbar_settings' );

	if ( is_array( $settings ) && ! empty( $settings['keep_settings_on_delete'] ) ) {
		return;
	}

	delete_option( 'fbar_settings' );
	delete_option( 'fbar_version' );
}

if ( is_multisite() ) {
	foreach ( get_sites( array( 'fields' => 'ids' ) ) as $fbar_site_id ) {
		switch_to_blog( $fbar_site_id );
		fbar_uninstall_site();
		restore_current_blog();
	}
} else {
	fbar_uninstall_site();
}
