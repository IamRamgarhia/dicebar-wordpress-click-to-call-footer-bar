<?php
/**
 * The settings screen markup.
 *
 * Template only: no queries, no logic beyond presentation. Variables come from
 * FBar_Admin::render_page().
 *
 * Every panel lives in one form and saves together, so moving between tabs
 * never loses a change. Without JavaScript every panel is simply visible.
 *
 * @package FooterBar
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

$fbar_type_choices = array();

foreach ( $types as $fbar_slug => $fbar_type ) {
	$fbar_type_choices[ $fbar_slug ] = $fbar_type['label'];
}

$fbar_device_choices = array(
	'inherit'      => __( 'Whatever the bar does', 'footer-bar-mobile-action-bar' ),
	'phone'        => __( 'Phones only', 'footer-bar-mobile-action-bar' ),
	'phone_tablet' => __( 'Phones and tablets', 'footer-bar-mobile-action-bar' ),
	'all'          => __( 'Every screen', 'footer-bar-mobile-action-bar' ),
);

$fbar_user_choices = array(
	'inherit' => __( 'Whatever the bar does', 'footer-bar-mobile-action-bar' ),
	'all'     => __( 'Everyone', 'footer-bar-mobile-action-bar' ),
	'in'      => __( 'Signed in visitors', 'footer-bar-mobile-action-bar' ),
	'out'     => __( 'Signed out visitors', 'footer-bar-mobile-action-bar' ),
);
?>
<div class="wrap fbarui">
	<header class="fbarui__masthead">
		<div class="fbarui__brand">
			<span class="dashicons dashicons-smartphone" aria-hidden="true"></span>
			<div>
				<h1><?php esc_html_e( 'Footer Bar', 'footer-bar-mobile-action-bar' ); ?></h1>
				<p><?php esc_html_e( 'A bar across the bottom of the screen on phones, holding whatever you put in it.', 'footer-bar-mobile-action-bar' ); ?></p>
			</div>
		</div>

		<div class="fbarui__status">
			<?php if ( ! empty( $settings['enabled'] ) && ! empty( $settings['items'] ) ) : ?>
				<span class="fbarui__pill fbarui__pill--on"><?php esc_html_e( 'Live on your site', 'footer-bar-mobile-action-bar' ); ?></span>
			<?php elseif ( empty( $settings['items'] ) ) : ?>
				<span class="fbarui__pill"><?php esc_html_e( 'No items yet', 'footer-bar-mobile-action-bar' ); ?></span>
			<?php else : ?>
				<span class="fbarui__pill"><?php esc_html_e( 'Turned off', 'footer-bar-mobile-action-bar' ); ?></span>
			<?php endif; ?>
		</div>
	</header>

	<?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading a redirect flag, changing nothing. ?>
	<?php if ( isset( $_GET['updated'] ) ) : ?>
		<div class="notice notice-success is-dismissible fbarui__notice">
			<p><?php esc_html_e( 'Saved.', 'footer-bar-mobile-action-bar' ); ?></p>
		</div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="fbarui__form">
		<input type="hidden" name="action" value="fbar_save">
		<input type="hidden" name="fbar_tab" id="fbar-active-tab" value="<?php echo esc_attr( $current ); ?>">
		<?php wp_nonce_field( 'fbar_save', 'fbar_nonce' ); ?>

		<nav class="fbarui__tabs" role="tablist" aria-label="<?php esc_attr_e( 'Settings sections', 'footer-bar-mobile-action-bar' ); ?>">
			<?php
			$fbar_step = 0;

			foreach ( $tabs as $fbar_key => $fbar_tab ) :
				++$fbar_step;
				$fbar_is_current = ( $fbar_key === $current );
				?>
				<a
					href="
					<?php
					echo esc_url(
						add_query_arg(
							array(
								'page' => 'footerbar',
								'tab'  => $fbar_key,
							),
							admin_url( 'admin.php' )
						)
					);
					?>
							"
					class="fbarui__tab<?php echo $fbar_is_current ? ' is-current' : ''; ?>"
					id="fbar-tab-<?php echo esc_attr( $fbar_key ); ?>"
					role="tab"
					aria-controls="fbar-panel-<?php echo esc_attr( $fbar_key ); ?>"
					aria-selected="<?php echo $fbar_is_current ? 'true' : 'false'; ?>"
					data-tab="<?php echo esc_attr( $fbar_key ); ?>"
				>
					<span class="fbarui__tab-step"><?php echo esc_html( $fbar_step ); ?></span>
					<span class="fbarui__tab-text">
						<strong><?php echo esc_html( $fbar_tab['label'] ); ?></strong>
						<small><?php echo esc_html( $fbar_tab['blurb'] ); ?></small>
					</span>
				</a>
			<?php endforeach; ?>
		</nav>

		<div class="fbarui__body">
		<div class="fbarui__main">

		<?php // ---------------------------------------------------- Items ?>
		<section
			class="fbarui__panel<?php echo 'items' === $current ? ' is-current' : ''; ?>"
			id="fbar-panel-items"
			role="tabpanel"
			aria-labelledby="fbar-tab-items"
		>
			<div class="fbarui__card">
				<div class="fbarui__card-head">
					<h2><?php esc_html_e( 'What goes in the bar', 'footer-bar-mobile-action-bar' ); ?></h2>
					<p class="fbarui__panel-note"><?php echo esc_html( $tabs['items']['blurb'] ); ?></p>
					<p><?php esc_html_e( 'Drag to reorder. Four is the most that fits: at 320 pixels wide a fifth item clips its label rather than shrinking.', 'footer-bar-mobile-action-bar' ); ?></p>
				</div>

				<div class="fbarui__kits">
					<h3><?php esc_html_e( 'Start from a kit', 'footer-bar-mobile-action-bar' ); ?></h3>
					<p class="fbarui__help"><?php esc_html_e( 'Fills the list in one click. Everything stays editable afterwards.', 'footer-bar-mobile-action-bar' ); ?></p>
					<div class="fbarui__kit-grid">
						<?php foreach ( $presets as $fbar_kit ) : ?>
							<button type="button" class="fbarui__kit" data-kit="<?php echo esc_attr( $fbar_kit['id'] ); ?>">
								<span class="fbarui__kit-count"><?php echo esc_html( count( $fbar_kit['items'] ) ); ?></span>
								<span class="fbarui__kit-text">
									<strong><?php echo esc_html( $fbar_kit['name'] ); ?></strong>
									<small><?php echo esc_html( $fbar_kit['note'] ); ?></small>
								</span>
							</button>
						<?php endforeach; ?>
					</div>
				</div>

				<div id="fbar-items" class="fbarui__items">
					<?php foreach ( $settings['items'] as $fbar_index => $fbar_item ) : ?>
						<div class="fbarui__item" data-index="<?php echo esc_attr( $fbar_index ); ?>" draggable="true">
							<div class="fbarui__item-head">
								<span class="fbarui__grip" aria-hidden="true"></span>
								<strong class="fbarui__item-title">
									<?php echo esc_html( '' !== $fbar_item['label'] ? $fbar_item['label'] : $fbar_type_choices[ $fbar_item['type'] ] ); ?>
								</strong>
								<code class="fbarui__item-id"><?php echo esc_html( $fbar_item['id'] ); ?></code>
								<button type="button" class="fbarui__remove" aria-label="<?php esc_attr_e( 'Remove this item', 'footer-bar-mobile-action-bar' ); ?>">
									<?php esc_html_e( 'Remove', 'footer-bar-mobile-action-bar' ); ?>
								</button>
							</div>

							<input type="hidden" name="fbar[items][<?php echo esc_attr( $fbar_index ); ?>][id]" value="<?php echo esc_attr( $fbar_item['id'] ); ?>">

							<div class="fbarui__fields">
								<label class="fbarui__field">
									<span><?php esc_html_e( 'Type', 'footer-bar-mobile-action-bar' ); ?></span>
									<?php FBar_Admin::select( 'fbar[items][' . $fbar_index . '][type]', $fbar_type_choices, $fbar_item['type'] ); ?>
								</label>

								<label class="fbarui__field">
									<span><?php esc_html_e( 'Label', 'footer-bar-mobile-action-bar' ); ?></span>
									<input type="text" name="fbar[items][<?php echo esc_attr( $fbar_index ); ?>][label]" value="<?php echo esc_attr( $fbar_item['label'] ); ?>" placeholder="<?php echo esc_attr( $fbar_type_choices[ $fbar_item['type'] ] ); ?>">
								</label>

								<label class="fbarui__field" data-role="value">
									<span><?php echo esc_html( $types[ $fbar_item['type'] ]['value']['label'] ); ?></span>
									<input type="text" name="fbar[items][<?php echo esc_attr( $fbar_index ); ?>][value]" value="<?php echo esc_attr( (string) $fbar_item['value'] ); ?>">
								</label>

								<div class="fbarui__field fbarui__field--icons" data-role="iconfield">
									<span><?php esc_html_e( 'Icon', 'footer-bar-mobile-action-bar' ); ?></span>
									<div class="fbarui__iconpicker" role="radiogroup" aria-label="<?php esc_attr_e( 'Icon', 'footer-bar-mobile-action-bar' ); ?>">
										<?php foreach ( $icons as $fbar_choice ) : ?>
											<label class="fbarui__iconopt" title="<?php echo esc_attr( $fbar_choice ); ?>">
												<input type="radio" name="fbar[items][<?php echo esc_attr( $fbar_index ); ?>][icon]" value="<?php echo esc_attr( $fbar_choice ); ?>" <?php checked( $fbar_choice, $fbar_item['icon'] ); ?>>
												<svg class="fbarui__iconglyph" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#fbar-i-<?php echo esc_attr( $fbar_choice ); ?>"></use></svg>
												<span class="screen-reader-text"><?php echo esc_html( $fbar_choice ); ?></span>
											</label>
										<?php endforeach; ?>
									</div>
								</div>

								<div class="fbarui__extra" data-role="extra">
									<?php foreach ( $types[ $fbar_item['type'] ]['extra'] as $fbar_key => $fbar_field ) : ?>
										<?php if ( 'boolean' === $fbar_field['kind'] ) : ?>
											<label class="fbarui__field fbarui__field--check">
												<input type="checkbox" name="fbar[items][<?php echo esc_attr( $fbar_index ); ?>][extra][<?php echo esc_attr( $fbar_key ); ?>]" value="1" <?php checked( ! empty( $fbar_item['extra'][ $fbar_key ] ) ); ?>>
												<span><?php echo esc_html( $fbar_field['label'] ); ?></span>
											</label>
										<?php else : ?>
											<label class="fbarui__field">
												<span><?php echo esc_html( $fbar_field['label'] ); ?></span>
												<input type="text" name="fbar[items][<?php echo esc_attr( $fbar_index ); ?>][extra][<?php echo esc_attr( $fbar_key ); ?>]" value="<?php echo esc_attr( isset( $fbar_item['extra'][ $fbar_key ] ) ? (string) $fbar_item['extra'][ $fbar_key ] : '' ); ?>">
											</label>
										<?php endif; ?>
									<?php endforeach; ?>
								</div>

								<label class="fbarui__field fbarui__field--check">
									<input type="checkbox" name="fbar[items][<?php echo esc_attr( $fbar_index ); ?>][primary]" value="1" <?php checked( ! empty( $fbar_item['primary'] ) ); ?>>
									<span><?php esc_html_e( 'Make this the standout button', 'footer-bar-mobile-action-bar' ); ?></span>
								</label>

								<label class="fbarui__field">
									<span><?php esc_html_e( 'Show on', 'footer-bar-mobile-action-bar' ); ?></span>
									<?php FBar_Admin::select( 'fbar[items][' . $fbar_index . '][show][devices]', $fbar_device_choices, $fbar_item['show']['devices'] ); ?>
								</label>

								<label class="fbarui__field">
									<span><?php esc_html_e( 'Show to', 'footer-bar-mobile-action-bar' ); ?></span>
									<?php FBar_Admin::select( 'fbar[items][' . $fbar_index . '][show][users]', $fbar_user_choices, $fbar_item['show']['users'] ); ?>
								</label>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

				<div class="fbarui__empty"<?php echo $settings['items'] ? ' hidden' : ''; ?>>
					<p><strong><?php esc_html_e( 'Nothing in the bar yet.', 'footer-bar-mobile-action-bar' ); ?></strong></p>
					<p><?php esc_html_e( 'Most sites start with a phone number and a WhatsApp link. Add one and look at your site on a phone.', 'footer-bar-mobile-action-bar' ); ?></p>
				</div>

				<div class="fbarui__card-foot">
					<button type="button" class="button button-primary" id="fbar-add-item">
						<?php esc_html_e( 'Add an item', 'footer-bar-mobile-action-bar' ); ?>
					</button>
					<span class="fbarui__hint" id="fbar-add-hint" role="status"></span>
				</div>
			</div>
		</section>

		<?php // ------------------------------------------------ Placement ?>
		<section
			class="fbarui__panel<?php echo 'placement' === $current ? ' is-current' : ''; ?>"
			id="fbar-panel-placement"
			role="tabpanel"
			aria-labelledby="fbar-tab-placement"
		>
			<div class="fbarui__card">
				<div class="fbarui__card-head">
					<h2><?php esc_html_e( 'Where the bar shows', 'footer-bar-mobile-action-bar' ); ?></h2>
					<p class="fbarui__panel-note"><?php echo esc_html( $tabs['placement']['blurb'] ); ?></p>
				</div>

				<div class="fbarui__rows">
					<div class="fbarui__row">
						<div class="fbarui__row-label"><?php esc_html_e( 'Turn it on', 'footer-bar-mobile-action-bar' ); ?></div>
						<div class="fbarui__row-field">
							<label class="fbarui__switch">
								<input type="checkbox" name="fbar[enabled]" value="1" <?php checked( ! empty( $settings['enabled'] ) ); ?>>
								<span><?php esc_html_e( 'Show the bar on the front end', 'footer-bar-mobile-action-bar' ); ?></span>
							</label>
						</div>
					</div>

					<div class="fbarui__row">
						<div class="fbarui__row-label"><?php esc_html_e( 'Screens', 'footer-bar-mobile-action-bar' ); ?></div>
						<div class="fbarui__row-field">
							<?php
							FBar_Admin::select(
								'fbar[display][devices]',
								array(
									'phone'        => __( 'Phones only', 'footer-bar-mobile-action-bar' ),
									'phone_tablet' => __( 'Phones and tablets', 'footer-bar-mobile-action-bar' ),
									'all'          => __( 'Every screen', 'footer-bar-mobile-action-bar' ),
								),
								$settings['display']['devices']
							);
							?>
							<p class="fbarui__help"><?php esc_html_e( 'A phone is anything up to the first width below. A tablet is anything up to the second.', 'footer-bar-mobile-action-bar' ); ?></p>
							<div class="fbarui__inline">
								<label class="fbarui__field fbarui__field--narrow">
									<span><?php esc_html_e( 'Phone up to', 'footer-bar-mobile-action-bar' ); ?></span>
									<input type="number" name="fbar[display][phone_max]" value="<?php echo esc_attr( $settings['display']['phone_max'] ); ?>" min="320" max="2560">
								</label>
								<label class="fbarui__field fbarui__field--narrow">
									<span><?php esc_html_e( 'Hide above', 'footer-bar-mobile-action-bar' ); ?></span>
									<input type="number" name="fbar[display][breakpoint]" value="<?php echo esc_attr( $settings['display']['breakpoint'] ); ?>" min="320" max="2560">
								</label>
							</div>
						</div>
					</div>

					<div class="fbarui__row">
						<div class="fbarui__row-label"><?php esc_html_e( 'Pages', 'footer-bar-mobile-action-bar' ); ?></div>
						<div class="fbarui__row-field">
							<?php
							FBar_Admin::select(
								'fbar[display][content][mode]',
								array_combine(
									FBar_Settings::CONTENT_MODES,
									array(
										__( 'Everywhere', 'footer-bar-mobile-action-bar' ),
										__( 'Only on the pages listed', 'footer-bar-mobile-action-bar' ),
										__( 'Everywhere except the pages listed', 'footer-bar-mobile-action-bar' ),
									)
								),
								$settings['display']['content']['mode']
							);
							?>
							<input type="text" name="fbar[display][content][ids]" class="fbarui__wide" value="<?php echo esc_attr( implode( ', ', $settings['display']['content']['ids'] ) ); ?>" placeholder="12, 48, 105">
							<p class="fbarui__help"><?php esc_html_e( 'Post or page ids, separated by commas. The id is in the address bar when you edit a page.', 'footer-bar-mobile-action-bar' ); ?></p>
						</div>
					</div>

				</div>

				<div class="fbarui__rows fbarui__advanced" data-advanced="placement">
					<div class="fbarui__row">
						<div class="fbarui__row-label"><?php esc_html_e( 'Visitors', 'footer-bar-mobile-action-bar' ); ?></div>
						<div class="fbarui__row-field">
							<?php
							FBar_Admin::select(
								'fbar[display][users]',
								array(
									'all' => __( 'Everyone', 'footer-bar-mobile-action-bar' ),
									'in'  => __( 'Signed in only', 'footer-bar-mobile-action-bar' ),
									'out' => __( 'Signed out only', 'footer-bar-mobile-action-bar' ),
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
			class="fbarui__panel<?php echo 'design' === $current ? ' is-current' : ''; ?>"
			id="fbar-panel-design"
			role="tabpanel"
			aria-labelledby="fbar-tab-design"
		>
			<div class="fbarui__card">
				<div class="fbarui__card-head">
					<h2><?php esc_html_e( 'How it looks', 'footer-bar-mobile-action-bar' ); ?></h2>
					<p class="fbarui__panel-note"><?php echo esc_html( $tabs['design']['blurb'] ); ?></p>
				</div>

				<div class="fbarui__rows">
					<div class="fbarui__row">
						<div class="fbarui__row-label"><?php esc_html_e( 'The look', 'footer-bar-mobile-action-bar' ); ?></div>
						<div class="fbarui__row-field">
							<div class="fbarui__presets">
								<?php
								$fbar_looks = array(
									'glass'   => array(
										__( 'Glass', 'footer-bar-mobile-action-bar' ),
										__( 'Frosted and floating, the way a phone shows its own bars.', 'footer-bar-mobile-action-bar' ),
									),
									'solid'   => array(
										__( 'Solid', 'footer-bar-mobile-action-bar' ),
										__( 'No see-through. Safest over busy photography.', 'footer-bar-mobile-action-bar' ),
									),
									'minimal' => array(
										__( 'Minimal', 'footer-bar-mobile-action-bar' ),
										__( 'No panel. The buttons sit straight on the page.', 'footer-bar-mobile-action-bar' ),
									),
									'bold'    => array(
										__( 'Bold', 'footer-bar-mobile-action-bar' ),
										__( 'Filled in your accent colour. Hard to ignore.', 'footer-bar-mobile-action-bar' ),
									),
								);

								foreach ( $fbar_looks as $fbar_key => $fbar_look ) :
									?>
									<label class="fbarui__preset fbarui__preset--<?php echo esc_attr( $fbar_key ); ?>">
										<input type="radio" name="fbar[style][preset]" value="<?php echo esc_attr( $fbar_key ); ?>" <?php checked( $fbar_key, $settings['style']['preset'] ); ?>>
										<span class="fbarui__preset-swatch" aria-hidden="true">
											<span></span><span></span><span></span>
										</span>
										<span class="fbarui__preset-name"><?php echo esc_html( $fbar_look[0] ); ?></span>
										<span class="fbarui__preset-note"><?php echo esc_html( $fbar_look[1] ); ?></span>
									</label>
								<?php endforeach; ?>
							</div>
						</div>
					</div>

					<div class="fbarui__row">
						<div class="fbarui__row-label"><?php esc_html_e( 'Each item shows', 'footer-bar-mobile-action-bar' ); ?></div>
						<div class="fbarui__row-field">
							<div class="fbarui__segmented">
								<?php
								$fbar_modes = array(
									'icon_label' => __( 'Icon and word', 'footer-bar-mobile-action-bar' ),
									'icon'       => __( 'Icon only', 'footer-bar-mobile-action-bar' ),
									'label'      => __( 'Word only', 'footer-bar-mobile-action-bar' ),
								);

								foreach ( $fbar_modes as $fbar_key => $fbar_text ) :
									?>
									<label>
										<input type="radio" name="fbar[style][label][mode]" value="<?php echo esc_attr( $fbar_key ); ?>" <?php checked( $fbar_key, $settings['style']['label']['mode'] ); ?>>
										<span><?php echo esc_html( $fbar_text ); ?></span>
									</label>
								<?php endforeach; ?>
							</div>
							<p class="fbarui__help"><?php esc_html_e( 'It applies to every item, not one. An unlabelled icon centres itself while a labelled one lifts to make room, so mixing them leaves one mark sitting low for no visible reason.', 'footer-bar-mobile-action-bar' ); ?></p>
						</div>
					</div>

					<div class="fbarui__row">
						<div class="fbarui__row-label"><?php esc_html_e( 'See-through', 'footer-bar-mobile-action-bar' ); ?></div>
						<div class="fbarui__row-field">
							<div class="fbarui__slider">
								<input type="range" name="fbar[style][opacity]" min="20" max="100" step="1" value="<?php echo esc_attr( $settings['style']['opacity'] ); ?>" oninput="this.nextElementSibling.value = this.value">
								<output><?php echo esc_html( $settings['style']['opacity'] ); ?></output>
								<span class="fbarui__slider-unit">%</span>
							</div>
							<p class="fbarui__help"><?php esc_html_e( 'How solid the panel is. Lower is more glass. Below about 60 percent the words start to swim against a photograph, which is what the preview is standing on one for.', 'footer-bar-mobile-action-bar' ); ?></p>
							<div class="fbarui__slider">
								<input type="range" name="fbar[style][glass]" min="0" max="60" step="1" value="<?php echo esc_attr( $settings['style']['glass'] ); ?>" oninput="this.nextElementSibling.value = this.value">
								<output><?php echo esc_html( $settings['style']['glass'] ); ?></output>
								<span class="fbarui__slider-unit">px</span>
							</div>
							<p class="fbarui__help"><?php esc_html_e( 'How much the blur softens what is behind. It only applies where the browser supports it, and the panel falls back to solid where it does not.', 'footer-bar-mobile-action-bar' ); ?></p>
						</div>
					</div>

					<div class="fbarui__row">
						<div class="fbarui__row-label"><?php esc_html_e( 'Shape', 'footer-bar-mobile-action-bar' ); ?></div>
						<div class="fbarui__row-field">
							<div class="fbarui__inline">
								<label class="fbarui__field">
									<span><?php esc_html_e( 'Bar', 'footer-bar-mobile-action-bar' ); ?></span>
									<?php
									FBar_Admin::select(
										'fbar[style][layout]',
										array(
											'island' => __( 'Floating panel', 'footer-bar-mobile-action-bar' ),
											'full'   => __( 'Edge to edge', 'footer-bar-mobile-action-bar' ),
										),
										$settings['style']['layout']
									);
									?>
								</label>
								<label class="fbarui__field">
									<span><?php esc_html_e( 'Buttons', 'footer-bar-mobile-action-bar' ); ?></span>
									<?php
									FBar_Admin::select(
										'fbar[style][item][shape]',
										array(
											'plain'   => __( 'Plain', 'footer-bar-mobile-action-bar' ),
											'filled'  => __( 'Filled', 'footer-bar-mobile-action-bar' ),
											'outline' => __( 'Outlined', 'footer-bar-mobile-action-bar' ),
											'soft'    => __( 'Tinted', 'footer-bar-mobile-action-bar' ),
										),
										$settings['style']['item']['shape']
									);
									?>
								</label>
								<label class="fbarui__field">
									<span><?php esc_html_e( 'Shadow', 'footer-bar-mobile-action-bar' ); ?></span>
									<?php
									FBar_Admin::select(
										'fbar[style][shadow]',
										array(
											'none'   => __( 'None', 'footer-bar-mobile-action-bar' ),
											'soft'   => __( 'Soft', 'footer-bar-mobile-action-bar' ),
											'strong' => __( 'Strong', 'footer-bar-mobile-action-bar' ),
										),
										$settings['style']['shadow']
									);
									?>
								</label>
							</div>
							<div class="fbarui__inline">
								<label class="fbarui__switch">
									<input type="checkbox" name="fbar[style][blur]" value="1" <?php checked( ! empty( $settings['style']['blur'] ) ); ?>>
									<span><?php esc_html_e( 'Blur what is behind it', 'footer-bar-mobile-action-bar' ); ?></span>
								</label>
								<label class="fbarui__switch">
									<input type="checkbox" name="fbar[style][divider]" value="hairline" <?php checked( 'hairline', $settings['style']['divider'] ); ?>>
									<span><?php esc_html_e( 'Line between items', 'footer-bar-mobile-action-bar' ); ?></span>
								</label>
							</div>
						</div>
					</div>

				</div>

				<div class="fbarui__rows fbarui__advanced" data-advanced="design">
					<div class="fbarui__row">
						<div class="fbarui__row-label"><?php esc_html_e( 'Colours', 'footer-bar-mobile-action-bar' ); ?></div>
						<div class="fbarui__row-field">
							<?php
							$fbar_colour_labels = array(
								'bar_bg'     => __( 'Bar background', 'footer-bar-mobile-action-bar' ),
								'bold_bg'    => __( 'Bold fill', 'footer-bar-mobile-action-bar' ),
								'text'       => __( 'Text', 'footer-bar-mobile-action-bar' ),
								'icon'       => __( 'Icons', 'footer-bar-mobile-action-bar' ),
								'accent'     => __( 'Standout item', 'footer-bar-mobile-action-bar' ),
								'hover_bg'   => __( 'Pressed', 'footer-bar-mobile-action-bar' ),
								'hover_text' => __( 'Pressed text', 'footer-bar-mobile-action-bar' ),
								'divider'    => __( 'Divider line', 'footer-bar-mobile-action-bar' ),
							);

							$fbar_palettes = array(
								'light' => __( 'Light', 'footer-bar-mobile-action-bar' ),
								'dark'  => __( 'Dark', 'footer-bar-mobile-action-bar' ),
							);

							foreach ( $fbar_palettes as $fbar_palette => $fbar_palette_name ) :
								?>
								<div class="fbarui__palette">
									<h4><?php echo esc_html( $fbar_palette_name ); ?></h4>
									<div class="fbarui__swatches">
										<?php foreach ( $fbar_colour_labels as $fbar_token => $fbar_label ) : ?>
											<label class="fbarui__swatch">
												<span><?php echo esc_html( $fbar_label ); ?></span>
												<input type="text" name="fbar[style][<?php echo esc_attr( $fbar_palette ); ?>][<?php echo esc_attr( $fbar_token ); ?>]" value="<?php echo esc_attr( $settings['style'][ $fbar_palette ][ $fbar_token ] ); ?>" data-role="colour">
											</label>
										<?php endforeach; ?>
									</div>
								</div>
							<?php endforeach; ?>

							<p class="fbarui__help"><?php esc_html_e( 'Hex values such as #0a84ff, or rgba for the divider. The dark set applies when the visitor has dark mode on, and the preview has a Dark button so you can check it before saving.', 'footer-bar-mobile-action-bar' ); ?></p>
						</div>
					</div>

					<div class="fbarui__row">
						<div class="fbarui__row-label"><?php esc_html_e( 'Dark mode', 'footer-bar-mobile-action-bar' ); ?></div>
						<div class="fbarui__row-field">
							<?php
							FBar_Admin::select(
								'fbar[style][scheme]',
								array(
									'system' => __( 'Follow the visitor\'s setting', 'footer-bar-mobile-action-bar' ),
									'light'  => __( 'Always light', 'footer-bar-mobile-action-bar' ),
									'dark'   => __( 'Always dark', 'footer-bar-mobile-action-bar' ),
									'off'    => __( 'My theme handles it', 'footer-bar-mobile-action-bar' ),
								),
								$settings['style']['scheme']
							);
							?>
						</div>
					</div>

					<div class="fbarui__row">
						<div class="fbarui__row-label"><?php esc_html_e( 'Your own CSS', 'footer-bar-mobile-action-bar' ); ?></div>
						<div class="fbarui__row-field">
							<textarea name="fbar[style][custom_css]" rows="6" class="fbarui__code" spellcheck="false"><?php echo esc_textarea( $settings['style']['custom_css'] ); ?></textarea>
							<p class="fbarui__help"><?php esc_html_e( 'Everything is a custom property on the .fbar element, so you can override anything without fighting the plugin.', 'footer-bar-mobile-action-bar' ); ?></p>
						</div>
					</div>
				</div>
			</div>
		</section>

		<?php // ------------------------------------------------ Behaviour ?>
		<section
			class="fbarui__panel<?php echo 'behaviour' === $current ? ' is-current' : ''; ?>"
			id="fbar-panel-behaviour"
			role="tabpanel"
			aria-labelledby="fbar-tab-behaviour"
		>
			<div class="fbarui__card">
				<div class="fbarui__card-head">
					<h2><?php esc_html_e( 'How it behaves', 'footer-bar-mobile-action-bar' ); ?></h2>
					<p class="fbarui__panel-note"><?php echo esc_html( $tabs['behaviour']['blurb'] ); ?></p>
				</div>

				<div class="fbarui__rows">
					<div class="fbarui__row">
						<div class="fbarui__row-label"><?php esc_html_e( 'Position', 'footer-bar-mobile-action-bar' ); ?></div>
						<div class="fbarui__row-field">
							<?php
							FBar_Admin::select(
								'fbar[behaviour][position]',
								array(
									'bottom' => __( 'Bottom of the screen', 'footer-bar-mobile-action-bar' ),
									'top'    => __( 'Top of the screen', 'footer-bar-mobile-action-bar' ),
								),
								$settings['behaviour']['position']
							);
							?>
							<p class="fbarui__help"><?php esc_html_e( 'Bottom is where thumbs are. Choose top only if your theme already puts something at the bottom.', 'footer-bar-mobile-action-bar' ); ?></p>
						</div>
					</div>

					<div class="fbarui__row">
						<div class="fbarui__row-label"><?php esc_html_e( 'On scroll', 'footer-bar-mobile-action-bar' ); ?></div>
						<div class="fbarui__row-field">
							<?php
							FBar_Admin::select(
								'fbar[behaviour][appear]',
								array(
									'always'    => __( 'Always visible', 'footer-bar-mobile-action-bar' ),
									'scroll_up' => __( 'Hide going down, return coming up', 'footer-bar-mobile-action-bar' ),
								),
								$settings['behaviour']['appear']
							);
							?>
						</div>
					</div>

				</div>

				<div class="fbarui__rows fbarui__advanced" data-advanced="behaviour">
					<div class="fbarui__row">
						<div class="fbarui__row-label"><?php esc_html_e( 'Step aside for', 'footer-bar-mobile-action-bar' ); ?></div>
						<div class="fbarui__row-field">
							<input type="text" name="fbar[behaviour][hide_selector]" class="fbarui__wide" value="<?php echo esc_attr( $settings['behaviour']['hide_selector'] ); ?>" placeholder="#contact">
							<p class="fbarui__help"><?php esc_html_e( 'The bar gets out of the way while this element is on screen. A Call button is noise beside the contact section carrying the same number.', 'footer-bar-mobile-action-bar' ); ?></p>
						</div>
					</div>

					<div class="fbarui__row">
						<div class="fbarui__row-label"><?php esc_html_e( 'Keep clear', 'footer-bar-mobile-action-bar' ); ?></div>
						<div class="fbarui__row-field">
							<label class="fbarui__field fbarui__field--narrow">
								<span><?php esc_html_e( 'Space below', 'footer-bar-mobile-action-bar' ); ?></span>
								<input type="number" name="fbar[behaviour][clearance]" value="<?php echo esc_attr( $settings['behaviour']['clearance'] ); ?>" min="0" max="400">
							</label>
							<p class="fbarui__help"><?php esc_html_e( 'Use this when a cookie banner sits in the same place.', 'footer-bar-mobile-action-bar' ); ?></p>
						</div>
					</div>

					<div class="fbarui__row">
						<div class="fbarui__row-label"><?php esc_html_e( 'Stacking order', 'footer-bar-mobile-action-bar' ); ?></div>
						<div class="fbarui__row-field">
							<label class="fbarui__field fbarui__field--narrow">
								<span><?php esc_html_e( 'z-index', 'footer-bar-mobile-action-bar' ); ?></span>
								<input type="number" name="fbar[behaviour][z_index]" value="<?php echo esc_attr( $settings['behaviour']['z_index'] ); ?>" min="1">
							</label>
							<p class="fbarui__help"><?php esc_html_e( 'Lower it if the bar covers one of your theme\'s pop-ups. Raise it if something covers the bar.', 'footer-bar-mobile-action-bar' ); ?></p>
						</div>
					</div>

					<div class="fbarui__row">
						<div class="fbarui__row-label"><?php esc_html_e( 'When you delete', 'footer-bar-mobile-action-bar' ); ?></div>
						<div class="fbarui__row-field">
							<label class="fbarui__switch">
								<input type="checkbox" name="fbar[keep_settings_on_delete]" value="1" <?php checked( ! empty( $settings['keep_settings_on_delete'] ) ); ?>>
								<span><?php esc_html_e( 'Keep my settings, so they return if I install it again', 'footer-bar-mobile-action-bar' ); ?></span>
							</label>
						</div>
					</div>
				</div>
			</div>
		</section>

		<?php // ---------------------------------------- Put it in a page ?>
		<section
			class="fbarui__panel<?php echo 'place' === $current ? ' is-current' : ''; ?>"
			id="fbar-panel-place"
			role="tabpanel"
			aria-labelledby="fbar-tab-place"
		>
			<div class="fbarui__card">
				<div class="fbarui__card-head">
					<h2><?php esc_html_e( 'Putting it inside a page', 'footer-bar-mobile-action-bar' ); ?></h2>
					<p><?php esc_html_e( 'You do not have to. The bar appears by itself on every page it is allowed on. This is for showing the same row of buttons inside your content as well.', 'footer-bar-mobile-action-bar' ); ?></p>
				</div>

				<div class="fbarui__rows">
					<div class="fbarui__row">
						<div class="fbarui__row-label"><?php esc_html_e( 'Shortcode', 'footer-bar-mobile-action-bar' ); ?></div>
						<div class="fbarui__row-field">
							<code class="fbarui__snippet">[footerbar]</code>
							<p class="fbarui__help"><?php esc_html_e( 'Works in the block editor, in a widget, in a theme template, and in Elementor, Divi, Beaver Builder, Bricks and Oxygen.', 'footer-bar-mobile-action-bar' ); ?></p>
						</div>
					</div>

					<div class="fbarui__row">
						<div class="fbarui__row-label"><?php esc_html_e( 'Only some items', 'footer-bar-mobile-action-bar' ); ?></div>
						<div class="fbarui__row-field">
							<?php if ( $settings['items'] ) : ?>
								<code class="fbarui__snippet">[footerbar items="<?php echo esc_html( $settings['items'][0]['id'] ); ?>"]</code>
								<p class="fbarui__help"><?php esc_html_e( 'Item ids are shown beside each item on the Items tab. Separate several with commas.', 'footer-bar-mobile-action-bar' ); ?></p>
							<?php else : ?>
								<p class="fbarui__help"><?php esc_html_e( 'Add an item first and its id will appear here.', 'footer-bar-mobile-action-bar' ); ?></p>
							<?php endif; ?>
						</div>
					</div>

					<div class="fbarui__row">
						<div class="fbarui__row-label"><?php esc_html_e( 'Elementor', 'footer-bar-mobile-action-bar' ); ?></div>
						<div class="fbarui__row-field">
							<?php if ( did_action( 'elementor/loaded' ) ) : ?>
								<p><?php esc_html_e( 'Elementor is active, so a Footer Bar widget is in your panel. Search for "Footer Bar".', 'footer-bar-mobile-action-bar' ); ?></p>
							<?php else : ?>
								<p class="fbarui__help"><?php esc_html_e( 'Elementor is not active. If you install it, a Footer Bar widget appears in its panel automatically.', 'footer-bar-mobile-action-bar' ); ?></p>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</section>

		</div><?php // .fbarui__main ?>

		<aside class="fbarui__side">
			<div class="fbarui__preview-card">
				<h2><?php esc_html_e( 'Preview', 'footer-bar-mobile-action-bar' ); ?></h2>
				<p class="fbarui__help"><?php esc_html_e( 'The real bar, drawn with the real stylesheet, so it cannot drift from what visitors see.', 'footer-bar-mobile-action-bar' ); ?></p>

				<div class="fbarui__phone">
					<div class="fbarui__phone-screen" id="fbar-preview-screen">
						<div class="fbarui__phone-lines" aria-hidden="true">
							<span></span><span></span><span></span><span></span><span></span><span></span>
						</div>
						<nav class="fbar fbar--preview" id="fbar-preview" aria-label="<?php esc_attr_e( 'Preview of the bar', 'footer-bar-mobile-action-bar' ); ?>">
							<div class="fbar__inner" id="fbar-preview-inner"></div>
						</nav>
					</div>
				</div>

				<div class="fbarui__preview-stages">
					<button type="button" data-stage="photo" class="is-current"><?php esc_html_e( 'Photo', 'footer-bar-mobile-action-bar' ); ?></button>
					<button type="button" data-stage="light"><?php esc_html_e( 'Light', 'footer-bar-mobile-action-bar' ); ?></button>
					<button type="button" data-stage="dark"><?php esc_html_e( 'Dark', 'footer-bar-mobile-action-bar' ); ?></button>
				</div>
				<div class="fbarui__preview-widths">
					<button type="button" data-width="320" class="is-current">320</button>
					<button type="button" data-width="375">375</button>
					<button type="button" data-width="414">414</button>
				</div>
				<p class="fbarui__help" id="fbar-preview-note"></p>
			</div>
		</aside>
		</div><?php // .fbarui__body ?>

		<div class="fbarui__actions">
			<?php submit_button( __( 'Save changes', 'footer-bar-mobile-action-bar' ), 'primary', 'submit', false ); ?>
			<span class="fbarui__hint"><?php esc_html_e( 'Saving keeps every tab, not just this one.', 'footer-bar-mobile-action-bar' ); ?></span>
		</div>
	</form>
</div>

<?php echo FBar_Icons::sprite(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped inside sprite(). ?>
