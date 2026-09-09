<?php
/**
 * The settings screen markup.
 *
 * Template only: no queries, no logic beyond presentation. Variables come from
 * DiceBar_Admin::render_page().
 *
 * Every panel lives in one form and saves together, so moving between tabs
 * never loses a change. Without JavaScript every panel is simply visible.
 *
 * @package DiceBar
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

$dicebar_type_choices = array();

foreach ( $types as $dicebar_slug => $dicebar_type ) {
	$dicebar_type_choices[ $dicebar_slug ] = $dicebar_type['label'];
}

$dicebar_device_choices = array(
	'inherit'      => __( 'Whatever the bar does', 'dicebar' ),
	'phone'        => __( 'Phones only', 'dicebar' ),
	'phone_tablet' => __( 'Phones and tablets', 'dicebar' ),
	'all'          => __( 'Every screen', 'dicebar' ),
);

$dicebar_user_choices = array(
	'inherit' => __( 'Whatever the bar does', 'dicebar' ),
	'all'     => __( 'Everyone', 'dicebar' ),
	'in'      => __( 'Signed in visitors', 'dicebar' ),
	'out'     => __( 'Signed out visitors', 'dicebar' ),
);
?>
<div class="wrap dicebarui">
	<header class="dicebarui__masthead">
		<div class="dicebarui__brand">
			<span class="dashicons dashicons-smartphone" aria-hidden="true"></span>
			<div>
				<h1><?php esc_html_e( 'DiceBar', 'dicebar' ); ?></h1>
				<p><?php esc_html_e( 'A bar across the bottom of the screen on phones, holding whatever you put in it.', 'dicebar' ); ?></p>
			</div>
		</div>

		<div class="dicebarui__status">
			<?php if ( ! empty( $settings['enabled'] ) && ! empty( $settings['items'] ) ) : ?>
				<span class="dicebarui__pill dicebarui__pill--on"><?php esc_html_e( 'Live on your site', 'dicebar' ); ?></span>
			<?php elseif ( empty( $settings['items'] ) ) : ?>
				<span class="dicebarui__pill"><?php esc_html_e( 'No items yet', 'dicebar' ); ?></span>
			<?php else : ?>
				<span class="dicebarui__pill"><?php esc_html_e( 'Turned off', 'dicebar' ); ?></span>
			<?php endif; ?>
		</div>
	</header>

	<?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading a redirect flag, changing nothing. ?>
	<?php if ( isset( $_GET['updated'] ) ) : ?>
		<div class="notice notice-success is-dismissible dicebarui__notice">
			<p><?php esc_html_e( 'Saved.', 'dicebar' ); ?></p>
		</div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="dicebarui__form">
		<input type="hidden" name="action" value="dicebar_save">
		<input type="hidden" name="dicebar_tab" id="dicebar-active-tab" value="<?php echo esc_attr( $current ); ?>">
		<?php wp_nonce_field( 'dicebar_save', 'dicebar_nonce' ); ?>

		<nav class="dicebarui__tabs" role="tablist" aria-label="<?php esc_attr_e( 'Settings sections', 'dicebar' ); ?>">
			<?php
			$dicebar_step = 0;

			foreach ( $tabs as $dicebar_key => $dicebar_tab ) :
				++$dicebar_step;
				$dicebar_is_current = ( $dicebar_key === $current );
				?>
				<a
					href="
					<?php
					echo esc_url(
						add_query_arg(
							array(
								'page' => 'dicebar',
								'tab'  => $dicebar_key,
							),
							admin_url( 'admin.php' )
						)
					);
					?>
							"
					class="dicebarui__tab<?php echo $dicebar_is_current ? ' is-current' : ''; ?>"
					id="dicebar-tab-<?php echo esc_attr( $dicebar_key ); ?>"
					role="tab"
					aria-controls="dicebar-panel-<?php echo esc_attr( $dicebar_key ); ?>"
					aria-selected="<?php echo $dicebar_is_current ? 'true' : 'false'; ?>"
					data-tab="<?php echo esc_attr( $dicebar_key ); ?>"
				>
					<span class="dicebarui__tab-step"><?php echo esc_html( $dicebar_step ); ?></span>
					<span class="dicebarui__tab-text">
						<strong><?php echo esc_html( $dicebar_tab['label'] ); ?></strong>
						<small><?php echo esc_html( $dicebar_tab['blurb'] ); ?></small>
					</span>
				</a>
			<?php endforeach; ?>
		</nav>

		<div class="dicebarui__body">
		<div class="dicebarui__main">

		<?php // ---------------------------------------------------- Items ?>
		<section
			class="dicebarui__panel<?php echo 'items' === $current ? ' is-current' : ''; ?>"
			id="dicebar-panel-items"
			role="tabpanel"
			aria-labelledby="dicebar-tab-items"
		>
			<div class="dicebarui__card">
				<div class="dicebarui__card-head">
					<h2><?php esc_html_e( 'What goes in the bar', 'dicebar' ); ?></h2>
					<p class="dicebarui__panel-note"><?php echo esc_html( $tabs['items']['blurb'] ); ?></p>
					<p><?php esc_html_e( 'Drag to reorder. Four is the most that fits: at 320 pixels wide a fifth item clips its label rather than shrinking.', 'dicebar' ); ?></p>
				</div>

				<div class="dicebarui__kits">
					<h3><?php esc_html_e( 'Start from a kit', 'dicebar' ); ?></h3>
					<p class="dicebarui__help"><?php esc_html_e( 'Fills the list in one click. Everything stays editable afterwards.', 'dicebar' ); ?></p>
					<div class="dicebarui__kit-grid">
						<?php foreach ( $presets as $dicebar_kit ) : ?>
							<button type="button" class="dicebarui__kit" data-kit="<?php echo esc_attr( $dicebar_kit['id'] ); ?>">
								<span class="dicebarui__kit-count"><?php echo esc_html( count( $dicebar_kit['items'] ) ); ?></span>
								<span class="dicebarui__kit-text">
									<strong><?php echo esc_html( $dicebar_kit['name'] ); ?></strong>
									<small><?php echo esc_html( $dicebar_kit['note'] ); ?></small>
								</span>
							</button>
						<?php endforeach; ?>
					</div>
				</div>

				<div id="dicebar-items" class="dicebarui__items">
					<?php foreach ( $settings['items'] as $dicebar_index => $dicebar_item ) : ?>
						<div class="dicebarui__item" data-index="<?php echo esc_attr( $dicebar_index ); ?>" draggable="true">
							<div class="dicebarui__item-head">
								<span class="dicebarui__grip" aria-hidden="true"></span>
								<strong class="dicebarui__item-title">
									<?php echo esc_html( '' !== $dicebar_item['label'] ? $dicebar_item['label'] : $dicebar_type_choices[ $dicebar_item['type'] ] ); ?>
								</strong>
								<code class="dicebarui__item-id"><?php echo esc_html( $dicebar_item['id'] ); ?></code>
								<button type="button" class="dicebarui__remove" aria-label="<?php esc_attr_e( 'Remove this item', 'dicebar' ); ?>">
									<?php esc_html_e( 'Remove', 'dicebar' ); ?>
								</button>
							</div>

							<input type="hidden" name="dicebar[items][<?php echo esc_attr( $dicebar_index ); ?>][id]" value="<?php echo esc_attr( $dicebar_item['id'] ); ?>">

							<div class="dicebarui__fields">
								<label class="dicebarui__field">
									<span><?php esc_html_e( 'Type', 'dicebar' ); ?></span>
									<?php DiceBar_Admin::select( 'dicebar[items][' . $dicebar_index . '][type]', $dicebar_type_choices, $dicebar_item['type'] ); ?>
								</label>

								<label class="dicebarui__field">
									<span><?php esc_html_e( 'Label', 'dicebar' ); ?></span>
									<input type="text" name="dicebar[items][<?php echo esc_attr( $dicebar_index ); ?>][label]" value="<?php echo esc_attr( $dicebar_item['label'] ); ?>" placeholder="<?php echo esc_attr( $dicebar_type_choices[ $dicebar_item['type'] ] ); ?>">
								</label>

								<label class="dicebarui__field" data-role="value">
									<span><?php echo esc_html( $types[ $dicebar_item['type'] ]['value']['label'] ); ?></span>
									<input type="text" name="dicebar[items][<?php echo esc_attr( $dicebar_index ); ?>][value]" value="<?php echo esc_attr( (string) $dicebar_item['value'] ); ?>">
								</label>

								<div class="dicebarui__field dicebarui__field--icons" data-role="iconfield">
									<span><?php esc_html_e( 'Icon', 'dicebar' ); ?></span>
									<div class="dicebarui__iconpicker" role="radiogroup" aria-label="<?php esc_attr_e( 'Icon', 'dicebar' ); ?>">
										<?php foreach ( $icons as $dicebar_choice ) : ?>
											<label class="dicebarui__iconopt" title="<?php echo esc_attr( $dicebar_choice ); ?>">
												<input type="radio" name="dicebar[items][<?php echo esc_attr( $dicebar_index ); ?>][icon]" value="<?php echo esc_attr( $dicebar_choice ); ?>" <?php checked( $dicebar_choice, $dicebar_item['icon'] ); ?>>
												<svg class="dicebarui__iconglyph" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#dicebar-i-<?php echo esc_attr( $dicebar_choice ); ?>"></use></svg>
												<span class="screen-reader-text"><?php echo esc_html( $dicebar_choice ); ?></span>
											</label>
										<?php endforeach; ?>
									</div>
								</div>

								<div class="dicebarui__extra" data-role="extra">
									<?php foreach ( $types[ $dicebar_item['type'] ]['extra'] as $dicebar_key => $dicebar_field ) : ?>
										<?php if ( 'boolean' === $dicebar_field['kind'] ) : ?>
											<label class="dicebarui__field dicebarui__field--check">
												<input type="checkbox" name="dicebar[items][<?php echo esc_attr( $dicebar_index ); ?>][extra][<?php echo esc_attr( $dicebar_key ); ?>]" value="1" <?php checked( ! empty( $dicebar_item['extra'][ $dicebar_key ] ) ); ?>>
												<span><?php echo esc_html( $dicebar_field['label'] ); ?></span>
											</label>
										<?php else : ?>
											<label class="dicebarui__field">
												<span><?php echo esc_html( $dicebar_field['label'] ); ?></span>
												<input type="text" name="dicebar[items][<?php echo esc_attr( $dicebar_index ); ?>][extra][<?php echo esc_attr( $dicebar_key ); ?>]" value="<?php echo esc_attr( isset( $dicebar_item['extra'][ $dicebar_key ] ) ? (string) $dicebar_item['extra'][ $dicebar_key ] : '' ); ?>">
											</label>
										<?php endif; ?>
									<?php endforeach; ?>
								</div>

								<label class="dicebarui__field dicebarui__field--check">
									<input type="checkbox" name="dicebar[items][<?php echo esc_attr( $dicebar_index ); ?>][primary]" value="1" <?php checked( ! empty( $dicebar_item['primary'] ) ); ?>>
									<span><?php esc_html_e( 'Make this the standout button', 'dicebar' ); ?></span>
								</label>

								<label class="dicebarui__field">
									<span><?php esc_html_e( 'Show on', 'dicebar' ); ?></span>
									<?php DiceBar_Admin::select( 'dicebar[items][' . $dicebar_index . '][show][devices]', $dicebar_device_choices, $dicebar_item['show']['devices'] ); ?>
								</label>

								<label class="dicebarui__field">
									<span><?php esc_html_e( 'Show to', 'dicebar' ); ?></span>
									<?php DiceBar_Admin::select( 'dicebar[items][' . $dicebar_index . '][show][users]', $dicebar_user_choices, $dicebar_item['show']['users'] ); ?>
								</label>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

				<div class="dicebarui__empty"<?php echo $settings['items'] ? ' hidden' : ''; ?>>
					<p><strong><?php esc_html_e( 'Nothing in the bar yet.', 'dicebar' ); ?></strong></p>
					<p><?php esc_html_e( 'Most sites start with a phone number and a WhatsApp link. Add one and look at your site on a phone.', 'dicebar' ); ?></p>
				</div>

				<div class="dicebarui__card-foot">
					<button type="button" class="button button-primary" id="dicebar-add-item">
						<?php esc_html_e( 'Add an item', 'dicebar' ); ?>
					</button>
					<span class="dicebarui__hint" id="dicebar-add-hint" role="status"></span>
				</div>
			</div>
		</section>

		<?php // ------------------------------------------------ Placement ?>
		<section
			class="dicebarui__panel<?php echo 'placement' === $current ? ' is-current' : ''; ?>"
			id="dicebar-panel-placement"
			role="tabpanel"
			aria-labelledby="dicebar-tab-placement"
		>
			<div class="dicebarui__card">
				<div class="dicebarui__card-head">
					<h2><?php esc_html_e( 'Where the bar shows', 'dicebar' ); ?></h2>
					<p class="dicebarui__panel-note"><?php echo esc_html( $tabs['placement']['blurb'] ); ?></p>
				</div>

				<div class="dicebarui__rows">
					<div class="dicebarui__row">
						<div class="dicebarui__row-label"><?php esc_html_e( 'Turn it on', 'dicebar' ); ?></div>
						<div class="dicebarui__row-field">
							<label class="dicebarui__switch">
								<input type="checkbox" name="dicebar[enabled]" value="1" <?php checked( ! empty( $settings['enabled'] ) ); ?>>
								<span><?php esc_html_e( 'Show the bar on the front end', 'dicebar' ); ?></span>
							</label>
						</div>
					</div>

					<div class="dicebarui__row">
						<div class="dicebarui__row-label"><?php esc_html_e( 'Screens', 'dicebar' ); ?></div>
						<div class="dicebarui__row-field">
							<?php
							DiceBar_Admin::select(
								'dicebar[display][devices]',
								array(
									'phone'        => __( 'Phones only', 'dicebar' ),
									'phone_tablet' => __( 'Phones and tablets', 'dicebar' ),
									'all'          => __( 'Every screen', 'dicebar' ),
								),
								$settings['display']['devices']
							);
							?>
							<p class="dicebarui__help"><?php esc_html_e( 'A phone is anything up to the first width below. A tablet is anything up to the second.', 'dicebar' ); ?></p>
							<div class="dicebarui__inline">
								<label class="dicebarui__field dicebarui__field--narrow">
									<span><?php esc_html_e( 'Phone up to', 'dicebar' ); ?></span>
									<input type="number" name="dicebar[display][phone_max]" value="<?php echo esc_attr( $settings['display']['phone_max'] ); ?>" min="320" max="2560">
								</label>
								<label class="dicebarui__field dicebarui__field--narrow">
									<span><?php esc_html_e( 'Hide above', 'dicebar' ); ?></span>
									<input type="number" name="dicebar[display][breakpoint]" value="<?php echo esc_attr( $settings['display']['breakpoint'] ); ?>" min="320" max="2560">
								</label>
							</div>
						</div>
					</div>

					<div class="dicebarui__row">
						<div class="dicebarui__row-label"><?php esc_html_e( 'Pages', 'dicebar' ); ?></div>
						<div class="dicebarui__row-field">
							<?php
							DiceBar_Admin::select(
								'dicebar[display][content][mode]',
								array_combine(
									DiceBar_Settings::CONTENT_MODES,
									array(
										__( 'Everywhere', 'dicebar' ),
										__( 'Only on the pages listed', 'dicebar' ),
										__( 'Everywhere except the pages listed', 'dicebar' ),
									)
								),
								$settings['display']['content']['mode']
							);
							?>
							<input type="text" name="dicebar[display][content][ids]" class="dicebarui__wide" value="<?php echo esc_attr( implode( ', ', $settings['display']['content']['ids'] ) ); ?>" placeholder="12, 48, 105">
							<p class="dicebarui__help"><?php esc_html_e( 'Post or page ids, separated by commas. The id is in the address bar when you edit a page.', 'dicebar' ); ?></p>
						</div>
					</div>

				</div>

				<div class="dicebarui__rows dicebarui__advanced" data-advanced="placement">
					<div class="dicebarui__row">
						<div class="dicebarui__row-label"><?php esc_html_e( 'Visitors', 'dicebar' ); ?></div>
						<div class="dicebarui__row-field">
							<?php
							DiceBar_Admin::select(
								'dicebar[display][users]',
								array(
									'all' => __( 'Everyone', 'dicebar' ),
									'in'  => __( 'Signed in only', 'dicebar' ),
									'out' => __( 'Signed out only', 'dicebar' ),
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
			class="dicebarui__panel<?php echo 'design' === $current ? ' is-current' : ''; ?>"
			id="dicebar-panel-design"
			role="tabpanel"
			aria-labelledby="dicebar-tab-design"
		>
			<div class="dicebarui__card">
				<div class="dicebarui__card-head">
					<h2><?php esc_html_e( 'How it looks', 'dicebar' ); ?></h2>
					<p class="dicebarui__panel-note"><?php echo esc_html( $tabs['design']['blurb'] ); ?></p>
				</div>

				<div class="dicebarui__rows">
					<div class="dicebarui__row">
						<div class="dicebarui__row-label"><?php esc_html_e( 'The look', 'dicebar' ); ?></div>
						<div class="dicebarui__row-field">
							<div class="dicebarui__presets">
								<?php
								$dicebar_looks = array(
									'glass'   => array(
										__( 'Glass', 'dicebar' ),
										__( 'Frosted and floating, the way a phone shows its own bars.', 'dicebar' ),
									),
									'solid'   => array(
										__( 'Solid', 'dicebar' ),
										__( 'No see-through. Safest over busy photography.', 'dicebar' ),
									),
									'minimal' => array(
										__( 'Minimal', 'dicebar' ),
										__( 'No panel. The buttons sit straight on the page.', 'dicebar' ),
									),
									'bold'    => array(
										__( 'Bold', 'dicebar' ),
										__( 'Filled in your accent colour. Hard to ignore.', 'dicebar' ),
									),
								);

								foreach ( $dicebar_looks as $dicebar_key => $dicebar_look ) :
									?>
									<label class="dicebarui__preset dicebarui__preset--<?php echo esc_attr( $dicebar_key ); ?>">
										<input type="radio" name="dicebar[style][preset]" value="<?php echo esc_attr( $dicebar_key ); ?>" <?php checked( $dicebar_key, $settings['style']['preset'] ); ?>>
										<span class="dicebarui__preset-swatch" aria-hidden="true">
											<span></span><span></span><span></span>
										</span>
										<span class="dicebarui__preset-name"><?php echo esc_html( $dicebar_look[0] ); ?></span>
										<span class="dicebarui__preset-note"><?php echo esc_html( $dicebar_look[1] ); ?></span>
									</label>
								<?php endforeach; ?>
							</div>
						</div>
					</div>

					<div class="dicebarui__row">
						<div class="dicebarui__row-label"><?php esc_html_e( 'Each item shows', 'dicebar' ); ?></div>
						<div class="dicebarui__row-field">
							<div class="dicebarui__segmented">
								<?php
								$dicebar_modes = array(
									'icon_label' => __( 'Icon and word', 'dicebar' ),
									'icon'       => __( 'Icon only', 'dicebar' ),
									'label'      => __( 'Word only', 'dicebar' ),
								);

								foreach ( $dicebar_modes as $dicebar_key => $dicebar_text ) :
									?>
									<label>
										<input type="radio" name="dicebar[style][label][mode]" value="<?php echo esc_attr( $dicebar_key ); ?>" <?php checked( $dicebar_key, $settings['style']['label']['mode'] ); ?>>
										<span><?php echo esc_html( $dicebar_text ); ?></span>
									</label>
								<?php endforeach; ?>
							</div>
							<p class="dicebarui__help"><?php esc_html_e( 'It applies to every item, not one. An unlabelled icon centres itself while a labelled one lifts to make room, so mixing them leaves one mark sitting low for no visible reason.', 'dicebar' ); ?></p>
						</div>
					</div>

					<div class="dicebarui__row">
						<div class="dicebarui__row-label"><?php esc_html_e( 'See-through', 'dicebar' ); ?></div>
						<div class="dicebarui__row-field">
							<div class="dicebarui__slider">
								<input type="range" name="dicebar[style][opacity]" min="20" max="100" step="1" value="<?php echo esc_attr( $settings['style']['opacity'] ); ?>" oninput="this.nextElementSibling.value = this.value">
								<output><?php echo esc_html( $settings['style']['opacity'] ); ?></output>
								<span class="dicebarui__slider-unit">%</span>
							</div>
							<p class="dicebarui__help"><?php esc_html_e( 'How solid the panel is. Lower is more glass. Below about 60 percent the words start to swim against a photograph, which is what the preview is standing on one for.', 'dicebar' ); ?></p>
							<div class="dicebarui__slider">
								<input type="range" name="dicebar[style][glass]" min="0" max="60" step="1" value="<?php echo esc_attr( $settings['style']['glass'] ); ?>" oninput="this.nextElementSibling.value = this.value">
								<output><?php echo esc_html( $settings['style']['glass'] ); ?></output>
								<span class="dicebarui__slider-unit">px</span>
							</div>
							<p class="dicebarui__help"><?php esc_html_e( 'How much the blur softens what is behind. It only applies where the browser supports it, and the panel falls back to solid where it does not.', 'dicebar' ); ?></p>
						</div>
					</div>

					<div class="dicebarui__row">
						<div class="dicebarui__row-label"><?php esc_html_e( 'Shape', 'dicebar' ); ?></div>
						<div class="dicebarui__row-field">
							<div class="dicebarui__inline">
								<label class="dicebarui__field">
									<span><?php esc_html_e( 'Bar', 'dicebar' ); ?></span>
									<?php
									DiceBar_Admin::select(
										'dicebar[style][layout]',
										array(
											'island' => __( 'Floating panel', 'dicebar' ),
											'full'   => __( 'Edge to edge', 'dicebar' ),
										),
										$settings['style']['layout']
									);
									?>
								</label>
								<label class="dicebarui__field">
									<span><?php esc_html_e( 'Buttons', 'dicebar' ); ?></span>
									<?php
									DiceBar_Admin::select(
										'dicebar[style][item][shape]',
										array(
											'plain'   => __( 'Plain', 'dicebar' ),
											'filled'  => __( 'Filled', 'dicebar' ),
											'outline' => __( 'Outlined', 'dicebar' ),
											'soft'    => __( 'Tinted', 'dicebar' ),
										),
										$settings['style']['item']['shape']
									);
									?>
								</label>
								<label class="dicebarui__field">
									<span><?php esc_html_e( 'Shadow', 'dicebar' ); ?></span>
									<?php
									DiceBar_Admin::select(
										'dicebar[style][shadow]',
										array(
											'none'   => __( 'None', 'dicebar' ),
											'soft'   => __( 'Soft', 'dicebar' ),
											'strong' => __( 'Strong', 'dicebar' ),
										),
										$settings['style']['shadow']
									);
									?>
								</label>
							</div>
							<div class="dicebarui__inline">
								<label class="dicebarui__switch">
									<input type="checkbox" name="dicebar[style][blur]" value="1" <?php checked( ! empty( $settings['style']['blur'] ) ); ?>>
									<span><?php esc_html_e( 'Blur what is behind it', 'dicebar' ); ?></span>
								</label>
								<label class="dicebarui__switch">
									<input type="checkbox" name="dicebar[style][divider]" value="hairline" <?php checked( 'hairline', $settings['style']['divider'] ); ?>>
									<span><?php esc_html_e( 'Line between items', 'dicebar' ); ?></span>
								</label>
								<label class="dicebarui__switch">
									<input type="checkbox" name="dicebar[style][brand_icons]" value="1" <?php checked( ! empty( $settings['style']['brand_icons'] ) ); ?>>
									<span><?php esc_html_e( 'Social icons in their own colours', 'dicebar' ); ?></span>
								</label>
							</div>
						</div>
					</div>

				</div>

				<div class="dicebarui__rows dicebarui__advanced" data-advanced="design">
					<div class="dicebarui__row">
						<div class="dicebarui__row-label"><?php esc_html_e( 'Colours', 'dicebar' ); ?></div>
						<div class="dicebarui__row-field">
							<?php
							$dicebar_colour_labels = array(
								'bar_bg'     => __( 'Bar background', 'dicebar' ),
								'bold_bg'    => __( 'Bold fill', 'dicebar' ),
								'text'       => __( 'Text', 'dicebar' ),
								'icon'       => __( 'Icons', 'dicebar' ),
								'accent'     => __( 'Standout item', 'dicebar' ),
								'hover_bg'   => __( 'Pressed', 'dicebar' ),
								'hover_text' => __( 'Pressed text', 'dicebar' ),
								'divider'    => __( 'Divider line', 'dicebar' ),
							);

							$dicebar_palettes = array(
								'light' => __( 'Light', 'dicebar' ),
								'dark'  => __( 'Dark', 'dicebar' ),
							);

							foreach ( $dicebar_palettes as $dicebar_palette => $dicebar_palette_name ) :
								?>
								<div class="dicebarui__palette">
									<h4><?php echo esc_html( $dicebar_palette_name ); ?></h4>
									<div class="dicebarui__swatches">
										<?php foreach ( $dicebar_colour_labels as $dicebar_token => $dicebar_label ) : ?>
											<label class="dicebarui__swatch">
												<span><?php echo esc_html( $dicebar_label ); ?></span>
												<input type="text" name="dicebar[style][<?php echo esc_attr( $dicebar_palette ); ?>][<?php echo esc_attr( $dicebar_token ); ?>]" value="<?php echo esc_attr( $settings['style'][ $dicebar_palette ][ $dicebar_token ] ); ?>" data-role="colour">
											</label>
										<?php endforeach; ?>
									</div>
								</div>
							<?php endforeach; ?>

							<p class="dicebarui__help"><?php esc_html_e( 'Hex values such as #0a84ff, or rgba for the divider. The dark set applies when the visitor has dark mode on, and the preview has a Dark button so you can check it before saving.', 'dicebar' ); ?></p>
						</div>
					</div>

					<div class="dicebarui__row">
						<div class="dicebarui__row-label"><?php esc_html_e( 'Dark mode', 'dicebar' ); ?></div>
						<div class="dicebarui__row-field">
							<?php
							DiceBar_Admin::select(
								'dicebar[style][scheme]',
								array(
									'system' => __( 'Follow the visitor\'s setting', 'dicebar' ),
									'light'  => __( 'Always light', 'dicebar' ),
									'dark'   => __( 'Always dark', 'dicebar' ),
									'off'    => __( 'My theme handles it', 'dicebar' ),
								),
								$settings['style']['scheme']
							);
							?>
						</div>
					</div>

					<div class="dicebarui__row">
						<div class="dicebarui__row-label"><?php esc_html_e( 'Your own CSS', 'dicebar' ); ?></div>
						<div class="dicebarui__row-field">
							<textarea name="dicebar[style][custom_css]" rows="6" class="dicebarui__code" spellcheck="false"><?php echo esc_textarea( $settings['style']['custom_css'] ); ?></textarea>
							<p class="dicebarui__help"><?php esc_html_e( 'Everything is a custom property on the .dicebar element, so you can override anything without fighting the plugin.', 'dicebar' ); ?></p>
						</div>
					</div>
				</div>
			</div>
		</section>

		<?php // ------------------------------------------------ Behaviour ?>
		<section
			class="dicebarui__panel<?php echo 'behaviour' === $current ? ' is-current' : ''; ?>"
			id="dicebar-panel-behaviour"
			role="tabpanel"
			aria-labelledby="dicebar-tab-behaviour"
		>
			<div class="dicebarui__card">
				<div class="dicebarui__card-head">
					<h2><?php esc_html_e( 'How it behaves', 'dicebar' ); ?></h2>
					<p class="dicebarui__panel-note"><?php echo esc_html( $tabs['behaviour']['blurb'] ); ?></p>
				</div>

				<div class="dicebarui__rows">
					<div class="dicebarui__row">
						<div class="dicebarui__row-label"><?php esc_html_e( 'Position', 'dicebar' ); ?></div>
						<div class="dicebarui__row-field">
							<?php
							DiceBar_Admin::select(
								'dicebar[behaviour][position]',
								array(
									'bottom' => __( 'Bottom of the screen', 'dicebar' ),
									'top'    => __( 'Top of the screen', 'dicebar' ),
								),
								$settings['behaviour']['position']
							);
							?>
							<p class="dicebarui__help"><?php esc_html_e( 'Bottom is where thumbs are. Choose top only if your theme already puts something at the bottom.', 'dicebar' ); ?></p>
						</div>
					</div>

					<div class="dicebarui__row">
						<div class="dicebarui__row-label"><?php esc_html_e( 'On scroll', 'dicebar' ); ?></div>
						<div class="dicebarui__row-field">
							<?php
							DiceBar_Admin::select(
								'dicebar[behaviour][appear]',
								array(
									'always'    => __( 'Always visible', 'dicebar' ),
									'scroll_up' => __( 'Hide going down, return coming up', 'dicebar' ),
								),
								$settings['behaviour']['appear']
							);
							?>
						</div>
					</div>

				</div>

				<div class="dicebarui__rows dicebarui__advanced" data-advanced="behaviour">
					<div class="dicebarui__row">
						<div class="dicebarui__row-label"><?php esc_html_e( 'Step aside for', 'dicebar' ); ?></div>
						<div class="dicebarui__row-field">
							<input type="text" name="dicebar[behaviour][hide_selector]" class="dicebarui__wide" value="<?php echo esc_attr( $settings['behaviour']['hide_selector'] ); ?>" placeholder="#contact">
							<p class="dicebarui__help"><?php esc_html_e( 'The bar gets out of the way while this element is on screen. A Call button is noise beside the contact section carrying the same number.', 'dicebar' ); ?></p>
						</div>
					</div>

					<div class="dicebarui__row">
						<div class="dicebarui__row-label"><?php esc_html_e( 'Keep clear', 'dicebar' ); ?></div>
						<div class="dicebarui__row-field">
							<label class="dicebarui__field dicebarui__field--narrow">
								<span><?php esc_html_e( 'Space below', 'dicebar' ); ?></span>
								<input type="number" name="dicebar[behaviour][clearance]" value="<?php echo esc_attr( $settings['behaviour']['clearance'] ); ?>" min="0" max="400">
							</label>
							<p class="dicebarui__help"><?php esc_html_e( 'Use this when a cookie banner sits in the same place.', 'dicebar' ); ?></p>
						</div>
					</div>

					<div class="dicebarui__row">
						<div class="dicebarui__row-label"><?php esc_html_e( 'Stacking order', 'dicebar' ); ?></div>
						<div class="dicebarui__row-field">
							<label class="dicebarui__field dicebarui__field--narrow">
								<span><?php esc_html_e( 'z-index', 'dicebar' ); ?></span>
								<input type="number" name="dicebar[behaviour][z_index]" value="<?php echo esc_attr( $settings['behaviour']['z_index'] ); ?>" min="1">
							</label>
							<p class="dicebarui__help"><?php esc_html_e( 'Lower it if the bar covers one of your theme\'s pop-ups. Raise it if something covers the bar.', 'dicebar' ); ?></p>
						</div>
					</div>

					<div class="dicebarui__row">
						<div class="dicebarui__row-label"><?php esc_html_e( 'When you delete', 'dicebar' ); ?></div>
						<div class="dicebarui__row-field">
							<label class="dicebarui__switch">
								<input type="checkbox" name="dicebar[keep_settings_on_delete]" value="1" <?php checked( ! empty( $settings['keep_settings_on_delete'] ) ); ?>>
								<span><?php esc_html_e( 'Keep my settings, so they return if I install it again', 'dicebar' ); ?></span>
							</label>
						</div>
					</div>
				</div>
			</div>
		</section>

		<?php // ---------------------------------------- Put it in a page ?>
		<section
			class="dicebarui__panel<?php echo 'place' === $current ? ' is-current' : ''; ?>"
			id="dicebar-panel-place"
			role="tabpanel"
			aria-labelledby="dicebar-tab-place"
		>
			<div class="dicebarui__card">
				<div class="dicebarui__card-head">
					<h2><?php esc_html_e( 'Putting it inside a page', 'dicebar' ); ?></h2>
					<p><?php esc_html_e( 'You do not have to. The bar appears by itself on every page it is allowed on. This is for showing the same row of buttons inside your content as well.', 'dicebar' ); ?></p>
				</div>

				<div class="dicebarui__rows">
					<div class="dicebarui__row">
						<div class="dicebarui__row-label"><?php esc_html_e( 'Shortcode', 'dicebar' ); ?></div>
						<div class="dicebarui__row-field">
							<code class="dicebarui__snippet">[dicebar]</code>
							<p class="dicebarui__help"><?php esc_html_e( 'Works in the block editor, in a widget, in a theme template, and in Elementor, Divi, Beaver Builder, Bricks and Oxygen.', 'dicebar' ); ?></p>
						</div>
					</div>

					<div class="dicebarui__row">
						<div class="dicebarui__row-label"><?php esc_html_e( 'Only some items', 'dicebar' ); ?></div>
						<div class="dicebarui__row-field">
							<?php if ( $settings['items'] ) : ?>
								<code class="dicebarui__snippet">[dicebar items="<?php echo esc_html( $settings['items'][0]['id'] ); ?>"]</code>
								<p class="dicebarui__help"><?php esc_html_e( 'Item ids are shown beside each item on the Items tab. Separate several with commas.', 'dicebar' ); ?></p>
							<?php else : ?>
								<p class="dicebarui__help"><?php esc_html_e( 'Add an item first and its id will appear here.', 'dicebar' ); ?></p>
							<?php endif; ?>
						</div>
					</div>

					<div class="dicebarui__row">
						<div class="dicebarui__row-label"><?php esc_html_e( 'Elementor', 'dicebar' ); ?></div>
						<div class="dicebarui__row-field">
							<?php if ( did_action( 'elementor/loaded' ) ) : ?>
								<p><?php esc_html_e( 'Elementor is active, so a DiceBar widget is in your panel. Search for "DiceBar".', 'dicebar' ); ?></p>
							<?php else : ?>
								<p class="dicebarui__help"><?php esc_html_e( 'Elementor is not active. If you install it, a DiceBar widget appears in its panel automatically.', 'dicebar' ); ?></p>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</section>

		</div><?php // .dicebarui__main ?>

		<aside class="dicebarui__side">
			<div class="dicebarui__preview-card">
				<h2><?php esc_html_e( 'Preview', 'dicebar' ); ?></h2>
				<p class="dicebarui__help"><?php esc_html_e( 'The real bar, drawn with the real stylesheet, so it cannot drift from what visitors see.', 'dicebar' ); ?></p>

				<div class="dicebarui__phone">
					<div class="dicebarui__phone-screen" id="dicebar-preview-screen">
						<div class="dicebarui__phone-lines" aria-hidden="true">
							<span></span><span></span><span></span><span></span><span></span><span></span>
						</div>
						<nav class="dicebar dicebar--preview" id="dicebar-preview" aria-label="<?php esc_attr_e( 'Preview of the bar', 'dicebar' ); ?>">
							<div class="dicebar__inner" id="dicebar-preview-inner"></div>
						</nav>
					</div>
				</div>

				<div class="dicebarui__preview-stages">
					<button type="button" data-stage="photo" class="is-current"><?php esc_html_e( 'Photo', 'dicebar' ); ?></button>
					<button type="button" data-stage="light"><?php esc_html_e( 'Light', 'dicebar' ); ?></button>
					<button type="button" data-stage="dark"><?php esc_html_e( 'Dark', 'dicebar' ); ?></button>
				</div>
				<div class="dicebarui__preview-widths">
					<button type="button" data-width="320" class="is-current">320</button>
					<button type="button" data-width="375">375</button>
					<button type="button" data-width="414">414</button>
				</div>
				<p class="dicebarui__help" id="dicebar-preview-note"></p>
			</div>
		</aside>
		</div><?php // .dicebarui__body ?>

		<div class="dicebarui__actions">
			<?php submit_button( __( 'Save changes', 'dicebar' ), 'primary', 'submit', false ); ?>
			<span class="dicebarui__hint"><?php esc_html_e( 'Saving keeps every tab, not just this one.', 'dicebar' ); ?></span>
		</div>
	</form>
</div>

<?php echo DiceBar_Icons::sprite(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped inside sprite(). ?>
