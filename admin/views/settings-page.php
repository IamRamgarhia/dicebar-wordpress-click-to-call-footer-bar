<?php
/**
 * The settings screen markup.
 *
 * Template only: no queries, no logic beyond presentation. Variables come from
 * TBar_Admin::render_page().
 *
 * @package TapBar
 *
 * @var array $settings The configuration.
 * @var array $types    Registered item types.
 * @var array $icons    Available icon names.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tbar_type_choices = array();

foreach ( $types as $tbar_slug => $tbar_type ) {
	$tbar_type_choices[ $tbar_slug ] = $tbar_type['label'];
}

$tbar_icon_choices = array( '' => __( 'Default for this type', 'tapbar-mobile-action-bar' ) );

foreach ( $icons as $tbar_icon ) {
	$tbar_icon_choices[ $tbar_icon ] = $tbar_icon;
}
?>
<div class="wrap tbar-admin">
	<h1><?php esc_html_e( 'TapBar', 'tapbar-mobile-action-bar' ); ?></h1>

	<?php if ( isset( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Settings saved.', 'tapbar-mobile-action-bar' ); ?></p>
		</div>
	<?php endif; ?>

	<p class="tbar-admin__intro">
		<?php esc_html_e( 'A bar across the bottom of the screen on phones and tablets, holding whatever you put in it. Add items below, then look at your site on a phone.', 'tapbar-mobile-action-bar' ); ?>
	</p>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="tbar_save">
		<?php wp_nonce_field( 'tbar_save', 'tbar_nonce' ); ?>

		<h2 class="title"><?php esc_html_e( 'Items', 'tapbar-mobile-action-bar' ); ?></h2>
		<p class="description">
			<?php esc_html_e( 'Four is the most that fits. At 320 pixels wide a fifth item clips its label rather than shrinking.', 'tapbar-mobile-action-bar' ); ?>
		</p>

		<div id="tbar-items" class="tbar-items">
			<?php
			$tbar_rows = $settings['items'];

			if ( empty( $tbar_rows ) ) {
				$tbar_rows = array();
			}

			foreach ( $tbar_rows as $tbar_index => $tbar_item ) :
				?>
				<div class="tbar-item" data-index="<?php echo esc_attr( $tbar_index ); ?>">
					<div class="tbar-item__head">
						<span class="tbar-item__handle" aria-hidden="true">⋮⋮</span>
						<strong class="tbar-item__title">
							<?php echo esc_html( '' !== $tbar_item['label'] ? $tbar_item['label'] : $tbar_type_choices[ $tbar_item['type'] ] ); ?>
						</strong>
						<code class="tbar-item__id"><?php echo esc_html( $tbar_item['id'] ); ?></code>
						<button type="button" class="button-link tbar-item__remove">
							<?php esc_html_e( 'Remove', 'tapbar-mobile-action-bar' ); ?>
						</button>
					</div>

					<input type="hidden" name="tbar[items][<?php echo esc_attr( $tbar_index ); ?>][id]" value="<?php echo esc_attr( $tbar_item['id'] ); ?>">

					<div class="tbar-item__fields">
						<label>
							<span><?php esc_html_e( 'Type', 'tapbar-mobile-action-bar' ); ?></span>
							<?php TBar_Admin::select( 'tbar[items][' . $tbar_index . '][type]', $tbar_type_choices, $tbar_item['type'] ); ?>
						</label>

						<label>
							<span><?php esc_html_e( 'Label', 'tapbar-mobile-action-bar' ); ?></span>
							<input type="text" name="tbar[items][<?php echo esc_attr( $tbar_index ); ?>][label]" value="<?php echo esc_attr( $tbar_item['label'] ); ?>" placeholder="<?php echo esc_attr( $tbar_type_choices[ $tbar_item['type'] ] ); ?>">
						</label>

						<label>
							<span><?php echo esc_html( $types[ $tbar_item['type'] ]['value']['label'] ); ?></span>
							<input type="text" name="tbar[items][<?php echo esc_attr( $tbar_index ); ?>][value]" value="<?php echo esc_attr( (string) $tbar_item['value'] ); ?>">
						</label>

						<label>
							<span><?php esc_html_e( 'Icon', 'tapbar-mobile-action-bar' ); ?></span>
							<?php TBar_Admin::select( 'tbar[items][' . $tbar_index . '][icon]', $tbar_icon_choices, $tbar_item['icon'] ); ?>
						</label>

						<?php foreach ( $types[ $tbar_item['type'] ]['extra'] as $tbar_key => $tbar_field ) : ?>
							<label>
								<span><?php echo esc_html( $tbar_field['label'] ); ?></span>
								<?php if ( 'boolean' === $tbar_field['kind'] ) : ?>
									<input type="checkbox" name="tbar[items][<?php echo esc_attr( $tbar_index ); ?>][extra][<?php echo esc_attr( $tbar_key ); ?>]" value="1" <?php checked( ! empty( $tbar_item['extra'][ $tbar_key ] ) ); ?>>
								<?php else : ?>
									<input type="text" name="tbar[items][<?php echo esc_attr( $tbar_index ); ?>][extra][<?php echo esc_attr( $tbar_key ); ?>]" value="<?php echo esc_attr( isset( $tbar_item['extra'][ $tbar_key ] ) ? (string) $tbar_item['extra'][ $tbar_key ] : '' ); ?>">
								<?php endif; ?>
							</label>
						<?php endforeach; ?>

						<label class="tbar-item__check">
							<input type="checkbox" name="tbar[items][<?php echo esc_attr( $tbar_index ); ?>][primary]" value="1" <?php checked( ! empty( $tbar_item['primary'] ) ); ?>>
							<span><?php esc_html_e( 'Make this the standout button', 'tapbar-mobile-action-bar' ); ?></span>
						</label>

						<label>
							<span><?php esc_html_e( 'Show on', 'tapbar-mobile-action-bar' ); ?></span>
							<?php
							TBar_Admin::select(
								'tbar[items][' . $tbar_index . '][show][devices]',
								array(
									'inherit'      => __( 'Whatever the bar does', 'tapbar-mobile-action-bar' ),
									'phone'        => __( 'Phones only', 'tapbar-mobile-action-bar' ),
									'phone_tablet' => __( 'Phones and tablets', 'tapbar-mobile-action-bar' ),
									'all'          => __( 'Every screen', 'tapbar-mobile-action-bar' ),
								),
								$tbar_item['show']['devices']
							);
							?>
						</label>

						<label>
							<span><?php esc_html_e( 'Show to', 'tapbar-mobile-action-bar' ); ?></span>
							<?php
							TBar_Admin::select(
								'tbar[items][' . $tbar_index . '][show][users]',
								array(
									'inherit' => __( 'Whatever the bar does', 'tapbar-mobile-action-bar' ),
									'all'     => __( 'Everyone', 'tapbar-mobile-action-bar' ),
									'in'      => __( 'Signed in visitors', 'tapbar-mobile-action-bar' ),
									'out'     => __( 'Signed out visitors', 'tapbar-mobile-action-bar' ),
								),
								$tbar_item['show']['users']
							);
							?>
						</label>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<p>
			<button type="button" class="button" id="tbar-add-item">
				<?php esc_html_e( 'Add an item', 'tapbar-mobile-action-bar' ); ?>
			</button>
			<span class="tbar-admin__hint" id="tbar-add-hint"></span>
		</p>

		<h2 class="title"><?php esc_html_e( 'Where it shows', 'tapbar-mobile-action-bar' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Turn the bar on', 'tapbar-mobile-action-bar' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="tbar[enabled]" value="1" <?php checked( ! empty( $settings['enabled'] ) ); ?>>
						<?php esc_html_e( 'Show the bar on the front end', 'tapbar-mobile-action-bar' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Screens', 'tapbar-mobile-action-bar' ); ?></th>
				<td>
					<?php
					TBar_Admin::select(
						'tbar[display][devices]',
						array(
							'phone'        => __( 'Phones only', 'tapbar-mobile-action-bar' ),
							'phone_tablet' => __( 'Phones and tablets', 'tapbar-mobile-action-bar' ),
							'all'          => __( 'Every screen', 'tapbar-mobile-action-bar' ),
						),
						$settings['display']['devices']
					);
					?>
					<p class="description">
						<?php esc_html_e( 'Phones end and tablets begin at the phone width below. Tablets end at the wide width.', 'tapbar-mobile-action-bar' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Widths', 'tapbar-mobile-action-bar' ); ?></th>
				<td>
					<label>
						<?php esc_html_e( 'Phone up to', 'tapbar-mobile-action-bar' ); ?>
						<input type="number" name="tbar[display][phone_max]" value="<?php echo esc_attr( $settings['display']['phone_max'] ); ?>" min="320" max="2560" class="small-text"> px
					</label>
					<label>
						<?php esc_html_e( 'Hide above', 'tapbar-mobile-action-bar' ); ?>
						<input type="number" name="tbar[display][breakpoint]" value="<?php echo esc_attr( $settings['display']['breakpoint'] ); ?>" min="320" max="2560" class="small-text"> px
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Pages', 'tapbar-mobile-action-bar' ); ?></th>
				<td>
					<?php
					TBar_Admin::select(
						'tbar[display][content][mode]',
						array(
							'all'     => __( 'Everywhere', 'tapbar-mobile-action-bar' ),
							'include' => __( 'Only on the pages listed below', 'tapbar-mobile-action-bar' ),
							'exclude' => __( 'Everywhere except the pages listed below', 'tapbar-mobile-action-bar' ),
						),
						$settings['display']['content']['mode']
					);
					?>
					<br>
					<input type="text" name="tbar[display][content][ids]" class="regular-text" value="<?php echo esc_attr( implode( ', ', $settings['display']['content']['ids'] ) ); ?>" placeholder="12, 48, 105">
					<p class="description"><?php esc_html_e( 'Post or page ids, separated by commas.', 'tapbar-mobile-action-bar' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Visitors', 'tapbar-mobile-action-bar' ); ?></th>
				<td>
					<?php
					TBar_Admin::select(
						'tbar[display][users]',
						array(
							'all' => __( 'Everyone', 'tapbar-mobile-action-bar' ),
							'in'  => __( 'Signed in only', 'tapbar-mobile-action-bar' ),
							'out' => __( 'Signed out only', 'tapbar-mobile-action-bar' ),
						),
						$settings['display']['users']
					);
					?>
				</td>
			</tr>
		</table>

		<h2 class="title"><?php esc_html_e( 'How it behaves', 'tapbar-mobile-action-bar' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Position', 'tapbar-mobile-action-bar' ); ?></th>
				<td>
					<?php
					TBar_Admin::select(
						'tbar[behaviour][position]',
						array(
							'bottom' => __( 'Bottom of the screen', 'tapbar-mobile-action-bar' ),
							'top'    => __( 'Top of the screen', 'tapbar-mobile-action-bar' ),
						),
						$settings['behaviour']['position']
					);
					?>
					<p class="description"><?php esc_html_e( 'Bottom is where thumbs are. Choose top only if your theme already puts something at the bottom.', 'tapbar-mobile-action-bar' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Appearance', 'tapbar-mobile-action-bar' ); ?></th>
				<td>
					<?php
					TBar_Admin::select(
						'tbar[behaviour][appear]',
						array(
							'always'    => __( 'Always visible', 'tapbar-mobile-action-bar' ),
							'scroll_up' => __( 'Hide when scrolling down', 'tapbar-mobile-action-bar' ),
						),
						$settings['behaviour']['appear']
					);
					?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Hide near', 'tapbar-mobile-action-bar' ); ?></th>
				<td>
					<input type="text" name="tbar[behaviour][hide_selector]" class="regular-text" value="<?php echo esc_attr( $settings['behaviour']['hide_selector'] ); ?>" placeholder="#contact">
					<p class="description"><?php esc_html_e( 'The bar steps out of the way while this element is on screen. A Call button is noise beside the contact section carrying the same number.', 'tapbar-mobile-action-bar' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Keep clear', 'tapbar-mobile-action-bar' ); ?></th>
				<td>
					<input type="number" name="tbar[behaviour][clearance]" value="<?php echo esc_attr( $settings['behaviour']['clearance'] ); ?>" min="0" max="400" class="small-text"> px
					<p class="description"><?php esc_html_e( 'Space left below the bar. Use it when a cookie banner sits in the same place.', 'tapbar-mobile-action-bar' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Stacking order', 'tapbar-mobile-action-bar' ); ?></th>
				<td>
					<input type="number" name="tbar[behaviour][z_index]" value="<?php echo esc_attr( $settings['behaviour']['z_index'] ); ?>" min="1" class="small-text">
					<p class="description"><?php esc_html_e( 'Lower this if the bar covers one of your theme\'s pop-ups. Raise it if something covers the bar.', 'tapbar-mobile-action-bar' ); ?></p>
				</td>
			</tr>
		</table>

		<h2 class="title"><?php esc_html_e( 'How it looks', 'tapbar-mobile-action-bar' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Labels', 'tapbar-mobile-action-bar' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="tbar[style][label][show]" value="1" <?php checked( ! empty( $settings['style']['label']['show'] ) ); ?>>
						<?php esc_html_e( 'Show a word under each icon', 'tapbar-mobile-action-bar' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'All of them or none. An unlabelled icon centres itself while a labelled one lifts to make room, so mixing them leaves one mark sitting low.', 'tapbar-mobile-action-bar' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Shape', 'tapbar-mobile-action-bar' ); ?></th>
				<td>
					<?php
					TBar_Admin::select(
						'tbar[style][layout]',
						array(
							'island' => __( 'Floating panel', 'tapbar-mobile-action-bar' ),
							'full'   => __( 'Edge to edge', 'tapbar-mobile-action-bar' ),
						),
						$settings['style']['layout']
					);

					TBar_Admin::select(
						'tbar[style][item][shape]',
						array(
							'plain'   => __( 'Plain buttons', 'tapbar-mobile-action-bar' ),
							'filled'  => __( 'Filled buttons', 'tapbar-mobile-action-bar' ),
							'outline' => __( 'Outlined buttons', 'tapbar-mobile-action-bar' ),
							'soft'    => __( 'Tinted buttons', 'tapbar-mobile-action-bar' ),
						),
						$settings['style']['item']['shape']
					);

					TBar_Admin::select(
						'tbar[style][shadow]',
						array(
							'none'   => __( 'No shadow', 'tapbar-mobile-action-bar' ),
							'soft'   => __( 'Soft shadow', 'tapbar-mobile-action-bar' ),
							'strong' => __( 'Strong shadow', 'tapbar-mobile-action-bar' ),
						),
						$settings['style']['shadow']
					);
					?>
					<br>
					<label>
						<input type="checkbox" name="tbar[style][blur]" value="1" <?php checked( ! empty( $settings['style']['blur'] ) ); ?>>
						<?php esc_html_e( 'Blur what is behind the bar', 'tapbar-mobile-action-bar' ); ?>
					</label>
					<label>
						<input type="checkbox" name="tbar[style][divider]" value="hairline" <?php checked( 'hairline', $settings['style']['divider'] ); ?>>
						<?php esc_html_e( 'Line between items', 'tapbar-mobile-action-bar' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Colours', 'tapbar-mobile-action-bar' ); ?></th>
				<td>
					<?php
					$tbar_colour_labels = array(
						'bar_bg'     => __( 'Bar background', 'tapbar-mobile-action-bar' ),
						'text'       => __( 'Text', 'tapbar-mobile-action-bar' ),
						'icon'       => __( 'Icons', 'tapbar-mobile-action-bar' ),
						'accent'     => __( 'Accent', 'tapbar-mobile-action-bar' ),
						'hover_bg'   => __( 'Pressed background', 'tapbar-mobile-action-bar' ),
						'hover_text' => __( 'Pressed text', 'tapbar-mobile-action-bar' ),
					);

					foreach ( $tbar_colour_labels as $tbar_token => $tbar_label ) :
						?>
						<label class="tbar-colour">
							<span><?php echo esc_html( $tbar_label ); ?></span>
							<input type="text" name="tbar[style][light][<?php echo esc_attr( $tbar_token ); ?>]" value="<?php echo esc_attr( $settings['style']['light'][ $tbar_token ] ); ?>" class="small-text">
						</label>
					<?php endforeach; ?>
					<p class="description"><?php esc_html_e( 'Hex values such as #0a84ff. Dark mode uses its own set, which follows the visitor\'s system preference.', 'tapbar-mobile-action-bar' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Dark mode', 'tapbar-mobile-action-bar' ); ?></th>
				<td>
					<?php
					TBar_Admin::select(
						'tbar[style][scheme]',
						array(
							'system' => __( 'Follow the visitor\'s setting', 'tapbar-mobile-action-bar' ),
							'light'  => __( 'Always light', 'tapbar-mobile-action-bar' ),
							'dark'   => __( 'Always dark', 'tapbar-mobile-action-bar' ),
							'off'    => __( 'My theme handles it', 'tapbar-mobile-action-bar' ),
						),
						$settings['style']['scheme']
					);
					?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Your own CSS', 'tapbar-mobile-action-bar' ); ?></th>
				<td>
					<textarea name="tbar[style][custom_css]" rows="5" class="large-text code" spellcheck="false"><?php echo esc_textarea( $settings['style']['custom_css'] ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Everything is exposed as a custom property on the .tbar element, so you can override anything without fighting the plugin.', 'tapbar-mobile-action-bar' ); ?></p>
				</td>
			</tr>
		</table>

		<h2 class="title"><?php esc_html_e( 'Putting it in a page', 'tapbar-mobile-action-bar' ); ?></h2>
		<p>
			<?php esc_html_e( 'The bar appears by itself, so you do not have to place it anywhere. To also drop the same row of buttons inside a page, use this shortcode. It works in the block editor, in Elementor, Divi, Beaver Builder, Bricks and Oxygen, and in any theme template.', 'tapbar-mobile-action-bar' ); ?>
		</p>
		<p><code>[tapbar]</code></p>
		<p class="description">
			<?php esc_html_e( 'Add item ids to show only some of them, for example [tapbar items="itm_a1b2c3d4"]. Elementor users also get a TapBar widget in the panel.', 'tapbar-mobile-action-bar' ); ?>
		</p>

		<h2 class="title"><?php esc_html_e( 'When you delete this plugin', 'tapbar-mobile-action-bar' ); ?></h2>
		<p>
			<label>
				<input type="checkbox" name="tbar[keep_settings_on_delete]" value="1" <?php checked( ! empty( $settings['keep_settings_on_delete'] ) ); ?>>
				<?php esc_html_e( 'Keep my settings, so they are still here if I install it again', 'tapbar-mobile-action-bar' ); ?>
			</label>
		</p>

		<?php submit_button(); ?>
	</form>
</div>
