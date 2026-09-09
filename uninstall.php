<?php
/**
 * Remove what the plugin created.
 *
 * Honours the "keep my settings" checkbox: people deactivate to test a
 * conflict and are rightly furious to lose their configuration.
 *
 * @package DiceBar
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Delete this site's options unless the owner asked to keep them.
 *
 * @return void
 */
function dicebar_uninstall_site() {
	$settings = get_option( 'dicebar_settings' );

	if ( is_array( $settings ) && ! empty( $settings['keep_settings_on_delete'] ) ) {
		return;
	}

	delete_option( 'dicebar_settings' );
	delete_option( 'dicebar_version' );
}

if ( is_multisite() ) {
	foreach ( get_sites( array( 'fields' => 'ids' ) ) as $dicebar_site_id ) {
		switch_to_blog( $dicebar_site_id );
		dicebar_uninstall_site();
		restore_current_blog();
	}
} else {
	dicebar_uninstall_site();
}
