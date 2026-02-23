<?php
/**
 * Complete Native WordPress Customizer - Kirki Replacement
 * 
 * This file provides a complete replacement for Kirki functionality including:
 * - Custom color palette controls
 * - Repeater controls
 * - Live preview JavaScript
 * - CSS output
 */

require_once get_template_directory() . '/library/colors.php';

// ============================================
// HELPER FUNCTIONS
// ============================================

if ( ! function_exists( 'get_native_palette' ) ) {
	function get_native_palette() {
		$choices = get_theme_design_choices([
			'include_colors'    => true,
			'include_gradients' => false,
			'for_kirki'         => true,
		]);
		return (is_array($choices) && count($choices) > 0) ? array_keys($choices) : ['#000000'];
	}
}

if ( ! function_exists( 'avidd_get_palette_hex_default' ) ) {
	function avidd_get_palette_hex_default( $var_name, $fallback = '#000000' ) {
		$choices = get_theme_design_choices( [
			'include_colors'    => true,
			'include_gradients' => false,
			'for_kirki'         => true,
		] );
		$first_hex = false;
		foreach ( $choices as $hex => $name ) {
			if ( ! $first_hex ) {
				$first_hex = $hex;
			}
			if ( is_string( $var_name ) && $name && stripos( $var_name, $name ) !== false ) {
				return $hex;
			}
		}
		if ( function_exists( 'get_theme_color_palette' ) ) {
			$palette = get_theme_color_palette();
			if ( isset( $palette[ $var_name ] ) ) {
				return $palette[ $var_name ];
			}
		}
		return $first_hex ? $first_hex : $fallback;
	}
}

// Helper: Safely get repeater field data
if ( ! function_exists( 'avidd_get_repeater_data' ) ) {
	function avidd_get_repeater_data( $setting_id ) {
		$data = get_theme_mod( $setting_id );
		
		// Handle empty values
		if ( empty( $data ) ) {
			return array();
		}
		
		// Decode if it's a JSON string
		if ( is_string( $data ) ) {
			$decoded = json_decode( $data, true );
			return is_array( $decoded ) ? $decoded : array();
		}
		
		// Return if already an array
		if ( is_array( $data ) ) {
			return $data;
		}
		
		// Fallback to empty array
		return array();
	}
}

// ============================================
// CUSTOM CONTROLS
// ============================================

if ( class_exists( 'WP_Customize_Control' ) ) {
	
	/**
	 * Color Palette Control
	 */
	class Avidd_Color_Palette_Control extends WP_Customize_Control {
		public $type = 'color-palette';
		public $palette = array();
		public $style = 'round';
		public $allow_clear = true; // Allow clearing the selection

		public function render_content() {
			$palette = ! empty( $this->palette ) ? $this->palette : get_native_palette();
			$unique_id = 'color_palette_' . str_replace( '-', '_', $this->id );
			?>
			<label>
				<?php if ( ! empty( $this->label ) ) : ?>
					<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<?php endif; ?>
				<?php if ( ! empty( $this->description ) ) : ?>
					<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
				<?php endif; ?>
			</label>
			<div class="color-palette-container" data-style="<?php echo esc_attr( $this->style ); ?>" data-setting-id="<?php echo esc_attr( $this->id ); ?>">
				<?php if ( $this->allow_clear ) : ?>
					<label class="color-palette-item clear-item <?php echo $this->style === 'round' ? 'round' : 'square'; ?>" title="<?php esc_attr_e( 'Clear selection', 'avidd' ); ?>">
						<input 
							type="radio" 
							name="<?php echo esc_attr( $unique_id ); ?>" 
							value=""
							data-setting-id="<?php echo esc_attr( $this->id ); ?>"
							<?php checked( $this->value(), '' ); ?>
						/>
						<span class="color-swatch clear-swatch">
							<span class="dashicons dashicons-no"></span>
						</span>
					</label>
				<?php endif; ?>
				<?php foreach ( $palette as $color ) : ?>
					<label class="color-palette-item <?php echo $this->style === 'round' ? 'round' : 'square'; ?>">
						<input 
							type="radio" 
							name="<?php echo esc_attr( $unique_id ); ?>" 
							value="<?php echo esc_attr( $color ); ?>"
							data-setting-id="<?php echo esc_attr( $this->id ); ?>"
							<?php checked( $this->value(), $color ); ?>
						/>
						<span class="color-swatch" style="background-color: <?php echo esc_attr( $color ); ?>;"></span>
					</label>
				<?php endforeach; ?>
			</div>
			<style>
				.color-palette-container {
					display: flex;
					flex-wrap: wrap;
					gap: 8px;
					margin-top: 8px;
				}
				.color-palette-item {
					cursor: pointer;
					position: relative;
				}
				.color-palette-item input[type="radio"] {
					position: absolute;
					opacity: 0;
					width: 0;
					height: 0;
				}
				.color-palette-item .color-swatch {
					display: block;
					width: 30px;
					height: 30px;
					border: 2px solid #ddd;
					transition: all 0.2s;
				}
				.color-palette-item.round .color-swatch {
					border-radius: 50%;
				}
				.color-palette-item.square .color-swatch {
					border-radius: 3px;
				}
				.color-palette-item input[type="radio"]:checked + .color-swatch {
					border-color: #0073aa;
					box-shadow: 0 0 0 2px #0073aa;
					transform: scale(1.1);
				}
				.color-palette-item:hover .color-swatch {
					border-color: #0073aa;
				}
				/* Clear button styles */
				.color-palette-item.clear-item .color-swatch {
					background: #fff;
					position: relative;
					display: flex;
					align-items: center;
					justify-content: center;
				}
				.color-palette-item.clear-item .color-swatch .dashicons {
					color: #dc3232;
					font-size: 20px;
					width: 20px;
					height: 20px;
				}
				.color-palette-item.clear-item input[type="radio"]:checked + .color-swatch {
					background: #f0f0f0;
				}
			</style>
			<script type="text/javascript">
				(function($) {
					var settingId = '<?php echo esc_js( $this->id ); ?>';
					var container = $('.color-palette-container[data-setting-id="' + settingId + '"]');
					
					container.find('.color-palette-item input[type="radio"]').on('change', function() {
						var value = $(this).val();
						wp.customize(settingId).set(value);
					});
				})(jQuery);
			</script>
			<?php
		}
	}

	/**
	 * Repeater Control
	 */
	class Avidd_Repeater_Control extends WP_Customize_Control {
		public $type = 'repeater';
		public $fields = array(); // Define fields for this repeater

		public function render_content() {
			$raw_value = $this->value();
			
			// Handle both JSON string and array (from previous Kirki data)
			if ( is_string( $raw_value ) ) {
				$value = json_decode( $raw_value, true );
			} elseif ( is_array( $raw_value ) ) {
				$value = $raw_value;
			} else {
				$value = array();
			}
			
			if ( ! is_array( $value ) ) {
				$value = array();
			}
			?>
			<label>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			</label>
			<div class="repeater-container" data-setting="<?php echo esc_attr( $this->id ); ?>">
				<div class="repeater-items">
					<?php foreach ( $value as $index => $item ) : ?>
						<?php $this->render_repeater_item( $index, $item ); ?>
					<?php endforeach; ?>
				</div>
				<button type="button" class="button add-repeater-item"><?php esc_html_e( 'Add Item', 'avidd' ); ?></button>
			</div>
			<style>
				.repeater-container { margin-top: 10px; }
				.repeater-item { 
					background: #f9f9f9; 
					padding: 15px; 
					margin-bottom: 10px; 
					border: 1px solid #ddd;
					position: relative;
				}
				.repeater-item-controls { margin-top: 10px; }
				.remove-repeater-item { 
					color: #a00; 
					text-decoration: none;
					position: absolute;
					top: 10px;
					right: 10px;
				}
				.repeater-item input[type="text"],
				.repeater-item input[type="url"],
				.repeater-item input[type="time"] { 
					width: 100%; 
					margin-top: 5px;
				}
				.repeater-item img {
					max-width: 100%;
					height: auto;
					margin-top: 10px;
				}
				.repeater-field {
					margin-bottom: 10px;
				}
			</style>
			<script type="text/javascript">
				(function($) {
					var container = $('.repeater-container[data-setting="<?php echo esc_js( $this->id ); ?>"]');
					var itemsContainer = container.find('.repeater-items');
					
					container.find('.add-repeater-item').on('click', function() {
						var index = itemsContainer.find('.repeater-item').length;
						var template = <?php echo json_encode( $this->get_repeater_item_template() ); ?>;
						itemsContainer.append(template.replace(/INDEX/g, index));
						updateRepeaterValue();
					});
					
					itemsContainer.on('click', '.remove-repeater-item', function(e) {
						e.preventDefault();
						$(this).closest('.repeater-item').remove();
						updateRepeaterValue();
					});
					
					itemsContainer.on('click', '.upload-image-button', function(e) {
						e.preventDefault();
						var button = $(this);
						var input = button.siblings('input[type="hidden"]');
						var preview = button.siblings('.image-preview');
						
						var mediaUploader = wp.media({
							title: 'Select Image',
							button: { text: 'Select' },
							multiple: false
						});
						
						mediaUploader.on('select', function() {
							var attachment = mediaUploader.state().get('selection').first().toJSON();
							input.val(attachment.id);
							preview.html('<img src="' + attachment.url + '" />');
							updateRepeaterValue();
						});
						
						mediaUploader.open();
					});
					
					itemsContainer.on('change', 'input, select, textarea', function() {
						updateRepeaterValue();
					});
					
					function updateRepeaterValue() {
						var items = [];
						itemsContainer.find('.repeater-item').each(function() {
							var item = {};
							$(this).find('input, select, textarea').each(function() {
								var name = $(this).attr('name');
								if (name) {
									item[name] = $(this).val();
								}
							});
							items.push(item);
						});
						wp.customize('<?php echo esc_js( $this->id ); ?>').set(JSON.stringify(items));
					}
				})(jQuery);
			</script>
			<?php
		}

		protected function render_repeater_item( $index, $item ) {
			?>
			<div class="repeater-item">
				<a href="#" class="remove-repeater-item">✕</a>
				<?php foreach ( $this->fields as $field_id => $field ) : 
					$field_value = isset( $item[ $field_id ] ) ? $item[ $field_id ] : '';
					$field_type = isset( $field['type'] ) ? $field['type'] : 'text';
					$field_label = isset( $field['label'] ) ? $field['label'] : ucfirst( $field_id );
				?>
					<div class="repeater-field">
						<label>
							<?php echo esc_html( $field_label ); ?>
							<?php if ( $field_type === 'image' ) : 
								$image_url = $field_value ? wp_get_attachment_url( $field_value ) : '';
							?>
								<input type="hidden" name="<?php echo esc_attr( $field_id ); ?>" value="<?php echo esc_attr( $field_value ); ?>" />
								<button type="button" class="button upload-image-button"><?php esc_html_e( 'Select Image', 'avidd' ); ?></button>
								<div class="image-preview">
									<?php if ( $image_url ) : ?>
										<img src="<?php echo esc_url( $image_url ); ?>" />
									<?php endif; ?>
								</div>
							<?php else : ?>
								<input 
									type="<?php echo esc_attr( $field_type ); ?>" 
									name="<?php echo esc_attr( $field_id ); ?>" 
									value="<?php echo esc_attr( $field_value ); ?>" 
								/>
							<?php endif; ?>
						</label>
					</div>
				<?php endforeach; ?>
			</div>
			<?php
		}

		protected function get_repeater_item_template() {
			ob_start();
			?>
			<div class="repeater-item">
				<a href="#" class="remove-repeater-item">✕</a>
				<?php foreach ( $this->fields as $field_id => $field ) : 
					$field_type = isset( $field['type'] ) ? $field['type'] : 'text';
					$field_label = isset( $field['label'] ) ? $field['label'] : ucfirst( $field_id );
				?>
					<div class="repeater-field">
						<label>
							<?php echo esc_html( $field_label ); ?>
							<?php if ( $field_type === 'image' ) : ?>
								<input type="hidden" name="<?php echo esc_attr( $field_id ); ?>" value="" />
								<button type="button" class="button upload-image-button"><?php esc_html_e( 'Select Image', 'avidd' ); ?></button>
								<div class="image-preview"></div>
							<?php else : ?>
								<input 
									type="<?php echo esc_attr( $field_type ); ?>" 
									name="<?php echo esc_attr( $field_id ); ?>" 
									value="" 
								/>
							<?php endif; ?>
						</label>
					</div>
				<?php endforeach; ?>
			</div>
			<?php
			return ob_get_clean();
		}
	}
}

// ============================================
// CUSTOMIZER REGISTRATION
// ============================================

function avidd_customize_register( $wp_customize ) {
	$palette_keys = get_native_palette();
	$default_1 = in_array( avidd_get_palette_hex_default('$primary-color', $palette_keys[0] ), $palette_keys ) 
		? avidd_get_palette_hex_default('$primary-color', $palette_keys[0] ) 
		: $palette_keys[0];
	$default_footer = in_array( avidd_get_palette_hex_default('#fefefe', $palette_keys[0] ), $palette_keys ) 
		? avidd_get_palette_hex_default('#fefefe', $palette_keys[0] ) 
		: $palette_keys[0];
	$default_settings = in_array( avidd_get_palette_hex_default('#fefefe', $palette_keys[0] ), $palette_keys ) 
		? avidd_get_palette_hex_default('#fefefe', $palette_keys[0] ) 
		: $palette_keys[0];

	// ============================================
	// PANELS
	// ============================================

	$wp_customize->add_panel( 'header_navigation_panel', array(
		'title'       => __( 'Header & Navigation', 'avidd' ),
		'description' => __( 'Customize your site header, logo, and navigation.', 'avidd' ),
		'priority'    => 30,
	));

	$wp_customize->add_panel( 'footer_panel', array(
		'title'       => __( 'Footer', 'avidd' ),
		'description' => __( 'Customize your site footer.', 'avidd' ),
		'priority'    => 40,
	));

	$wp_customize->add_panel( 'design_layout_panel', array(
		'title'       => __( 'Design & Layout', 'avidd' ),
		'description' => __( 'Customize colors, typography, and layout.', 'avidd' ),
		'priority'    => 50,
	));

	$wp_customize->add_panel( 'company_information_panel', array(
		'title'       => __( 'Company Information', 'avidd' ),
		'description' => __( 'Manage contact information, social media, and opening hours.', 'avidd' ),
		'priority'    => 60,
	));
	
	$wp_customize->add_panel( 'notifications_panel', array(
		'title'       => __( 'Notifications', 'avidd' ),
		'description' => __( 'Manage site notifications.', 'avidd' ),
		'priority'    => 70,
	));

	// ============================================
	// SECTIONS
	// ============================================

	// Header & Navigation sections
	$wp_customize->add_section( 'site_header_section', array(
		'title' => __( 'Header Settings', 'avidd' ),
		'panel' => 'header_navigation_panel',
	));

	$wp_customize->add_section( 'navigation_colors_section', array(
		'title' => __( 'Navigation Colors', 'avidd' ),
		'panel' => 'header_navigation_panel',
	));

	// Footer sections
	$wp_customize->add_section( 'footer_colors_section', array(
		'title' => __( 'Footer Colors', 'avidd' ),
		'panel' => 'footer_panel',
	));

	$wp_customize->add_section( 'footer_content_section', array(
		'title' => __( 'Footer Content', 'avidd' ),
		'panel' => 'footer_panel',
	));

	// Design & Layout sections
	$wp_customize->add_section( 'site_colors_section', array(
		'title' => __( 'Site Colors', 'avidd' ),
		'panel' => 'design_layout_panel',
	));
	
	$wp_customize->add_section( 'default_content_section', array(
		'title' => __( 'Default content', 'avidd' ),
		'panel' => 'design_layout_panel',
	));

	// Company Information sections
	$wp_customize->add_section( 'contact_section', array(
		'title' => __( 'Contact Details', 'avidd' ),
		'panel' => 'company_information_panel',
	));

	$wp_customize->add_section( 'social_media_section', array(
		'title' => __( 'Social Media', 'avidd' ),
		'panel' => 'company_information_panel',
	));

	$wp_customize->add_section( 'opening_times', array(
		'title' => __( 'Opening Times', 'avidd' ),
		'panel' => 'company_information_panel',
	));
	
	// Notifications sections
	$wp_customize->add_section( 'notifications_section', array(
		'title' => __( 'Header Notifications', 'avidd' ),
		'panel' => 'notifications_panel',
	));
	
	$wp_customize->add_section( 'email_notifications_section', array(
		'title' => __( 'Email Notifications', 'avidd' ),
		'panel' => 'notifications_panel',
	));

		$wp_customize->add_section( 'resources_notifications_section', array(
		'title' => __( 'Resources Notifications', 'avidd' ),
		'panel' => 'notifications_panel',
	));

	// ============================================
	// HEADER & NAVIGATION
	// ============================================

	// Header Settings Section
	
	// Header background image
	$wp_customize->add_setting( 'header_background_image', array(
		'default'           => '',
		'sanitize_callback' => 'absint',
	));
	$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'header_background_image', array(
		'label'     => __( 'Header background image', 'avidd' ),
		'section'   => 'site_header_section',
		'mime_type' => 'image',
	)));

	// Header logo
	$wp_customize->add_setting( 'header_logo', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	));
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'header_logo', array(
		'label'   => __( 'Header logo', 'avidd' ),
		'section' => 'site_header_section',
	)));

	// Contained Header
	$wp_customize->add_setting( 'contained_header', array(
		'default'           => 'on',
		'sanitize_callback' => 'sanitize_text_field',
	));
	$wp_customize->add_control( 'contained_header', array(
		'label'   => __( 'Contained Header', 'avidd' ),
		'section' => 'site_header_section',
		'type'    => 'checkbox',
	));

	// Sticky Header
	$wp_customize->add_setting( 'sticky_header', array(
		'default'           => 'on',
		'sanitize_callback' => 'sanitize_text_field',
	));
	$wp_customize->add_control( 'sticky_header', array(
		'label'   => __( 'Sticky Header', 'avidd' ),
		'section' => 'site_header_section',
		'type'    => 'checkbox',
	));

	// Fixed Header
	$wp_customize->add_setting( 'fixed_header', array(
		'default'           => 'on',
		'sanitize_callback' => 'sanitize_text_field',
	));
	$wp_customize->add_control( 'fixed_header', array(
		'label'   => __( 'Sticky header over featured image', 'avidd' ),
		'section' => 'site_header_section',
		'type'    => 'checkbox',
	));

	// Transparent Header
	$wp_customize->add_setting( 'transparent_header', array(
		'default'           => 'on',
		'sanitize_callback' => 'sanitize_text_field',
	));
	$wp_customize->add_control( 'transparent_header', array(
		'label'   => __( 'Transparent header over hero section', 'avidd' ),
		'section' => 'site_header_section',
		'type'    => 'checkbox',
	));

	// Navigation Colors Section

	// Nav background colour
	$wp_customize->add_setting( 'color_palette_setting_0', array(
		'default'           => $palette_keys[0],
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'postMessage',
	));
	$wp_customize->add_control( new Avidd_Color_Palette_Control( $wp_customize, 'color_palette_setting_0', array(
		'label'   => __( 'Nav background colour', 'avidd' ),
		'section' => 'navigation_colors_section',
		'palette' => $palette_keys,
		'style'   => 'round',
	)));

	// Nav menu item colour
	$wp_customize->add_setting( 'color_palette_setting_1', array(
		'default'           => $default_1,
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'postMessage',
	));
	$wp_customize->add_control( new Avidd_Color_Palette_Control( $wp_customize, 'color_palette_setting_1', array(
		'label'   => __( 'Nav menu item colour', 'avidd' ),
		'section' => 'navigation_colors_section',
		'palette' => $palette_keys,
		'style'   => 'round',
	)));

	// ============================================
	// FOOTER
	// ============================================

	// Footer Colors Section

	// Footer background colour
	$wp_customize->add_setting( 'color_palette_setting_3', array(
		'default'           => $default_footer,
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'postMessage',
	));
	$wp_customize->add_control( new Avidd_Color_Palette_Control( $wp_customize, 'color_palette_setting_3', array(
		'label'   => __( 'Footer background colour', 'avidd' ),
		'section' => 'footer_colors_section',
		'palette' => $palette_keys,
		'style'   => 'round',
	)));

	// Footer text colour
	$wp_customize->add_setting( 'color_palette_setting_4', array(
		'default'           => $default_1,
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'postMessage',
	));
	$wp_customize->add_control( new Avidd_Color_Palette_Control( $wp_customize, 'color_palette_setting_4', array(
		'label'   => __( 'Footer text colour', 'avidd' ),
		'section' => 'footer_colors_section',
		'palette' => $palette_keys,
		'style'   => 'round',
	)));

	// Footer link colour
	$wp_customize->add_setting( 'color_palette_setting_5', array(
		'default'           => $default_1,
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'postMessage',
	));
	$wp_customize->add_control( new Avidd_Color_Palette_Control( $wp_customize, 'color_palette_setting_5', array(
		'label'   => __( 'Footer link colour', 'avidd' ),
		'section' => 'footer_colors_section',
		'palette' => $palette_keys,
		'style'   => 'round',
	)));

	// Footer Content Section

	// Footer background image
	$wp_customize->add_setting( 'footer_background_image', array(
		'default'           => '',
		'sanitize_callback' => 'absint',
	));
	$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'footer_background_image', array(
		'label'     => __( 'Footer background image', 'avidd' ),
		'section'   => 'footer_content_section',
		'mime_type' => 'image',
	)));

	// Footer links repeater
	$wp_customize->add_setting( 'footer_links', array(
		'default'           => array(),
		'sanitize_callback' => 'avidd_sanitize_repeater',
	));
	$wp_customize->add_control( new Avidd_Repeater_Control( $wp_customize, 'footer_links', array(
		'label'   => __( 'Footer images', 'avidd' ),
		'section' => 'footer_content_section',
		'fields'  => array(
			'footer_image' => array(
				'type'  => 'image',
				'label' => __( 'Footer Image', 'avidd' ),
			),
			'link_url' => array(
				'type'  => 'url',
				'label' => __( 'Link URL', 'avidd' ),
			),
		),
	)));

	// ============================================
	// COMPANY INFORMATION
	// ============================================

	// Contact Details Section

	// Contact Phone Number
	$wp_customize->add_setting( 'contact_phone_number', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
	));
	$wp_customize->add_control( 'contact_phone_number', array(
		'label'   => __( 'Phone Number', 'avidd' ),
		'section' => 'contact_section',
		'type'    => 'tel',
	));

	// Contact Email
	$wp_customize->add_setting( 'contact_email', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_email',
	));
	$wp_customize->add_control( 'contact_email', array(
		'label'   => __( 'Email Address', 'avidd' ),
		'section' => 'contact_section',
		'type'    => 'email',
	));

	// Address Lines
	for ( $i = 1; $i <= 6; $i++ ) {
		$wp_customize->add_setting( "contact_address_{$i}", array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		));
		$wp_customize->add_control( "contact_address_{$i}", array(
			'label'   => sprintf( __( 'Address Line %d', 'avidd' ), $i ),
			'section' => 'contact_section',
			'type'    => 'text',
		));
	}

	// Footer Company Number
	$wp_customize->add_setting( 'footer_company_number', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
	));
	$wp_customize->add_control( 'footer_company_number', array(
		'label'   => __( 'Company Number', 'avidd' ),
		'section' => 'contact_section',
		'type'    => 'text',
	));

	// Footer Copyright
	$wp_customize->add_setting( 'footer_copyright', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
	));
	$wp_customize->add_control( 'footer_copyright', array(
		'label'   => __( 'Copyright Text', 'avidd' ),
		'section' => 'contact_section',
		'type'    => 'text',
	));

	// Social Media Section

	$social_networks = array(
		'instagram' => array( 'label' => 'Instagram', 'default' => 'https://instagram.com/' ),
		'facebook'  => array( 'label' => 'Facebook', 'default' => 'https://facebook.com/' ),
		'x'         => array( 'label' => 'X', 'default' => 'https://x.com/' ),
		'linkedin'  => array( 'label' => 'LinkedIn', 'default' => 'https://linkedin.com/' ),
		'pinterest' => array( 'label' => 'Pinterest', 'default' => 'https://pinterest.com/' ),
		'youtube'   => array( 'label' => 'YouTube', 'default' => 'https://youtube.com/' ),
		'tiktok'    => array( 'label' => 'TikTok', 'default' => 'https://tiktok.com/' ),
	);

	foreach ( $social_networks as $network => $data ) {
		$wp_customize->add_setting( "social-{$network}", array(
			'default'           => '',
			'sanitize_callback' => function( $checked ) {
				return ( ( isset( $checked ) && true == $checked ) ? '1' : '' );
			},
		));
		$wp_customize->add_control( "social-{$network}", array(
			'label'   => $data['label'],
			'section' => 'social_media_section',
			'type'    => 'checkbox',
		));

		$wp_customize->add_setting( "social-{$network}-url", array(
			'default'           => $data['default'],
			'sanitize_callback' => 'esc_url_raw',
		));
		$wp_customize->add_control( "social-{$network}-url", array(
			'label'           => sprintf( __( '%s URL', 'avidd' ), $data['label'] ),
			'section'         => 'social_media_section',
			'type'            => 'url',
			'active_callback' => function() use ( $network ) {
				return get_theme_mod( "social-{$network}", '' ) === '1';
			},
		));
	}

	// Opening Times Section

	$wp_customize->add_setting( 'opening_times', array(
		'default'           => '',
		'sanitize_callback' => 'avidd_sanitize_repeater',
	));
	$wp_customize->add_control( new Avidd_Repeater_Control( $wp_customize, 'opening_times', array(
		'label'   => __( 'Opening Hours', 'avidd' ),
		'section' => 'opening_times',
		'fields'  => array(
			'day' => array(
				'type'  => 'text',
				'label' => __( 'Day', 'avidd' ),
			),
			'opening_time' => array(
				'type'  => 'time',
				'label' => __( 'Opening Time', 'avidd' ),
			),
			'closing_time' => array(
				'type'  => 'time',
				'label' => __( 'Closing Time', 'avidd' ),
			),
			'note' => array(
				'type'  => 'text',
				'label' => __( 'Note', 'avidd' ),
			),
		),
	)));

	$wp_customize->add_setting( 'special_opening_times', array(
		'default'           => '',
		'sanitize_callback' => 'avidd_sanitize_repeater',
	));
	$wp_customize->add_control( new Avidd_Repeater_Control( $wp_customize, 'special_opening_times', array(
		'label'   => __( 'Special Opening Hours', 'avidd' ),
		'section' => 'opening_times',
		'fields'  => array(
			'day' => array(
				'type'  => 'text',
				'label' => __( 'Day', 'avidd' ),
			),
			'opening_time' => array(
				'type'  => 'time',
				'label' => __( 'Opening Time', 'avidd' ),
			),
			'closing_time' => array(
				'type'  => 'time',
				'label' => __( 'Closing Time', 'avidd' ),
			),
			'note' => array(
				'type'  => 'text',
				'label' => __( 'Note', 'avidd' ),
			),
		),
	)));

	// ============================================
	// DESIGN & LAYOUT
	// ============================================

	// Site Colors Section

	// Page background colour
	$wp_customize->add_setting( 'color_palette_setting_10', array(
		'default'           => $default_settings,
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'postMessage',
	));
	$wp_customize->add_control( new Avidd_Color_Palette_Control( $wp_customize, 'color_palette_setting_10', array(
		'label'   => __( 'Page background colour', 'avidd' ),
		'section' => 'site_colors_section',
		'palette' => $palette_keys,
		'style'   => 'round',
	)));

	// Dark Mode
	$wp_customize->add_setting( 'dark_mode', array(
		'default'           => 'off',
		'sanitize_callback' => 'sanitize_text_field',
	));
	$wp_customize->add_control( 'dark_mode', array(
		'label'   => __( 'Dark Mode', 'avidd' ),
		'section' => 'site_colors_section',
		'type'    => 'checkbox',
	));

	// Default Content Section

	// Default Post Image
	$wp_customize->add_setting( 'post_default_image', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	));
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'post_default_image', array(
		'label'   => __( 'Default Post Image', 'avidd' ),
		'section' => 'default_content_section',
	)));
	
	// Default Event Image
	$wp_customize->add_setting( 'event_default_image', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	));
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'event_default_image', array(
		'label'   => __( 'Default Event Image', 'avidd' ),
		'section' => 'default_content_section',
	)));
			$wp_customize->add_setting( "booking_link", array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		));
		$wp_customize->add_control( "booking_link", array(
			'label'   => __( 'Booking Link', 'avidd' ),
			'section'         => 'default_content_section',
			'type'            => 'url',

		));

	// ============================================
	// NOTIFICATIONS
	// ============================================

	// Header Notifications Section

	$wp_customize->add_setting( 'notifications', array(
		'default'           => '',
		'sanitize_callback' => 'avidd_sanitize_repeater',
	));
	$wp_customize->add_control( new Avidd_Repeater_Control( $wp_customize, 'notifications', array(
		'label'   => __( 'Notification messages', 'avidd' ),
		'section' => 'notifications_section',
		'fields'  => array(
			'notification_header' => array(
				'type'  => 'text',
				'label' => __( 'Header', 'avidd' ),
			),
			'notification_text' => array(
				'type'  => 'text',
				'label' => __( 'Text', 'avidd' ),
			),
			'notification_link' => array(
				'type'  => 'url',
				'label' => __( 'Link', 'avidd' ),
			),
		),
	)));

	// Email Notifications Section

	// Email logo
	$wp_customize->add_setting( 'email_logo', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	));
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'email_logo', array(
		'label'   => __( 'Email logo', 'avidd' ),
		'section' => 'email_notifications_section',
	)));
	// Resource Download Notifications

   $wp_customize->add_setting('resource_download_email', [
        'default'           => 'info@farthingaleslegal.co.uk',
        'sanitize_callback' => 'sanitize_email',
        'type'              => 'theme_mod',
    ]);

    $wp_customize->add_control('resource_download_email', [
        'label'   => 'Resource Download Notification Email',
        'section' => 'resources_notifications_section',
        'type'    => 'email',
    ]);
}
add_action( 'customize_register', 'avidd_customize_register' );



// ============================================
// SANITIZATION
// ============================================

function avidd_sanitize_repeater( $input ) {
	$decoded = json_decode( $input, true );
	if ( ! is_array( $decoded ) ) {
		return '';
	}
	return $input;
}

// ============================================
// CSS OUTPUT
// ============================================

function avidd_customizer_css() {
	?>
	<style type="text/css" id="avidd-customizer-styles">
		<?php
		// Nav background
		$nav_bg = get_theme_mod( 'color_palette_setting_0' );
		if ( $nav_bg ) {
			echo '.top-bar, .top-bar ul, .title-bar, #mega-menu-wrap-top-bar-r { background-color: ' . esc_attr( $nav_bg ) . '; }';
		}

		// Nav menu item color
		$nav_color = get_theme_mod( 'color_palette_setting_1' );
		if ( $nav_color ) {
			echo '.top-bar, .top-bar .desktop-menu a:not(.button), .title-bar .mobile-menu a:not(.button) { color: ' . esc_attr( $nav_color ) . '; }';
		}

		// Footer background
		$footer_bg = get_theme_mod( 'color_palette_setting_3' );
		if ( $footer_bg ) {
			echo '.footer { background-color: ' . esc_attr( $footer_bg ) . '; }';
		}

		// Footer text
		$footer_text = get_theme_mod( 'color_palette_setting_4' );
		if ( $footer_text ) {
			echo '.footer, .footer li { color: ' . esc_attr( $footer_text ) . '; }';
		}

		// Footer link
		$footer_link = get_theme_mod( 'color_palette_setting_5' );
		if ( $footer_link ) {
			echo '.footer a { color: ' . esc_attr( $footer_link ) . '; }';
		}

		// Page background
		$page_bg = get_theme_mod( 'color_palette_setting_10' );
		if ( $page_bg ) {
			echo 'body { background-color: ' . esc_attr( $page_bg ) . '; }';
		}
		?>
	</style>
	<?php
}
add_action( 'wp_head', 'avidd_customizer_css' );
