<?php
/**
 * The inline SVG icon set.
 *
 * Two families. Interface icons are drawn on a 24 unit grid with a 2 unit
 * stroke, so every glyph reads at the same weight at 18 pixels. Brand marks are
 * filled shapes, because that is how the networks' own marks are drawn and a
 * stroked outline of one is unrecognisable at 18 pixels.
 *
 * Inline rather than a sprite file: the bar renders at most four glyphs, and a
 * request for a sprite costs more than the markup it saves.
 *
 * The brand marks are simplified geometry drawn for this plugin, not the
 * networks' own logo files. They identify a link's destination, which is
 * nominative use, and nothing here is redistributed under anyone's brand
 * licence.
 *
 * @package MobileBottomBar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Supplies icon markup by name.
 */
class MBBar_Icons {

	/**
	 * Interface icons: stroked, on a 24 unit grid.
	 *
	 * @return array
	 */
	private static function outline() {
		return array(
			'phone'      => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2 4.2 2 2 0 0 1 4 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.1a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2Z"/>',
			'message'    => '<path d="M21 11.5a8.4 8.4 0 0 1-9 8.4 8.5 8.5 0 0 1-3.9-.9L3 20.5l1.5-5.1a8.5 8.5 0 0 1-.9-3.9 8.4 8.4 0 0 1 8.4-9 8.4 8.4 0 0 1 9 9Z"/>',
			'mail'       => '<path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z"/><path d="m22 6-10 7L2 6"/>',
			'link'       => '<path d="M10 13a5 5 0 0 0 7.5.5l3-3a5 5 0 0 0-7-7l-1.7 1.7"/><path d="M14 11a5 5 0 0 0-7.5-.5l-3 3a5 5 0 0 0 7 7L12.3 19"/>',
			'arrow-down' => '<path d="M12 5v14"/><path d="m19 12-7 7-7-7"/>',
			'arrow-up'   => '<path d="M12 19V5"/><path d="m5 12 7-7 7 7"/>',
			'file'       => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/>',
			'map-pin'    => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
			'share'      => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 13.5 6.8 4"/><path d="m15.4 6.5-6.8 4"/>',
			'star'       => '<path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.2-6.2 3.2L7 14.2l-5-4.9 6.9-1Z"/>',
			'heart'      => '<path d="M20.8 5.6a5 5 0 0 0-7.1 0L12 7.3l-1.7-1.7a5 5 0 0 0-7.1 7.1l8.8 8.8 8.8-8.8a5 5 0 0 0 0-7.1Z"/>',
			'cart'       => '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/>',
			'search'     => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
			'user'       => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
			'calendar'   => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
			'clock'      => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
			'home'       => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/><path d="M9 22V12h6v10"/>',
			'menu'       => '<path d="M3 12h18M3 6h18M3 18h18"/>',
			'info'       => '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>',
			'download'   => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/>',
			'gift'       => '<rect x="2" y="7" width="20" height="5" rx="1"/><path d="M12 22V7"/><path d="M20 12v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-8"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7Z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7Z"/>',
			'chat'       => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2Z"/>',
			'camera'     => '<path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2Z"/><circle cx="12" cy="13" r="4"/>',
			'globe'      => '<circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15 15 0 0 1 0 20 15 15 0 0 1 0-20Z"/>',
		);
	}

	/**
	 * Brand marks: filled, on a 24 unit grid.
	 *
	 * @return array
	 */
	private static function brands() {
		return array(
			'facebook'    => '<path d="M22 12a10 10 0 1 0-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.4v7A10 10 0 0 0 22 12Z"/>',
			'instagram'   => '<path d="M12 2.2c3.2 0 3.6 0 4.9.1 1.2 0 1.8.3 2.2.4.6.2 1 .5 1.4.9.4.4.7.8.9 1.4.2.4.4 1 .4 2.2.1 1.3.1 1.7.1 4.9s0 3.6-.1 4.9c0 1.2-.3 1.8-.4 2.2-.2.6-.5 1-.9 1.4-.4.4-.8.7-1.4.9-.4.2-1 .4-2.2.4-1.3.1-1.7.1-4.9.1s-3.6 0-4.9-.1c-1.2 0-1.8-.3-2.2-.4-.6-.2-1-.5-1.4-.9-.4-.4-.7-.8-.9-1.4-.2-.4-.4-1-.4-2.2C2.2 15.6 2.2 15.2 2.2 12s0-3.6.1-4.9c0-1.2.3-1.8.4-2.2.2-.6.5-1 .9-1.4.4-.4.8-.7 1.4-.9.4-.2 1-.4 2.2-.4 1.3-.1 1.7-.1 4.9-.1Zm0 5.8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm0 6.6a2.6 2.6 0 1 1 0-5.2 2.6 2.6 0 0 1 0 5.2Zm5.1-6.7a.9.9 0 1 1-1.9 0 .9.9 0 0 1 1.9 0Z"/>',
			'x'           => '<path d="M17.5 3h3.1l-6.8 7.7L21.8 21h-6.2l-4.9-6.4L5.1 21H2l7.3-8.3L2.4 3h6.4l4.4 5.8ZM16.4 19.2h1.7L7.7 4.7H5.9Z"/>',
			'linkedin'    => '<path d="M20.4 2H3.6A1.6 1.6 0 0 0 2 3.6v16.8A1.6 1.6 0 0 0 3.6 22h16.8a1.6 1.6 0 0 0 1.6-1.6V3.6A1.6 1.6 0 0 0 20.4 2ZM8 18.3H5.1V9.5H8ZM6.5 8.3a1.7 1.7 0 1 1 0-3.4 1.7 1.7 0 0 1 0 3.4Zm12.4 10H16v-4.3c0-1 0-2.3-1.4-2.3s-1.7 1.1-1.7 2.3v4.3h-2.8V9.5h2.7v1.2h.1a3 3 0 0 1 2.7-1.5c2.9 0 3.4 1.9 3.4 4.4Z"/>',
			'youtube'     => '<path d="M21.6 7.2a2.5 2.5 0 0 0-1.8-1.8C18.2 5 12 5 12 5s-6.2 0-7.8.4a2.5 2.5 0 0 0-1.8 1.8A26 26 0 0 0 2 12a26 26 0 0 0 .4 4.8 2.5 2.5 0 0 0 1.8 1.8C5.8 19 12 19 12 19s6.2 0 7.8-.4a2.5 2.5 0 0 0 1.8-1.8A26 26 0 0 0 22 12a26 26 0 0 0-.4-4.8ZM10 15V9l5.2 3Z"/>',
			'tiktok'      => '<path d="M16.6 5.8a4.8 4.8 0 0 1-1-2.8h-3.3v13.2a2.7 2.7 0 1 1-1.9-2.6V10a6 6 0 1 0 5.2 5.9V9.4a8.1 8.1 0 0 0 4.4 1.3V7.4a4.8 4.8 0 0 1-3.4-1.6Z"/>',
			'pinterest'   => '<path d="M12 2a10 10 0 0 0-3.6 19.3c-.1-.8-.2-2 0-2.9l1.2-5.1s-.3-.6-.3-1.5c0-1.4.8-2.5 1.8-2.5.9 0 1.3.6 1.3 1.4 0 .9-.5 2.2-.8 3.4-.3 1 .5 1.9 1.5 1.9 1.8 0 3.2-1.9 3.2-4.7 0-2.4-1.8-4.1-4.3-4.1a4.5 4.5 0 0 0-4.7 4.5c0 .9.3 1.8.8 2.3.1.1.1.2.1.3l-.3 1.1c0 .2-.1.2-.3.1-1.3-.6-2.1-2.5-2.1-4C5.5 8.2 7.8 5.2 12.3 5.2c3.6 0 6.4 2.6 6.4 6 0 3.6-2.2 6.4-5.4 6.4-1 0-2-.5-2.4-1.2l-.6 2.5c-.2.9-.8 2-1.2 2.6A10 10 0 1 0 12 2Z"/>',
			'telegram'    => '<path d="M21.9 5.3 18.6 20c-.2 1.1-.9 1.4-1.8.9l-5-3.7-2.4 2.3c-.3.3-.5.5-1 .5l.4-5.1 9.3-8.4c.4-.4-.1-.6-.6-.2L6 12.5 1.1 11c-1.1-.3-1.1-1 .2-1.5l19.2-7.4c.9-.3 1.7.2 1.4 1.2Z"/>',
			'whatsapp'    => '<path d="M17.5 14.4c-.3-.2-1.7-.9-2-1-.3-.1-.5-.2-.7.1l-.9 1.1c-.2.2-.3.2-.6.1a8.2 8.2 0 0 1-4-3.5c-.3-.5.3-.5.8-1.5.1-.2 0-.4 0-.5L9 6.9c-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1.1 1-1.1 2.6s1.1 3 1.3 3.2c.2.2 2.3 3.5 5.6 4.9 2.1.9 2.9.9 3.9.8.6-.1 1.7-.7 2-1.4.2-.7.2-1.3.2-1.4-.1-.1-.3-.2-.6-.3ZM12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm0 18.3a8.3 8.3 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.3 8.3 0 1 1 12 20.3Z"/>',
			'snapchat'    => '<path d="M12 2c2.7 0 4.6 2 4.7 4.7v2.1c.6.2 1.1-.3 1.6-.3.4 0 .9.3.9.8s-.6.7-1.2 1c-.5.2-1 .4-1 .8 0 1 2.4 3.5 4.1 3.8.4.1.6.3.6.6 0 .8-1.9 1.2-2.4 1.3-.2.4-.2 1.2-.7 1.2-.7 0-1.3-.3-2.2-.1-.9.2-1.8 1.8-3.4 1.8s-2.5-1.6-3.4-1.8c-.9-.2-1.5.1-2.2.1-.5 0-.5-.8-.7-1.2-.5-.1-2.4-.5-2.4-1.3 0-.3.2-.5.6-.6 1.7-.3 4.1-2.8 4.1-3.8 0-.4-.5-.6-1-.8-.6-.3-1.2-.5-1.2-1s.5-.8.9-.8c.5 0 1 .5 1.6.3V6.7C7.4 4 9.3 2 12 2Z"/>',
			'reddit'      => '<path d="M22 12c0-1.2-1-2.2-2.2-2.2-.6 0-1.1.2-1.5.6a10.8 10.8 0 0 0-5.8-1.8l1-4.6 3.2.7a1.6 1.6 0 1 0 .2-1.5l-3.6-.8c-.2 0-.4.1-.4.3l-1.1 5.1a10.8 10.8 0 0 0-5.9 1.8 2.2 2.2 0 1 0-2.4 3.6 4.3 4.3 0 0 0 0 .7c0 3.5 4.1 6.4 9.1 6.4s9.1-2.9 9.1-6.4a4.3 4.3 0 0 0 0-.7c.7-.4 1.3-1.1 1.3-2.2ZM7.6 13.6a1.6 1.6 0 1 1 3.2 0 1.6 1.6 0 0 1-3.2 0Zm8.9 4.2a5.9 5.9 0 0 1-4.5 1.4 5.9 5.9 0 0 1-4.5-1.4.6.6 0 0 1 .8-.8 4.8 4.8 0 0 0 3.7 1 4.8 4.8 0 0 0 3.7-1 .6.6 0 0 1 .8.8Zm-.3-2.6a1.6 1.6 0 1 1 0-3.2 1.6 1.6 0 0 1 0 3.2Z"/>',
			'threads'     => '<path d="M17.1 11.2a6.5 6.5 0 0 0-.3-.1c-.2-3-1.8-4.7-4.5-4.7a4.6 4.6 0 0 0-4 2l1.5 1a2.8 2.8 0 0 1 2.5-1.2c1.5 0 2.5.8 2.7 2.3a10.2 10.2 0 0 0-2.5-.2c-2.6.1-4.2 1.6-4.1 3.6a3.3 3.3 0 0 0 3.6 3.1 3.9 3.9 0 0 0 3.6-2.1 6 6 0 0 0 .5-1.6c.9.6 1.4 1.4 1.4 2.5 0 1.9-1.9 4-5.5 4-3.9 0-5.9-2.6-5.9-6.8S7.9 5.3 11.9 5.3c2.9 0 4.7 1.1 5.6 3.3l1.7-.6c-1.2-2.9-3.7-4.5-7.3-4.5-5 0-7.8 3.3-7.8 8.5s2.8 8.5 7.8 8.5c4.5 0 7.3-2.8 7.3-5.9 0-1.8-.9-3.2-2.1-3.4Zm-5.2 4c-.9 0-1.8-.4-1.8-1.4 0-.9.9-1.7 2.5-1.7a8.5 8.5 0 0 1 2.2.3c-.2 2-1.3 2.8-2.9 2.8Z"/>',
			'discord'     => '<path d="M19.5 5.7a17 17 0 0 0-4.2-1.3l-.2.4a15.7 15.7 0 0 0-6.2 0l-.2-.4a17 17 0 0 0-4.2 1.3A18 18 0 0 0 2.1 18a17 17 0 0 0 5.1 2.6l1-1.7a11 11 0 0 1-1.7-.8l.4-.3a12.2 12.2 0 0 0 10.4 0l.4.3a11 11 0 0 1-1.7.8l1 1.7A17 17 0 0 0 22 18a18 18 0 0 0-2.5-12.3ZM8.7 15.3c-1 0-1.8-.9-1.8-2s.8-2 1.8-2 1.8.9 1.8 2-.8 2-1.8 2Zm6.6 0c-1 0-1.8-.9-1.8-2s.8-2 1.8-2 1.8.9 1.8 2-.8 2-1.8 2Z"/>',
			'twitch'      => '<path d="M4.3 2 2 6.4v14h5v3h3l3-3h4l5-5V2Zm15.4 12.9-3 3h-4.6l-3 3v-3H5.8V3.6h13.9ZM16.7 7v5h-1.9V7Zm-5 0v5H9.8V7Z"/>',
			'github'      => '<path d="M12 2a10 10 0 0 0-3.2 19.5c.5.1.7-.2.7-.5v-1.8c-2.8.6-3.4-1.3-3.4-1.3-.5-1.2-1.1-1.5-1.1-1.5-.9-.6.1-.6.1-.6 1 .1 1.6 1 1.6 1 .9 1.5 2.3 1.1 2.9.8.1-.6.4-1.1.6-1.3-2.2-.3-4.6-1.1-4.6-5 0-1.1.4-2 1-2.7 0-.3-.4-1.3.1-2.7 0 0 .8-.3 2.7 1a9.4 9.4 0 0 1 5 0c1.9-1.3 2.7-1 2.7-1 .5 1.4.2 2.4.1 2.7.6.7 1 1.6 1 2.7 0 3.9-2.4 4.7-4.6 5 .4.3.7.9.7 1.9v2.8c0 .3.2.6.7.5A10 10 0 0 0 12 2Z"/>',
			'dribbble'    => '<path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm6.6 4.6a8.4 8.4 0 0 1 1.9 5.3 20 20 0 0 0-5.9-.3 26 26 0 0 0-.8-1.8 11.6 11.6 0 0 0 4.8-3.2ZM12 3.5a8.4 8.4 0 0 1 5.6 2.1 9.9 9.9 0 0 1-4.4 2.9A44 44 0 0 0 10 4a8.5 8.5 0 0 1 2-.5ZM8.3 4.6a52 52 0 0 1 3.2 4.5 32 32 0 0 1-7.9 1 8.6 8.6 0 0 1 4.7-5.5ZM3.5 12v-.3a35 35 0 0 0 8.8-1.2l.7 1.4a13.6 13.6 0 0 0-6.6 5.5A8.4 8.4 0 0 1 3.5 12Zm4.2 6.6a12 12 0 0 1 5.9-5.1 35 35 0 0 1 1.8 6.4 8.4 8.4 0 0 1-7.7-1.3Zm9.3.5a37 37 0 0 0-1.6-6 15 15 0 0 1 5.1.3 8.5 8.5 0 0 1-3.5 5.7Z"/>',
			'behance'     => '<path d="M9.3 5.5c.7 0 1.3.1 1.9.2.6.1 1 .3 1.4.6.4.3.7.6.9 1.1.2.4.3 1 .3 1.6 0 .7-.2 1.3-.5 1.8-.3.5-.8.9-1.4 1.2.9.2 1.5.7 1.9 1.3.4.6.6 1.4.6 2.2 0 .7-.1 1.3-.4 1.8-.3.5-.6.9-1.1 1.2-.5.3-1 .6-1.6.7-.6.2-1.2.2-1.8.2H2V5.5Zm-.4 5.2c.6 0 1-.1 1.4-.4.4-.3.5-.7.5-1.3 0-.3-.1-.6-.2-.8a1 1 0 0 0-.5-.5 1.7 1.7 0 0 0-.7-.2H4.9v3.2Zm.2 5.5c.3 0 .6 0 .9-.1a2 2 0 0 0 .7-.3c.2-.1.4-.3.5-.6.1-.2.2-.6.2-1 0-.7-.2-1.2-.6-1.5-.4-.3-1-.5-1.6-.5H4.9v4ZM17.3 16.1c.4.4 1 .6 1.7.6.5 0 1-.1 1.4-.4.4-.3.6-.6.7-.9h2.2c-.4 1.1-.9 1.9-1.6 2.4-.7.5-1.6.7-2.7.7-.7 0-1.4-.1-2-.4a4.2 4.2 0 0 1-2.4-2.6 5.7 5.7 0 0 1 0-3.9 4.4 4.4 0 0 1 2.4-2.6c.6-.3 1.2-.4 2-.4.8 0 1.5.2 2.1.5.6.3 1.1.7 1.5 1.2.4.5.7 1.1.8 1.8.2.6.2 1.3.2 2h-6.6c0 .8.3 1.6.7 2Zm3-5.2c-.3-.4-.8-.6-1.5-.6-.4 0-.8.1-1.1.2a2 2 0 0 0-1.1 1.3c0 .2-.1.4-.1.6h4c-.1-.7-.3-1.2-.6-1.5ZM15.9 6.5h5v1.4h-5Z"/>',
			'vimeo'       => '<path d="M22 7.4c-.1 2.1-1.6 5-4.4 8.7-2.9 3.9-5.4 5.8-7.4 5.8-1.2 0-2.3-1.2-3.1-3.5-.6-2.1-1.2-4.2-1.7-6.3-.7-2.3-1.4-3.5-2.2-3.5-.2 0-.8.4-1.8 1.1L.3 8.3c1.2-1 2.3-2 3.5-3.1 1.5-1.3 2.7-2 3.5-2.1 1.8-.2 2.9 1.1 3.3 3.7.5 2.9.8 4.7.9 5.4.5 2.1 1 3.2 1.5 3.2.4 0 1.1-.7 2-2a7.9 7.9 0 0 0 1.7-3.1c.2-1.3-.4-2-1.7-2-.6 0-1.2.1-1.9.4C14.5 3.9 16.6 1.7 19.4 1.8c2 .1 3 1.5 2.6 5.6Z"/>',
			'spotify'     => '<path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm4.6 14.4a.6.6 0 0 1-.9.2 12.7 12.7 0 0 0-6.2-1.5 15 15 0 0 0-2.9.3.6.6 0 1 1-.3-1.2 16 16 0 0 1 3.2-.4c2.5 0 4.9.6 6.9 1.7.3.2.4.6.2.9Zm1.2-2.8a.8.8 0 0 1-1 .3 15.5 15.5 0 0 0-7.5-1.8 18 18 0 0 0-3.5.4.8.8 0 0 1-.4-1.5 19 19 0 0 1 3.9-.4c3.1 0 6 .7 8.3 2 .3.2.4.7.2 1Zm.1-2.9a18.6 18.6 0 0 0-8.7-2.1 22 22 0 0 0-4.2.4.9.9 0 1 1-.4-1.7 24 24 0 0 1 4.6-.5c3.6 0 7 .8 9.7 2.3a.9.9 0 1 1-1 1.6Z"/>',
			'tripadvisor' => '<path d="M12 6.2c-2.6 0-5 .7-7 2H1l1.5 1.7a4.6 4.6 0 1 0 7.7 3.4l1.8 2.1 1.8-2.1a4.6 4.6 0 1 0 7.7-3.4L23 8.2h-4c-2-1.3-4.4-2-7-2Zm-5.4 9.9a2.8 2.8 0 1 1 0-5.6 2.8 2.8 0 0 1 0 5.6Zm0-1.4a1.4 1.4 0 1 0 0-2.8 1.4 1.4 0 0 0 0 2.8Zm10.8 1.4a2.8 2.8 0 1 1 0-5.6 2.8 2.8 0 0 1 0 5.6Zm0-1.4a1.4 1.4 0 1 0 0-2.8 1.4 1.4 0 0 0 0 2.8Z"/>',
			'yelp'        => '<path d="M10 12.5 4.7 10c-.6-.3-.8-1.1-.4-1.7A8.9 8.9 0 0 1 8.4 5c.7-.2 1.4.3 1.4 1v6c0 .5-.4.8-.8.6Zm.7 2.8-3.5 4.2c-.4.5-1.2.5-1.6-.1a8.6 8.6 0 0 1-1.3-3.3c-.1-.7.4-1.3 1.1-1.3l4.6-.3c.7 0 1.1.7.7 1.2Zm2.5.3 2 5c.3.7-.2 1.4-.9 1.4a9 9 0 0 1-3.3-.7c-.6-.3-.8-1-.5-1.6l2.2-4.2c.3-.6 1.2-.5 1.5.1Zm1.7-3.1 5.4-1.2c.7-.2 1.3.4 1.3 1.1a8.9 8.9 0 0 1-.9 3.3c-.3.6-1.1.8-1.6.3l-3.9-2.8c-.5-.4-.4-1.2-.3-1.3Zm.9-3.1L20.4 5c.5-.5 1.3-.3 1.6.3a8.7 8.7 0 0 1 .9 3.2c.1.7-.5 1.3-1.2 1.2l-5.4-.8c-.6-.1-.9-.9-.5-1.4Z"/>',
			'google'      => '<path d="M21.8 12.2c0-.7-.1-1.4-.2-2H12v3.9h5.5a4.7 4.7 0 0 1-2 3.1v2.6h3.2a9.8 9.8 0 0 0 3.1-7.6Z"/><path d="M12 22c2.7 0 5-.9 6.7-2.4l-3.2-2.5a6 6 0 0 1-9-3.2H3.2v2.6A10 10 0 0 0 12 22Z"/><path d="M6.5 13.9a6 6 0 0 1 0-3.8V7.5H3.2a10 10 0 0 0 0 9Z"/><path d="M12 6a5.4 5.4 0 0 1 3.8 1.5l2.9-2.9A9.6 9.6 0 0 0 12 2a10 10 0 0 0-8.8 5.5l3.3 2.6A6 6 0 0 1 12 6Z"/>',
			'messenger'   => '<path d="M12 2C6.4 2 2 6.1 2 11.6c0 3.2 1.4 6 3.7 7.8v3.8l3.4-1.9c.9.3 1.9.4 2.9.4 5.6 0 10-4.1 10-9.6S17.6 2 12 2Zm1 12.9-2.6-2.7-5 2.7 5.5-5.8 2.6 2.7 4.9-2.7Z"/>',
			'viber'       => '<path d="M12 2C8.9 2 5 2.6 3.7 5.2c-1 2-1 7.6.5 10.4.4.8 1.1 1.5 1.9 2v3.2c0 .5.6.8 1 .5l2.6-2.3c.8.1 1.5.1 2.3.1 3.1 0 7-.6 8.3-3.2 1-2 1-7.6-.5-10.4C18.6 2.9 15.5 2 12 2Zm4.6 12.5c-.3.6-1.1 1.1-1.7 1.2-.4.1-.9.1-1.5-.1a13 13 0 0 1-7-6.1c-.4-.8-.6-1.5-.5-2 .1-.6.6-1.4 1.2-1.7.3-.1.6 0 .8.3l1 1.7c.2.3.1.7-.1.9l-.5.5c-.2.2-.2.4-.1.6a8 8 0 0 0 3.6 3.6c.2.1.4.1.6-.1l.5-.5c.2-.2.6-.3.9-.1l1.7 1c.3.2.4.5.3.8Z"/>',
			'line'        => '<path d="M22 10.4C22 5.9 17.5 2.2 12 2.2S2 5.9 2 10.4c0 4 3.6 7.4 8.4 8 .3.1.8.2.9.5.1.3.1.7 0 1l-.1.9c0 .3-.2 1 .9.6 1.1-.5 6-3.5 8.2-6 1.5-1.6 1.7-3.3 1.7-5ZM8.1 13.1H6.1c-.3 0-.5-.2-.5-.5V8.7c0-.3.2-.5.5-.5s.5.2.5.5v3.4h1.5c.3 0 .5.2.5.5s-.2.5-.5.5Zm2-.5c0 .3-.2.5-.5.5s-.5-.2-.5-.5V8.7c0-.3.2-.5.5-.5s.5.2.5.5Zm4.6 0c0 .2-.1.4-.4.5h-.2c-.2 0-.3-.1-.4-.2l-2-2.7v2.4c0 .3-.2.5-.5.5s-.5-.2-.5-.5V8.7c0-.2.1-.4.4-.5h.2c.1 0 .3.1.4.2l2 2.7V8.7c0-.3.2-.5.5-.5s.5.2.5.5Zm3.3-2.4c.3 0 .5.2.5.5s-.2.5-.5.5h-1.5v1h1.5c.3 0 .5.2.5.5s-.2.5-.5.5h-2c-.3 0-.5-.2-.5-.5V8.7c0-.3.2-.5.5-.5h2c.3 0 .5.2.5.5s-.2.5-.5.5h-1.5v1Z"/>',
			'tumblr'      => '<path d="M14.6 21.8c-3.4 0-5.9-1.7-5.9-5.9v-6H5.9V7.2c3-.8 4.3-3.4 4.5-5.2h2.8v4.7h3.4v3.2h-3.4v5.3c0 1.4.7 1.9 1.8 1.9h1.7v3.6c-.4.1-1.2.1-2.1.1Z"/>',
			'flickr'      => '<path d="M7 7a5 5 0 1 0 0 10A5 5 0 0 0 7 7Zm10 0a5 5 0 1 0 0 10 5 5 0 0 0 0-10Z"/>',
			'medium'      => '<path d="M13.5 12a6.8 6.8 0 1 1-13.5 0 6.8 6.8 0 0 1 13.5 0Zm7.4 0c0 3.5-1.5 6.4-3.4 6.4-1.9 0-3.4-2.9-3.4-6.4s1.5-6.4 3.4-6.4c1.9 0 3.4 2.9 3.4 6.4Zm3.1 0c0 3.2-.5 5.7-1.2 5.7-.7 0-1.2-2.6-1.2-5.7s.5-5.7 1.2-5.7c.7 0 1.2 2.6 1.2 5.7Z"/>',
			'soundcloud'  => '<path d="M1.2 13.2c-.1 0-.2.1-.2.2l-.2 1.6.2 1.6c0 .1.1.2.2.2s.2-.1.2-.2l.2-1.6-.2-1.6c0-.1-.1-.2-.2-.2Zm1.4-.7c-.1 0-.2.1-.2.2L2.2 15l.2 2.3c0 .1.1.2.2.2s.2-.1.2-.2l.2-2.3-.2-2.3c0-.1-.1-.2-.2-.2Zm1.5-.6c-.1 0-.2.1-.2.3L3.7 15l.2 2.5c0 .1.1.3.2.3s.2-.1.2-.3l.3-2.5-.3-2.8c0-.2-.1-.3-.2-.3Zm1.6-.4c-.2 0-.3.1-.3.3L5.2 15l.2 2.5c0 .2.1.3.3.3s.3-.1.3-.3l.3-2.5-.3-3.2c0-.2-.1-.3-.3-.3Zm1.7.2c-.2 0-.3.1-.3.3L6.9 15l.2 2.5c0 .2.1.3.3.3s.3-.1.3-.3l.2-2.5-.2-3c0-.2-.1-.3-.3-.3Zm1.7.4c-.2 0-.3.2-.3.3L8.5 15l.3 2.5c0 .2.1.3.3.3s.3-.1.3-.3l.3-2.5-.3-2.6c0-.2-.1-.3-.3-.3Zm1.8-1.6c-.2 0-.3.2-.3.4L10.3 15l.3 2.5c0 .2.1.4.3.4s.3-.2.3-.4l.3-2.5-.3-4.1c0-.2-.1-.4-.3-.4Zm1.8-.7c-.2 0-.4.2-.4.4L12.1 15l.2 2.4c0 .2.2.4.4.4s.4-.2.4-.4l.2-2.4-.2-4.8c0-.2-.2-.4-.4-.4Zm1.9 7.9c.2 0 .4-.2.4-.4l.2-2.3-.2-6.5c0-.2-.2-.4-.4-.4s-.4.2-.4.4L14 15l.2 2.3c0 .2.2.4.4.4Zm7.1-6.2c-.4 0-.8.1-1.1.2A5.7 5.7 0 0 0 15 7a5.5 5.5 0 0 0-2 .4c-.2.1-.3.2-.3.4v9.5c0 .2.2.4.4.4h8.6a2.9 2.9 0 0 0 0-5.7Z"/>',
			'quora'       => '<path d="M13.4 18.1a7.7 7.7 0 0 0 1.5-4.9c0-4.5-3.4-8-7.5-8S0 8.7 0 13.2s3.4 8 7.4 8c1 0 2-.2 2.9-.6l1.2 1.9h3.1l-2-3.1a5 5 0 0 0 .8-1.3Zm-6-1.2c-2 0-3.1-1.6-3.1-3.7s1.1-3.7 3.1-3.7 3.1 1.6 3.1 3.7c0 .8-.2 1.5-.5 2.1L8.6 13H5.4l2.2 3.5c-.1.1-.2.4-.2.4ZM24 15.5c-.9 0-1.2-.5-1.2-1.7V3.2h-3.5v11.7c0 2.8 1.6 4.5 4.2 4.5.2 0 .4 0 .5-.1Z"/>',
		);
	}

	/**
	 * Each network's own colour.
	 *
	 * Used only when the owner asks for brand colours. These identify a
	 * destination rather than reproduce a logo, which is why the marks are
	 * drawn here rather than shipped from anyone's brand kit.
	 *
	 * @return array
	 */
	public static function brand_colors() {
		return array(
			'facebook'    => '#1877f2',
			'instagram'   => '#e4405f',
			'x'           => '#000000',
			'linkedin'    => '#0a66c2',
			'youtube'     => '#ff0000',
			'tiktok'      => '#000000',
			'pinterest'   => '#bd081c',
			'telegram'    => '#26a5e4',
			'whatsapp'    => '#25d366',
			'snapchat'    => '#f7cb00',
			'reddit'      => '#ff4500',
			'threads'     => '#000000',
			'discord'     => '#5865f2',
			'twitch'      => '#9146ff',
			'github'      => '#181717',
			'dribbble'    => '#ea4c89',
			'behance'     => '#1769ff',
			'vimeo'       => '#1ab7ea',
			'spotify'     => '#1db954',
			'tripadvisor' => '#00a680',
			'yelp'        => '#d32323',
			'google'      => '#4285f4',
			'messenger'   => '#0084ff',
			'viber'       => '#7360f2',
			'line'        => '#06c755',
			'tumblr'      => '#36465d',
			'flickr'      => '#0063dc',
			'medium'      => '#000000',
			'soundcloud'  => '#ff5500',
			'quora'       => '#b92b27',
		);
	}

	/**
	 * One network's colour, or an empty string.
	 *
	 * @param string $name Icon name.
	 * @return string
	 */
	public static function brand_color( $name ) {
		$colors = self::brand_colors();

		return isset( $colors[ $name ] ) ? $colors[ $name ] : '';
	}

	/**
	 * Every icon name available, in both families.
	 *
	 * @return string[]
	 */
	public static function names() {
		return array_keys( self::all() );
	}

	/**
	 * Brand mark names only.
	 *
	 * @return string[]
	 */
	public static function brand_names() {
		return array_keys( self::brands() );
	}

	/**
	 * Every icon, keyed by name.
	 *
	 * @return array
	 */
	public static function all() {
		/**
		 * Filters the available icons.
		 *
		 * Values are SVG child elements on a 24 unit viewBox. They are output
		 * through wp_kses, so only shape elements and geometry attributes
		 * survive.
		 *
		 * @param array $icons Icon markup keyed by name.
		 */
		return apply_filters( 'mbbar_icons', array_merge( self::outline(), self::brands() ) );
	}

	/**
	 * Whether an icon is a filled brand mark rather than a stroked glyph.
	 *
	 * @param string $name Icon name.
	 * @return bool
	 */
	public static function is_brand( $name ) {
		return array_key_exists( $name, self::brands() );
	}

	/**
	 * One icon as a complete SVG element, already escaped.
	 *
	 * Decorative by definition: the accessible name comes from the item's label
	 * or its aria-label, so the glyph is hidden from assistive technology and
	 * removed from the tab order.
	 *
	 * @param string $name Icon name.
	 * @return string Empty string when the icon does not exist.
	 */
	public static function render( $name ) {
		$icons = self::all();

		if ( ! isset( $icons[ $name ] ) ) {
			return '';
		}

		$paint = self::is_brand( $name )
			? 'fill="currentColor"'
			: 'fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"';

		return '<svg class="mbbar__icon" viewBox="0 0 24 24" ' . $paint . ' aria-hidden="true" focusable="false">'
			. wp_kses( $icons[ $name ], self::allowed_svg() )
			. '</svg>';
	}

	/**
	 * Every icon as a hidden sprite of symbols.
	 *
	 * The settings screen's live preview builds items in the browser, and
	 * passing forty icons of markup through to a script would mean the script
	 * handling SVG source as strings. A sprite rendered here keeps every glyph
	 * server-escaped and lets the preview reference one by id.
	 *
	 * @return string
	 */
	public static function sprite() {
		$out = '<svg class="mbbar-sprite" aria-hidden="true" focusable="false" style="position:absolute;width:0;height:0;overflow:hidden">';

		foreach ( self::all() as $name => $markup ) {
			$paint = self::is_brand( $name )
				? 'fill="currentColor"'
				: 'fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"';

			$out .= '<symbol id="mbbar-i-' . esc_attr( $name ) . '" viewBox="0 0 24 24" ' . $paint . '>'
				. wp_kses( $markup, self::allowed_svg() )
				. '</symbol>';
		}

		return $out . '</svg>';
	}

	/**
	 * The SVG elements and attributes permitted inside an icon.
	 *
	 * The icon set is filterable, so this is what stops a filtered icon from
	 * carrying a script element or an event handler attribute.
	 *
	 * @return array
	 */
	private static function allowed_svg() {
		$geometry = array(
			'd'         => true,
			'cx'        => true,
			'cy'        => true,
			'r'         => true,
			'x'         => true,
			'y'         => true,
			'x1'        => true,
			'x2'        => true,
			'y1'        => true,
			'y2'        => true,
			'rx'        => true,
			'ry'        => true,
			'width'     => true,
			'height'    => true,
			'points'    => true,
			'fill'      => true,
			'fill-rule' => true,
		);

		return array(
			'path'     => $geometry,
			'circle'   => $geometry,
			'rect'     => $geometry,
			'line'     => $geometry,
			'polyline' => $geometry,
			'polygon'  => $geometry,
			'ellipse'  => $geometry,
			'g'        => array( 'fill' => true ),
		);
	}
}
