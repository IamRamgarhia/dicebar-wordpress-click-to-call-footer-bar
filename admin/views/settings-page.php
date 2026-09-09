<?php
/**
 * The settings screen markup.
 *
 * Template only: no queries, no logic beyond presentation. Variables come from
 * MBBar_Admin::render_page().
 *
 * Every panel lives in one form and saves together, so moving between tabs
 * never loses a change. Without JavaScript every panel is simply visible.
 *
 * @package MobileBottomBar
 *
 * @var array  $settings The configuration.
 * @var array  $types    Registered item types.
 * @var array  $icons    Available icon names.
 * @var array  $tabs     Tab slugs mapped to labels and blurbs.
 * @var string $current  The active tab slug.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$mbbar_type_choices = array();

foreach ( $types as $mbbar_slug => $mbbar_type ) {
	$mbbar_type_choices[ $mbbar_slug ] = $mbbar_type['label'];
}

$mbbar_device_choices = array(
	'inherit'      => __( 'Whatever the bar does', 'mobile-bottom-bar' ),
	'phone'        => __( 'Phones only', 'mobile-bottom-bar' ),
	'phone_tablet' => __( 'Phones and tablets', 'mobile-bottom-bar' ),
	'all'          => __( 'Every screen', 'mobile-bottom-bar' ),
);

$mbbar_user_choices = array(
	'inherit' => __( 'Whatever the bar does', 'mobile-bottom-bar' ),
	'all'     => __( 'Everyone', 'mobile-bottom-bar' ),
	'in'      => __( 'Signed in visitors', 'mobile-bottom-bar' ),
	'out'     => __( 'Signed out visitors', 'mobile-bottom-bar' ),
);
?>
<div class="wrap mbbarui">
	<header class="mbbarui__masthead">
		<div class="mbbarui__brand">
			<span class="dashicons dashicons-smartphone" aria-hidden="true"></span>
			<div>
				<h1><?php esc_html_e( 'Mobile Bottom Bar', 'mobile-bottom-bar' ); ?></h1>
				<p><?php esc_html_e( 'A bar across the bottom of the screen on phones, holding whatever you put in it.', 'mobile-bottom-bar' ); ?></p>
			</div>
		</div>

		<div class="mbbarui__status">
			<?php if ( ! empty( $settings['enabled'] ) && ! empty( $settings['items'] ) ) : ?>
				<span class="mbbarui__pill mbbarui__pill--on"><?php esc_html_e( 'Live on your site', 'mobile-bottom-bar' ); ?></span>
			<?php elseif ( empty( $settings['items'] ) ) : ?>
				<span class="mbbarui__pill"><?php esc_html_e( 'No items yet', 'mobile-bottom-bar' ); ?></span>
			<?php else : ?>
				<span class="mbbarui__pill"><?php esc_html_e( 'Turned off', 'mobile-bottom-bar' ); ?></span>
			<?php endif; ?>
		</div>
	</header>

	<?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading a redirect flag, changing nothing. ?>
	<?php if ( isset( $_GET['updated'] ) ) : ?>
		<div class="notice notice-success is-dismissible mbbarui__notice">
			<p><?php esc_html_e( 'Saved.', 'mobile-bottom-bar' ); ?></p>
		</div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="mbbarui__form">
		<input type="hidden" name="action" value="mbbar_save">
		<input type="hidden" name="mbbar_tab" id="mbbar-active-tab" value="<?php echo esc_attr( $current ); ?>">
		<?php wp_nonce_field( 'mbbar_save', 'mbbar_nonce' ); ?>

		<nav class="mbbarui__tabs" role="tablist" aria-label="<?php esc_attr_e( 'Settings sections', 'mobile-bottom-bar' ); ?>">
			<?php
			$mbbar_step = 0;

			foreach ( $tabs as $mbbar_key => $mbbar_tab ) :
				++$mbbar_step;
				$mbbar_is_current = ( $mbbar_key === $current );
				?>
				<a
					href="
					<?php
					echo esc_url(
						add_query_arg(
							array(
								'page' => 'mobile-bottom-bar',
								'tab'  => $mbbar_key,
							),
							admin_url( 'admin.php' )
						)
					);
					?>
							"
					class="mbbarui__tab<?php echo $mbbar_is_current ? ' is-current' : ''; ?>"
					id="mbbar-tab-<?php echo esc_attr( $mbbar_key ); ?>"
					role="tab"
					aria-controls="mbbar-panel-<?php echo esc_attr( $mbbar_key ); ?>"
					aria-selected="<?php echo $mbbar_is_current ? 'true' : 'false'; ?>"
					data-tab="<?php echo esc_attr( $mbbar_key ); ?>"
				>
					<span class="mbbarui__tab-step"><?php echo esc_html( $mbbar_step ); ?></span>
					<span class="mbbarui__tab-text">
						<strong><?php echo esc_html( $mbbar_tab['label'] ); ?></strong>
						<small><?php echo esc_html( $mbbar_tab['blurb'] ); ?></small>
					</span>
				</a>
			<?php endforeach; ?>
		</nav>

		<div class="mbbarui__body">
		<div class="mbbarui__main">

		<?php // ---------------------------------------------------- Items ?>
		<section
			class="mbbarui__panel<?php echo 'items' === $current ? ' is-current' : ''; ?>"
			id="mbbar-panel-items"
			role="tabpanel"
			aria-labelledby="mbbar-tab-items"
		>
			<div class="mbbarui__card">
				<div class="mbbarui__card-head">
					<h2><?php esc_html_e( 'What goes in the bar', 'mobile-bottom-bar' ); ?></h2>
					<p class="mbbarui__panel-note"><?php echo esc_html( $tabs['items']['blurb'] ); ?></p>
					<p><?php esc_html_e( 'Drag to reorder. Four is the most that fits: at 320 pixels wide a fifth item clips its label rather than shrinking.', 'mobile-bottom-bar' ); ?></p>
				</div>

				<div class="mbbarui__kits">
					<h3><?php esc_html_e( 'Start from a kit', 'mobile-bottom-bar' ); ?></h3>
					<p class="mbbarui__help"><?php esc_html_e( 'Fills the list in one click. Everything stays editable afterwards.', 'mobile-bottom-bar' ); ?></p>
					<div class="mbbarui__kit-grid">
						<?php foreach ( $presets as $mbbar_kit ) : ?>
							<button type="button" class="mbbarui__kit" data-kit="<?php echo esc_attr( $mbbar_kit['id'] ); ?>">
								<span class="mbbarui__kit-count"><?php echo esc_html( count( $mbbar_kit['items'] ) ); ?></span>
								<span class="mbbarui__kit-text">
									<strong><?php echo esc_html( $mbbar_kit['name'] ); ?></strong>
									<small><?php echo esc_html( $mbbar_kit['note'] ); ?></small>
								</span>
							</button>
						<?php endforeach; ?>
					</div>
				</div>

				<div id="mbbar-items" class="mbbarui__items">
					<?php foreach ( $settings['items'] as $mbbar_index => $mbbar_item ) : ?>
						<div class="mbbarui__item" data-index="<?php echo esc_attr( $mbbar_index ); ?>" draggable="true">
							<div class="mbbarui__item-head">
								<span class="mbbarui__grip" aria-hidden="true"></span>
								<strong class="mbbarui__item-title">
									<?php echo esc_html( '' !== $mbbar_item['label'] ? $mbbar_item['label'] : $mbbar_type_choices[ $mbbar_item['type'] ] ); ?>
								</strong>
								<code class="mbbarui__item-id"><?php echo esc_html( $mbbar_item['id'] ); ?></code>
								<button type="button" class="mbbarui__remove" aria-label="<?php esc_attr_e( 'Remove this item', 'mobile-bottom-bar' ); ?>">
									<?php esc_html_e( 'Remove', 'mobile-bottom-bar' ); ?>
								</button>
							</div>

							<input type="hidden" name="mbbar[items][<?php echo esc_attr( $mbbar_index ); ?>][id]" value="<?php echo esc_attr( $mbbar_item['id'] ); ?>">

							<div class="mbbarui__fields">
								<label class="mbbarui__field">
									<span><?php esc_html_e( 'Type', 'mobile-bottom-bar' ); ?></span>
									<?php MBBar_Admin::select( 'mbbar[items][' . $mbbar_index . '][type]', $mbbar_type_choices, $mbbar_item['type'] ); ?>
								</label>

								<label class="mbbarui__field">
									<span><?php esc_html_e( 'Label', 'mobile-bottom-bar' ); ?></span>
									<input type="text" name="mbbar[items][<?php echo esc_attr( $mbbar_index ); ?>][label]" value="<?php echo esc_attr( $mbbar_item['label'] ); ?>" placeholder="<?php echo esc_attr( $mbbar_type_choices[ $mbbar_item['type'] ] ); ?>">
								</label>

								<label class="mbbarui__field" data-role="value">
									<span><?php echo esc_html( $types[ $mbbar_item['type'] ]['value']['label'] ); ?></span>
									<input type="text" name="mbbar[items][<?php echo esc_attr( $mbbar_index ); ?>][value]" value="<?php echo esc_attr( (string) $mbbar_item['value'] ); ?>">
								</label>

								<div class="mbbarui__field mbbarui__field--icons" data-role="iconfield">
									<span><?php esc_html_e( 'Icon', 'mobile-bottom-bar' ); ?></span>
									<div class="mbbarui__iconpicker" role="radiogroup" aria-label="<?php esc_attr_e( 'Icon', 'mobile-bottom-bar' ); ?>">
										<?php foreach ( $icons as $mbbar_choice ) : ?>
											<label class="mbbarui__iconopt" title="<?php echo esc_attr( $mbbar_choice ); ?>">
												<input type="radio" name="mbbar[items][<?php echo esc_attr( $mbbar_index ); ?>][icon]" value="<?php echo esc_attr( $mbbar_choice ); ?>" <?php checked( $mbbar_choice, $mbbar_item['icon'] ); ?>>
												<svg class="mbbarui__iconglyph" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#mbbar-i-<?php echo esc_attr( $mbbar_choice ); ?>"></use></svg>
												<span class="screen-reader-text"><?php echo esc_html( $mbbar_choice ); ?></span>
											</label>
										<?php endforeach; ?>
									</div>
								</div>

								<div class="mbbarui__extra" data-role="extra">
									<?php foreach ( $types[ $mbbar_item['type'] ]['extra'] as $mbbar_key => $mbbar_field ) : ?>
										<?php if ( 'boolean' === $mbbar_field['kind'] ) : ?>
											<label class="mbbarui__field mbbarui__field--check">
												<input type="checkbox" name="mbbar[items][<?php echo esc_attr( $mbbar_index ); ?>][extra][<?php echo esc_attr( $mbbar_key ); ?>]" value="1" <?php checked( ! empty( $mbbar_item['extra'][ $mbbar_key ] ) ); ?>>
												<span><?php echo esc_html( $mbbar_field['label'] ); ?></span>
											</label>
										<?php else : ?>
											<label class="mbbarui__field">
												<span><?php echo esc_html( $mbbar_field['label'] ); ?></span>
												<input type="text" name="mbbar[items][<?php echo esc_attr( $mbbar_index ); ?>][extra][<?php echo esc_attr( $mbbar_key ); ?>]" value="<?php echo esc_attr( isset( $mbbar_item['extra'][ $mbbar_key ] ) ? (string) $mbbar_item['extra'][ $mbbar_key ] : '' ); ?>">
											</label>
										<?php endif; ?>
									<?php endforeach; ?>
								</div>

								<label class="mbbarui__field mbbarui__field--check">
									<input type="checkbox" name="mbbar[items][<?php echo esc_attr( $mbbar_index ); ?>][primary]" value="1" <?php checked( ! empty( $mbbar_item['primary'] ) ); ?>>
									<span><?php esc_html_e( 'Make this the standout button', 'mobile-bottom-bar' ); ?></span>
								</label>

								<label class="mbbarui__field">
									<span><?php esc_html_e( 'Show on', 'mobile-bottom-bar' ); ?></span>
									<?php MBBar_Admin::select( 'mbbar[items][' . $mbbar_index . '][show][devices]', $mbbar_device_choices, $mbbar_item['show']['devices'] ); ?>
								</label>

								<label class="mbbarui__field">
									<span><?php esc_html_e( 'Show to', 'mobile-bottom-bar' ); ?></span>
									<?php MBBar_Admin::select( 'mbbar[items][' . $mbbar_index . '][show][users]', $mbbar_user_choices, $mbbar_item['show']['users'] ); ?>
								</label>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

				<div class="mbbarui__empty"<?php echo $settings['items'] ? ' hidden' : ''; ?>>
					<p><strong><?php esc_html_e( 'Nothing in the bar yet.', 'mobile-bottom-bar' ); ?></strong></p>
					<p><?php esc_html_e( 'Most sites start with a phone number and a WhatsApp link. Add one and look at your site on a phone.', 'mobile-bottom-bar' ); ?></p>
				</div>

				<div class="mbbarui__card-foot">
					<button type="button" class="button button-primary" id="mbbar-add-item">
						<?php esc_html_e( 'Add an item', 'mobile-bottom-bar' ); ?>
					</button>
					<span class="mbbarui__hint" id="mbbar-add-hint" role="status"></span>
				</div>
			</div>
		</section>

		<?php // ------------------------------------------------ Placement ?>
		<section
			class="mbbarui__panel<?php echo 'placement' === $current ? ' is-current' : ''; ?>"
			id="mbbar-panel-placement"
			role="tabpanel"
			aria-labelledby="mbbar-tab-placement"
		>
			<div class="mbbarui__card">
				<div class="mbbarui__card-head">
					<h2><?php esc_html_e( 'Where the bar shows', 'mobile-bottom-bar' ); ?></h2>
					<p class="mbbarui__panel-note"><?php echo esc_html( $tabs['placement']['blurb'] ); ?></p>
				</div>

				<div class="mbbarui__rows">
					<div class="mbbarui__row">
						<div class="mbbarui__row-label"><?php esc_html_e( 'Turn it on', 'mobile-bottom-bar' ); ?></div>
						<div class="mbbarui__row-field">
							<label class="mbbarui__switch">
								<input type="checkbox" name="mbbar[enabled]" value="1" <?php checked( ! empty( $settings['enabled'] ) ); ?>>
								<span><?php esc_html_e( 'Show the bar on the front end', 'mobile-bottom-bar' ); ?></span>
							</label>
						</div>
					</div>

					<div class="mbbarui__row">
						<div class="mbbarui__row-label"><?php esc_html_e( 'Screens', 'mobile-bottom-bar' ); ?></div>
						<div class="mbbarui__row-field">
							<?php
							MBBar_Admin::select(
								'mbbar[display][devices]',
								array(
									'phone'        => __( 'Phones only', 'mobile-bottom-bar' ),
									'phone_tablet' => __( 'Phones and tablets', 'mobile-bottom-bar' ),
									'all'          => __( 'Every screen', 'mobile-bottom-bar' ),
								),
								$settings['display']['devices']
							);
							?>
							<p class="mbbarui__help"><?php esc_html_e( 'A phone is anything up to the first width below. A tablet is anything up to the second.', 'mobile-bottom-bar' ); ?></p>
							<div class="mbbarui__inline">
								<label class="mbbarui__field mbbarui__field--narrow">
									<span><?php esc_html_e( 'Phone up to', 'mobile-bottom-bar' ); ?></span>
									<input type="number" name="mbbar[display][phone_max]" value="<?php echo esc_attr( $settings['display']['phone_max'] ); ?>" min="320" max="2560">
								</label>
								<label class="mbbarui__field mbbarui__field--narrow">
									<span><?php esc_html_e( 'Hide above', 'mobile-bottom-bar' ); ?></span>
									<input type="number" name="mbbar[display][breakpoint]" value="<?php echo esc_attr( $settings['display']['breakpoint'] ); ?>" min="320" max="2560">
								</label>
							</div>
						</div>
					</div>

					<div class="mbbarui__row">
						<div class="mbbarui__row-label"><?php esc_html_e( 'Pages', 'mobile-bottom-bar' ); ?></div>
						<div class="mbbarui__row-field">
							<?php
							MBBar_Admin::select(
								'mbbar[display][content][mode]',
								array_combine(
									MBBar_Settings::CONTENT_MODES,
									array(
										__( 'Everywhere', 'mobile-bottom-bar' ),
										__( 'Only on the pages listed', 'mobile-bottom-bar' ),
										__( 'Everywhere except the pages listed', 'mobile-bottom-bar' ),
									)
								),
								$settings['display']['content']['mode']
							);
							?>
							<input type="text" name="mbbar[display][content][ids]" class="mbbarui__wide" value="<?php echo esc_attr( implode( ', ', $settings['display']['content']['ids'] ) ); ?>" placeholder="12, 48, 105">
							<p class="mbbarui__help"><?php esc_html_e( 'Post or page ids, separated by commas. The id is in the address bar when you edit a page.', 'mobile-bottom-bar' ); ?></p>
						</div>
					</div>

				</div>

				<div class="mbbarui__rows mbbarui__advanced" data-advanced="placement">
					<div class="mbbarui__row">
						<div class="mbbarui__row-label"><?php esc_html_e( 'Visitors', 'mobile-bottom-bar' ); ?></div>
						<div class="mbbarui__row-field">
							<?php
							MBBar_Admin::select(
								'mbbar[display][users]',
								array(
									'all' => __( 'Everyone', 'mobile-bottom-bar' ),
									'in'  => __( 'Signed in only', 'mobile-bottom-bar' ),
									'out' => __( 'Signed out only', 'mobile-bottom-bar' ),
								),
								$settings['display']['users']
							);
							?>
						</div>
					</div>
				</div>
			</div>
		</section>

		<?php // --------------------------------------------------- Design ?>
		<section
			class="mbbarui__panel<?php echo 'design' === $current ? ' is-current' : ''; ?>"
			id="mbbar-panel-design"
			role="tabpanel"
			aria-labelledby="mbbar-tab-design"
		>
			<div class="mbbarui__card">
				<div class="mbbarui__card-head">
					<h2><?php esc_html_e( 'How it looks', 'mobile-bottom-bar' ); ?></h2>
					<p class="mbbarui__panel-note"><?php echo esc_html( $tabs['design']['blurb'] ); ?></p>
				</div>

				<div class="mbbarui__rows">
					<div class="mbbarui__row">
						<div class="mbbarui__row-label"><?php esc_html_e( 'The look', 'mobile-bottom-bar' ); ?></div>
						<div class="mbbarui__row-field">
							<div class="mbbarui__presets">
								<?php
								$mbbar_looks = array(
									'glass'   => array(
										__( 'Glass', 'mobile-bottom-bar' ),
										__( 'Frosted and floating, the way a phone shows its own bars.', 'mobile-bottom-bar' ),
									),
									'solid'   => array(
										__( 'Solid', 'mobile-bottom-bar' ),
										__( 'No see-through. Safest over busy photography.', 'mobile-bottom-bar' ),
									),
									'minimal' => array(
										__( 'Minimal', 'mobile-bottom-bar' ),
										__( 'No panel. The buttons sit straight on the page.', 'mobile-bottom-bar' ),
									),
									'bold'    => array(
										__( 'Bold', 'mobile-bottom-bar' ),
										__( 'Filled in your accent colour. Hard to ignore.', 'mobile-bottom-bar' ),
									),
								);

								foreach ( $mbbar_looks as $mbbar_key => $mbbar_look ) :
									?>
									<label class="mbbarui__preset mbbarui__preset--<?php echo esc_attr( $mbbar_key ); ?>">
										<input type="radio" name="mbbar[style][preset]" value="<?php echo esc_attr( $mbbar_key ); ?>" <?php checked( $mbbar_key, $settings['style']['preset'] ); ?>>
										<span class="mbbarui__preset-swatch" aria-hidden="true">
											<span></span><span></span><span></span>
										</span>
										<span class="mbbarui__preset-name"><?php echo esc_html( $mbbar_look[0] ); ?></span>
										<span class="mbbarui__preset-note"><?php echo esc_html( $mbbar_look[1] ); ?></span>
									</label>
								<?php endforeach; ?>
							</div>
						</div>
					</div>

					<div class="mbbarui__row">
						<div class="mbbarui__row-label"><?php esc_html_e( 'Each item shows', 'mobile-bottom-bar' ); ?></div>
						<div class="mbbarui__row-field">
							<div class="mbbarui__segmented">
								<?php
								$mbbar_modes = array(
									'icon_label' => __( 'Icon and word', 'mobile-bottom-bar' ),
									'icon'       => __( 'Icon only', 'mobile-bottom-bar' ),
									'label'      => __( 'Word only', 'mobile-bottom-bar' ),
								);

								foreach ( $mbbar_modes as $mbbar_key => $mbbar_text ) :
									?>
									<label>
										<input type="radio" name="mbbar[style][label][mode]" value="<?php echo esc_attr( $mbbar_key ); ?>" <?php checked( $mbbar_key, $settings['style']['label']['mode'] ); ?>>
										<span><?php echo esc_html( $mbbar_text ); ?></span>
									</label>
								<?php endforeach; ?>
							</div>
							<p class="mbbarui__help"><?php esc_html_e( 'It applies to every item, not one. An unlabelled icon centres itself while a labelled one lifts to make room, so mixing them leaves one mark sitting low for no visible reason.', 'mobile-bottom-bar' ); ?></p>
						</div>
					</div>

					<div class="mbbarui__row">
						<div class="mbbarui__row-label"><?php esc_html_e( 'See-through', 'mobile-bottom-bar' ); ?></div>
						<div class="mbbarui__row-field">
							<div class="mbbarui__slider">
								<input type="range" name="mbbar[style][opacity]" min="20" max="100" step="1" value="<?php echo esc_attr( $settings['style']['opacity'] ); ?>" oninput="this.nextElementSibling.value = this.value">
								<output><?php echo esc_html( $settings['style']['opacity'] ); ?></output>
								<span class="mbbarui__slider-unit">%</span>
							</div>
							<p class="mbbarui__help"><?php esc_html_e( 'How solid the panel is. Lower is more glass. Below about 60 percent the words start to swim against a photograph, which is what the preview is standing on one for.', 'mobile-bottom-bar' ); ?></p>
							<div class="mbbarui__slider">
								<input type="range" name="mbbar[style][glass]" min="0" max="60" step="1" value="<?php echo esc_attr( $settings['style']['glass'] ); ?>" oninput="this.nextElementSibling.value = this.value">
								<output><?php echo esc_html( $settings['style']['glass'] ); ?></output>
								<span class="mbbarui__slider-unit">px</span>
							</div>
							<p class="mbbarui__help"><?php esc_html_e( 'How much the blur softens what is behind. It only applies where the browser supports it, and the panel falls back to solid where it does not.', 'mobile-bottom-bar' ); ?></p>
						</div>
					</div>

					<div class="mbbarui__row">
						<div class="mbbarui__row-label"><?php esc_html_e( 'Shape', 'mobile-bottom-bar' ); ?></div>
						<div class="mbbarui__row-field">
							<div class="mbbarui__inline">
								<label class="mbbarui__field">
									<span><?php esc_html_e( 'Bar', 'mobile-bottom-bar' ); ?></span>
									<?php
									MBBar_Admin::select(
										'mbbar[style][layout]',
										array(
											'island' => __( 'Floating panel', 'mobile-bottom-bar' ),
											'full'   => __( 'Edge to edge', 'mobile-bottom-bar' ),
										),
										$settings['style']['layout']
									);
									?>
								</label>
								<label class="mbbarui__field">
									<span><?php esc_html_e( 'Buttons', 'mobile-bottom-bar' ); ?></span>
									<?php
									MBBar_Admin::select(
										'mbbar[style][item][shape]',
										array(
											'plain'   => __( 'Plain', 'mobile-bottom-bar' ),
											'filled'  => __( 'Filled', 'mobile-bottom-bar' ),
											'outline' => __( 'Outlined', 'mobile-bottom-bar' ),
											'soft'    => __( 'Tinted', 'mobile-bottom-bar' ),
										),
										$settings['style']['item']['shape']
									);
									?>
								</label>
								<label class="mbbarui__field">
									<span><?php esc_html_e( 'Shadow', 'mobile-bottom-bar' ); ?></span>
									<?php
									MBBar_Admin::select(
										'mbbar[style][shadow]',
										array(
											'none'   => __( 'None', 'mobile-bottom-bar' ),
											'soft'   => __( 'Soft', 'mobile-bottom-bar' ),
											'strong' => __( 'Strong', 'mobile-bottom-bar' ),
										),
										$settings['style']['shadow']
									);
									?>
								</label>
							</div>
							<div class="mbbarui__inline">
								<label class="mbbarui__switch">
									<input type="checkbox" name="mbbar[style][blur]" value="1" <?php checked( ! empty( $settings['style']['blur'] ) ); ?>>
									<span><?php esc_html_e( 'Blur what is behind it', 'mobile-bottom-bar' ); ?></span>
								</label>
								<label class="mbbarui__switch">
									<input type="checkbox" name="mbbar[style][divider]" value="hairline" <?php checked( 'hairline', $settings['style']['divider'] ); ?>>
									<span><?php esc_html_e( 'Line between items', 'mobile-bottom-bar' ); ?></span>
								</label>
								<label class="mbbarui__switch">
									<input type="checkbox" name="mbbar[style][brand_icons]" value="1" <?php checked( ! empty( $settings['style']['brand_icons'] ) ); ?>>
									<span><?php esc_html_e( 'Social icons in their own colours', 'mobile-bottom-bar' ); ?></span>
								</label>
							</div>
						</div>
					</div>

				</div>

				<div class="mbbarui__rows mbbarui__advanced" data-advanced="design">
					<div class="mbbarui__row">
						<div class="mbbarui__row-label"><?php esc_html_e( 'Colours', 'mobile-bottom-bar' ); ?></div>
						<div class="mbbarui__row-field">
							<?php
							$mbbar_colour_labels = array(
								'bar_bg'     => __( 'Bar background', 'mobile-bottom-bar' ),
								'bold_bg'    => __( 'Bold fill', 'mobile-bottom-bar' ),
								'text'       => __( 'Text', 'mobile-bottom-bar' ),
								'icon'       => __( 'Icons', 'mobile-bottom-bar' ),
								'accent'     => __( 'Standout item', 'mobile-bottom-bar' ),
								'hover_bg'   => __( 'Pressed', 'mobile-bottom-bar' ),
								'hover_text' => __( 'Pressed text', 'mobile-bottom-bar' ),
								'divider'    => __( 'Divider line', 'mobile-bottom-bar' ),
							);

							$mbbar_palettes = array(
								'light' => __( 'Light', 'mobile-bottom-bar' ),
								'dark'  => __( 'Dark', 'mobile-bottom-bar' ),
							);

							foreach ( $mbbar_palettes as $mbbar_palette => $mbbar_palette_name ) :
								?>
								<div class="mbbarui__palette">
									<h4><?php echo esc_html( $mbbar_palette_name ); ?></h4>
									<div class="mbbarui__swatches">
										<?php foreach ( $mbbar_colour_labels as $mbbar_token => $mbbar_label ) : ?>
											<label class="mbbarui__swatch">
												<span><?php echo esc_html( $mbbar_label ); ?></span>
												<input type="text" name="mbbar[style][<?php echo esc_attr( $mbbar_palette ); ?>][<?php echo esc_attr( $mbbar_token ); ?>]" value="<?php echo esc_attr( $settings['style'][ $mbbar_palette ][ $mbbar_token ] ); ?>" data-role="colour">
											</label>
										<?php endforeach; ?>
									</div>
								</div>
							<?php endforeach; ?>

							<p class="mbbarui__help"><?php esc_html_e( 'Hex values such as #0a84ff, or rgba for the divider. The dark set applies when the visitor has dark mode on, and the preview has a Dark button so you can check it before saving.', 'mobile-bottom-bar' ); ?></p>
						</div>
					</div>

					<div class="mbbarui__row">
						<div class="mbbarui__row-label"><?php esc_html_e( 'Dark mode', 'mobile-bottom-bar' ); ?></div>
						<div class="mbbarui__row-field">
							<?php
							MBBar_Admin::select(
								'mbbar[style][scheme]',
								array(
									'system' => __( 'Follow the visitor\'s setting', 'mobile-bottom-bar' ),
									'light'  => __( 'Always light', 'mobile-bottom-bar' ),
									'dark'   => __( 'Always dark', 'mobile-bottom-bar' ),
									'off'    => __( 'My theme handles it', 'mobile-bottom-bar' ),
								),
								$settings['style']['scheme']
							);
							?>
						</div>
					</div>

					<div class="mbbarui__row">
						<div class="mbbarui__row-label"><?php esc_html_e( 'Your own CSS', 'mobile-bottom-bar' ); ?></div>
						<div class="mbbarui__row-field">
							<textarea name="mbbar[style][custom_css]" rows="6" class="mbbarui__code" spellcheck="false"><?php echo esc_textarea( $settings['style']['custom_css'] ); ?></textarea>
							<p class="mbbarui__help"><?php esc_html_e( 'Everything is a custom property on the .mbbar element, so you can override anything without fighting the plugin.', 'mobile-bottom-bar' ); ?></p>
						</div>
					</div>
				</div>
			</div>
		</section>

		<?php // ------------------------------------------------ Behaviour ?>
		<section
			class="mbbarui__panel<?php echo 'behaviour' === $current ? ' is-current' : ''; ?>"
			id="mbbar-panel-behaviour"
			role="tabpanel"
			aria-labelledby="mbbar-tab-behaviour"
		>
			<div class="mbbarui__card">
				<div class="mbbarui__card-head">
					<h2><?php esc_html_e( 'How it behaves', 'mobile-bottom-bar' ); ?></h2>
					<p class="mbbarui__panel-note"><?php echo esc_html( $tabs['behaviour']['blurb'] ); ?></p>
				</div>

				<div class="mbbarui__rows">
					<div class="mbbarui__row">
						<div class="mbbarui__row-label"><?php esc_html_e( 'Position', 'mobile-bottom-bar' ); ?></div>
						<div class="mbbarui__row-field">
							<?php
							MBBar_Admin::select(
								'mbbar[behaviour][position]',
								array(
									'bottom' => __( 'Bottom of the screen', 'mobile-bottom-bar' ),
									'top'    => __( 'Top of the screen', 'mobile-bottom-bar' ),
								),
								$settings['behaviour']['position']
							);
							?>
							<p class="mbbarui__help"><?php esc_html_e( 'Bottom is where thumbs are. Choose top only if your theme already puts something at the bottom.', 'mobile-bottom-bar' ); ?></p>
						</div>
					</div>

					<div class="mbbarui__row">
						<div class="mbbarui__row-label"><?php esc_html_e( 'On scroll', 'mobile-bottom-bar' ); ?></div>
						<div class="mbbarui__row-field">
							<?php
							MBBar_Admin::select(
								'mbbar[behaviour][appear]',
								array(
									'always'    => __( 'Always visible', 'mobile-bottom-bar' ),
									'scroll_up' => __( 'Hide going down, return coming up', 'mobile-bottom-bar' ),
								),
								$settings['behaviour']['appear']
							);
							?>
						</div>
					</div>

				</div>

				<div class="mbbarui__rows mbbarui__advanced" data-advanced="behaviour">
					<div class="mbbarui__row">
						<div class="mbbarui__row-label"><?php esc_html_e( 'Step aside for', 'mobile-bottom-bar' ); ?></div>
						<div class="mbbarui__row-field">
							<input type="text" name="mbbar[behaviour][hide_selector]" class="mbbarui__wide" value="<?php echo esc_attr( $settings['behaviour']['hide_selector'] ); ?>" placeholder="#contact">
							<p class="mbbarui__help"><?php esc_html_e( 'The bar gets out of the way while this element is on screen. A Call button is noise beside the contact section carrying the same number.', 'mobile-bottom-bar' ); ?></p>
						</div>
					</div>

					<div class="mbbarui__row">
						<div class="mbbarui__row-label"><?php esc_html_e( 'Keep clear', 'mobile-bottom-bar' ); ?></div>
						<div class="mbbarui__row-field">
							<label class="mbbarui__field mbbarui__field--narrow">
								<span><?php esc_html_e( 'Space below', 'mobile-bottom-bar' ); ?></span>
								<input type="number" name="mbbar[behaviour][clearance]" value="<?php echo esc_attr( $settings['behaviour']['clearance'] ); ?>" min="0" max="400">
							</label>
							<p class="mbbarui__help"><?php esc_html_e( 'Use this when a cookie banner sits in the same place.', 'mobile-bottom-bar' ); ?></p>
						</div>
					</div>

					<div class="mbbarui__row">
						<div class="mbbarui__row-label"><?php esc_html_e( 'Stacking order', 'mobile-bottom-bar' ); ?></div>
						<div class="mbbarui__row-field">
							<label class="mbbarui__field mbbarui__field--narrow">
								<span><?php esc_html_e( 'z-index', 'mobile-bottom-bar' ); ?></span>
								<input type="number" name="mbbar[behaviour][z_index]" value="<?php echo esc_attr( $settings['behaviour']['z_index'] ); ?>" min="1">
							</label>
							<p class="mbbarui__help"><?php esc_html_e( 'Lower it if the bar covers one of your theme\'s pop-ups. Raise it if something covers the bar.', 'mobile-bottom-bar' ); ?></p>
						</div>
					</div>

					<div class="mbbarui__row">
						<div class="mbbarui__row-label"><?php esc_html_e( 'When you delete', 'mobile-bottom-bar' ); ?></div>
						<div class="mbbarui__row-field">
							<label class="mbbarui__switch">
								<input type="checkbox" name="mbbar[keep_settings_on_delete]" value="1" <?php checked( ! empty( $settings['keep_settings_on_delete'] ) ); ?>>
								<span><?php esc_html_e( 'Keep my settings, so they return if I install it again', 'mobile-bottom-bar' ); ?></span>
							</label>
						</div>
					</div>
				</div>
			</div>
		</section>

		<?php // ---------------------------------------- Put it in a page ?>
		<section
			class="mbbarui__panel<?php echo 'place' === $current ? ' is-current' : ''; ?>"
			id="mbbar-panel-place"
			role="tabpanel"
			aria-labelledby="mbbar-tab-place"
		>
			<div class="mbbarui__card">
				<div class="mbbarui__card-head">
					<h2><?php esc_html_e( 'Putting it inside a page', 'mobile-bottom-bar' ); ?></h2>
					<p><?php esc_html_e( 'You do not have to. The bar appears by itself on every page it is allowed on. This is for showing the same row of buttons inside your content as well.', 'mobile-bottom-bar' ); ?></p>
				</div>

				<div class="mbbarui__rows">
					<div class="mbbarui__row">
						<div class="mbbarui__row-label"><?php esc_html_e( 'Shortcode', 'mobile-bottom-bar' ); ?></div>
						<div class="mbbarui__row-field">
							<code class="mbbarui__snippet">[mobile_bottom_bar]</code>
							<p class="mbbarui__help"><?php esc_html_e( 'Works in the block editor, in a widget, in a theme template, and in Elementor, Divi, Beaver Builder, Bricks and Oxygen.', 'mobile-bottom-bar' ); ?></p>
						</div>
					</div>

					<div class="mbbarui__row">
						<div class="mbbarui__row-label"><?php esc_html_e( 'Only some items', 'mobile-bottom-bar' ); ?></div>
						<div class="mbbarui__row-field">
							<?php if ( $settings['items'] ) : ?>
								<code class="mbbarui__snippet">[mobile_bottom_bar items="<?php echo esc_html( $settings['items'][0]['id'] ); ?>"]</code>
								<p class="mbbarui__help"><?php esc_html_e( 'Item ids are shown beside each item on the Items tab. Separate several with commas.', 'mobile-bottom-bar' ); ?></p>
							<?php else : ?>
								<p class="mbbarui__help"><?php esc_html_e( 'Add an item first and its id will appear here.', 'mobile-bottom-bar' ); ?></p>
							<?php endif; ?>
						</div>
					</div>

					<div class="mbbarui__row">
						<div class="mbbarui__row-label"><?php esc_html_e( 'Elementor', 'mobile-bottom-bar' ); ?></div>
						<div class="mbbarui__row-field">
							<?php if ( did_action( 'elementor/loaded' ) ) : ?>
								<p><?php esc_html_e( 'Elementor is active, so a Mobile Bottom Bar widget is in your panel. Search for "Mobile Bottom Bar".', 'mobile-bottom-bar' ); ?></p>
							<?php else : ?>
								<p class="mbbarui__help"><?php esc_html_e( 'Elementor is not active. If you install it, a Mobile Bottom Bar widget appears in its panel automatically.', 'mobile-bottom-bar' ); ?></p>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</section>

		</div><?php // .mbbarui__main ?>

		<aside class="mbbarui__side">
			<div class="mbbarui__preview-card">
				<h2><?php esc_html_e( 'Preview', 'mobile-bottom-bar' ); ?></h2>
				<p class="mbbarui__help"><?php esc_html_e( 'The real bar, drawn with the real stylesheet, so it cannot drift from what visitors see.', 'mobile-bottom-bar' ); ?></p>

				<div class="mbbarui__phone">
					<div class="mbbarui__phone-screen" id="mbbar-preview-screen">
						<div class="mbbarui__phone-lines" aria-hidden="true">
							<span></span><span></span><span></span><span></span><span></span><span></span>
						</div>
						<nav class="mbbar mbbar--preview" id="mbbar-preview" aria-label="<?php esc_attr_e( 'Preview of the bar', 'mobile-bottom-bar' ); ?>">
							<div class="mbbar__inner" id="mbbar-preview-inner"></div>
						</nav>
					</div>
				</div>

				<div class="mbbarui__preview-stages">
					<button type="button" data-stage="photo" class="is-current"><?php esc_html_e( 'Photo', 'mobile-bottom-bar' ); ?></button>
					<button type="button" data-stage="light"><?php esc_html_e( 'Light', 'mobile-bottom-bar' ); ?></button>
					<button type="button" data-stage="dark"><?php esc_html_e( 'Dark', 'mobile-bottom-bar' ); ?></button>
				</div>
				<div class="mbbarui__preview-widths">
					<button type="button" data-width="320" class="is-current">320</button>
					<button type="button" data-width="375">375</button>
					<button type="button" data-width="414">414</button>
				</div>
				<p class="mbbarui__help" id="mbbar-preview-note"></p>
			</div>
		</aside>
		</div><?php // .mbbarui__body ?>

		<div class="mbbarui__actions">
			<?php submit_button( __( 'Save changes', 'mobile-bottom-bar' ), 'primary', 'submit', false ); ?>
			<span class="mbbarui__hint"><?php esc_html_e( 'Saving keeps every tab, not just this one.', 'mobile-bottom-bar' ); ?></span>
		</div>
	</form>
</div>

<?php echo MBBar_Icons::sprite(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped inside sprite(). ?>
