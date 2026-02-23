<?php
/**
 * Theme Custom Functions
 * ACF, TinyMCE, Embed, and Admin Tweaks
 */

// ------------------------------------------------------------
// ACF CONTENT FILTERS
// ------------------------------------------------------------

/**
 * Make ACF WYSIWYG content images responsive
 */
add_filter('acf_the_content', 'wp_make_content_images_responsive');

/**
 * Remove <p> wrappers around images and replace with <figure>
 */
function img_unautop($content) {
    return preg_replace('/<p>\s*(<a .*?><img.*?><\/a>|<img.*?>)\s*<\/p>/s', '<figure class="figure">$1</figure>', $content);
}
add_filter('acf_the_content', 'img_unautop', 30);

/**
 * Remove <p> wrappers around buttons (<a> elements)
 */
function a_unautop($content) {
    return preg_replace('/<p>\s*(<a .*?>.*?<\/a>)\s*<\/p>/s', '$1', $content);
}
add_filter('acf_the_content', 'a_unautop', 30);

/**
 * Dynamic ACF field population
 * Used for populating select fields with data from ACF options pages or custom sources.
 */

add_filter('acf/load_field/name=options_page_selector', function($field) {
    $field['choices'] = [];

    if (function_exists('acf_get_options_pages')) {
        $options_pages = acf_get_options_pages();
        if ($options_pages) {
            foreach ($options_pages as $slug => $page) {
                $field['choices'][$slug] = $page['page_title'];
            }
        }
    }

    return $field;
});

// ------------------------------------------------------------
// GUTENBERG SUPPORT
// ------------------------------------------------------------

add_theme_support('align-wide');

function custom_block_categories($categories) {
    return array_merge(
        $categories,
        [
            [
                'slug'  => 'avidd',
                'title' => __('AVIDD Blocks', 'avidd'),
            ],
        ]
    );
}
add_action('block_categories_all', 'custom_block_categories', 10, 2);

/**
 * Automatically generate unique anchors for ACF blocks
 */
function set_unique_acf_block_anchor($attributes) {
    if (empty($attributes['anchor'])) {
        $attributes['anchor'] = 'acf-block-' . uniqid();
    }
    return $attributes;
}
add_filter('acf/pre_save_block', 'set_unique_acf_block_anchor');

// ------------------------------------------------------------
// TINYMCE CUSTOMISATIONS
// ------------------------------------------------------------


add_filter('tiny_mce_before_init', 'customise_tinymce');

function customise_tinymce($init) {
    // Always paste as plain text
    $init['paste_as_text'] = true;

    // Load custom colour palette via helper (returns ['#hex' => 'Name', ...])
    $default_colours = [];

    if ( function_exists( 'get_theme_design_choices' ) ) {
        $choices = get_theme_design_choices([
            'include_colors'    => true,
            'include_gradients' => false,
            'key'               => 'color', // we want HEX keys for the editor
            'for_acf'           => false,   // not needed here
        ]);

        if (is_array($choices) && count($choices)) {
            foreach ($choices as $key => $label) {
                // $key should be a HEX like '#ffffff'
                if (!is_string($key)) {
                    continue;
                }
                $hex = trim($key);
                if (strtolower($hex) === 'transparent') {
                    continue;
                }
                // guard: only include hex values that start with #
                if (strpos($hex, '#') !== 0) {
                    continue;
                }
                $default_colours[] = '"' . ltrim($hex, '#') . '"';
                $default_colours[] = '"' . esc_js($label) . '"';
            }
        }
    }

    // fallback: if no colours found, use a single neutral so editor won't fall back to core defaults
    if (empty($default_colours)) {
        $default_colours[] = '"000000"';
        $default_colours[] = '"Black"';
    }

    $init['textcolor_map'] = '[' . implode(', ', $default_colours) . ']';

    // Add custom style formats
    $init['style_formats'] = json_encode([
        [
            'title' => 'Primary Button',
            'selector' => 'a',
            'classes' => 'button',
        ],
          [
            'title' => 'Secondary button',
            'selector' => 'a',
            'classes' => 'button secondary',
        ],
        [
            'title' => 'Theme color 1 button',
            'selector' => 'a',
            'classes' => 'button theme-color-1',
        ],
        [
            'title' => 'Theme color 2 button',
            'selector' => 'a',
            'classes' => 'button theme-color-2',
        ],
    ]);

    return $init;
}

/**
 * Add 'styleselect' dropdown to TinyMCE toolbar
 */
function my_mce_buttons_2($buttons) {
    array_unshift($buttons, 'styleselect');
    return $buttons;
}
add_filter('mce_buttons_2', 'my_mce_buttons_2');




// ------------------------------------------------------------
// EMBED AND VIDEO RESPONSIVENESS
// ------------------------------------------------------------

/**
 * Wrap oEmbed content in a responsive container
 */
function wrap_embed_html($html) {
    return '<div class="responsive-embed">' . $html . '</div>';
}
add_filter('embed_oembed_html', 'wrap_embed_html', 10, 3);
add_filter('video_embed_html', 'wrap_embed_html');

/**
 * Add YouTube oEmbed parameters for cleaner display
 */
function modify_oembed_youtube($html, $url, $attr, $post_id) {
    if (strpos($html, 'feature=oembed') !== false) {
        return str_replace(
            'feature=oembed',
            'feature=oembed&amp;rel=0&modestbranding=1&showinfo=0',
            $html
        );
    }
    return $html;
}
add_filter('embed_oembed_html', 'modify_oembed_youtube', 10, 4);

// ------------------------------------------------------------
// ACF FIXES & GOOGLE MAP KEY
// ------------------------------------------------------------

/**
 * Fix issue with ACF fields missing in preview
 */
if (class_exists('acf_revisions')) {
    $acf_revs_cls = acf()->revisions;
    remove_filter('acf/validate_post_id', [$acf_revs_cls, 'acf_validate_post_id'], 10);
}

/**
 * Set ACF Google Maps API key
 * Replace with an environment variable or ACF options field for safety
 */
function my_acf_google_map_api($api) {
    $api['key'] = getenv('GOOGLE_MAPS_API_KEY'); // Use env var or ACF option
    return $api;
}
add_filter('acf/fields/google_map/api', 'my_acf_google_map_api');

// ------------------------------------------------------------
// MISC
// ------------------------------------------------------------

/**
 * Enable excerpts on pages
 */
add_post_type_support('page', 'excerpt');

/**
 * Remove Comments from Admin Menu
 */
function my_remove_admin_menus() {
    remove_menu_page('edit-comments.php');
}
add_action('admin_menu', 'my_remove_admin_menus');


function avidd_social_links_inline_shortcode($atts) {
    $atts = shortcode_atts([
        'class' => '', // allow user to pass a class
    ], $atts, 'social_links');

    $links = [];

    $social_sites = [
        'facebook' => 'fa-brands fa-facebook-f',
        'twitter'  => 'fa-brands fa-x-twitter',
        'instagram'=> 'fa-brands fa-instagram',
        'linkedin' => 'fa-brands fa-linkedin-in',
        'pinterest'=> 'fa-brands fa-pinterest',
        'tiktok'   => 'fa-brands fa-tiktok',
    ];

    foreach ($social_sites as $key => $icon_class) {
        if (get_theme_mod('social-' . $key)) {
            $url = esc_url(get_theme_mod('social-' . $key . '-url'));
            $links[] = '<a href="' . $url . '" target="_blank" rel="noreferrer" aria-label="' . ucfirst($key) . '" class="social-inline ' . esc_attr($atts['class']) . '"><i class="' . $icon_class . '"></i></a>';
        }
    }

    return implode(' ', $links);
}
add_shortcode('social_links', 'avidd_social_links_inline_shortcode');

// Register the shortcode for opening times
function opening_times_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'title' => esc_html__('Opening Hours', 'avidd'), 
        ),
        $atts,
        'opening_times'
    );
    
    // Use the helper function
    $opening_times = avidd_get_repeater_data('opening_times');
    
    if ( ! empty( $opening_times ) ) {
        ob_start(); // Start output buffering
        
        // Output the opening times list
        echo '<p class="opening-times-header">';
        echo '<strong>Office Hours</strong>';
        echo '</p>';
        echo '<ul class="opening-times">';
        foreach ( $opening_times as $time ) {
            $day = isset( $time['day'] ) ? esc_html( $time['day'] ) : '';
            $opening_time = isset( $time['opening_time'] ) ? esc_html( $time['opening_time'] ) : '';
            $closing_time = isset( $time['closing_time'] ) ? esc_html( $time['closing_time'] ) : '';
            $note = isset( $time['note'] ) ? esc_html( $time['note'] ) : '';
            
            // Build hours string
            $hours = '';
            if ( $opening_time && $closing_time ) {
                $hours = $opening_time . ' - ' . $closing_time;
            } elseif ( $opening_time ) {
                $hours = $opening_time;
            } elseif ( $closing_time ) {
                $hours = $closing_time;
            }
            
            if ( $note ) {
                $hours = $note; // Use note instead if provided
            }
            ?>
            <li>
                <?php echo $day; ?><br />
                <strong><?php echo $hours; ?></strong>
            </li>
            <?php
        }
        
        echo '</ul>';
        echo '<small></small>';
        
        return ob_get_clean(); // Return the output buffer content
    }
    
    return ''; // Return empty if no opening times are set
}
// Register the shortcode [opening_times]
add_shortcode('opening_times', 'opening_times_shortcode');

// Shortcode: [contact_details]
function contact_details_shortcode( $atts ) {
    $atts = shortcode_atts(
        array(
            'contact_title'        => esc_html__( 'Contact', 'avidd' ),
            'address_title'        => esc_html__( 'Correspondence Address', 'avidd' ),
            'wrapper_class'        => 'contact-details-block',
            'show_email_as_link'   => '1',
            'show_phone_as_link'   => '1',
        ),
        $atts,
        'contact_details'
    );
    
    $contact_phone = get_theme_mod( 'contact_phone_number' );
    $contact_email = get_theme_mod( 'contact_email' );
    $address_lines = array_filter(
        array(
            get_theme_mod( 'contact_address_1' ),
            get_theme_mod( 'contact_address_2' ),
            get_theme_mod( 'contact_address_3' ),
            get_theme_mod( 'contact_address_4' ),
            get_theme_mod( 'contact_address_5' ),
            get_theme_mod( 'contact_address_6' ),
        )
    );
    
    // If absolutely nothing is set, return nothing.
    if ( empty( $contact_phone ) && empty( $contact_email ) && empty( $address_lines ) ) {
        return '';
    }
    
    ob_start();
    echo '<div class="' . esc_attr( $atts['wrapper_class'] ) . '">';
    
    // --- Contact section ---
    if ( ! empty( $contact_phone ) || ! empty( $contact_email ) ) {
        echo '<p class="contact-details-header"><strong>' . esc_html( $atts['contact_title'] ) . '</strong></p>';
        echo '<ul class="contact-details">';
        
        if ( ! empty( $contact_phone ) ) {
            echo '<li class="contact-details-phone">';
            if ( $atts['show_phone_as_link'] === '1' ) {
                $tel = preg_replace( '/[^0-9\+]/', '', $contact_phone );
                echo '<a href="tel:' . esc_attr( $tel ) . '">' . esc_html( $contact_phone ) . '</a>';
            } else {
                echo esc_html( $contact_phone );
            }
            echo '</li>';
        }
        
        if ( ! empty( $contact_email ) ) {
            echo '<li class="contact-details-email">';
            if ( $atts['show_email_as_link'] === '1' ) {
                echo '<a href="mailto:' . antispambot( esc_attr( $contact_email ) ) . '">'
                    . antispambot( esc_html( $contact_email ) )
                    . '</a>';
            } else {
                echo antispambot( esc_html( $contact_email ) );
            }
            echo '</li>';
        }
        
        echo '</ul>';
    }
    
    // --- Address section ---
    if ( ! empty( $address_lines ) ) {
        echo '<p class="contact-details-header"><strong>' . esc_html( $atts['address_title'] ) . '</strong></p>';
        echo '<div class="contact-details-address">';
        echo nl2br( esc_html( implode( "\n", $address_lines ) ) );
        echo '</div>';
    }
    
    echo '</div>';
    echo '<br />';
    
    return ob_get_clean();
}
add_shortcode( 'contact_details', 'contact_details_shortcode' );

// --- Handle AJAX form submission ---
add_action('wp_ajax_nopriv_save_email', 'save_email');
add_action('wp_ajax_save_email', 'save_email');
function save_email() {
    $email        = sanitize_email($_POST['email']);
    $name         = sanitize_text_field($_POST['user_name']);
    $file         = esc_url_raw($_POST['file']);
    $mailing_list = !empty($_POST['mailing_list']) && $_POST['mailing_list'] === '1';
    $opt_in_label = $mailing_list ? 'Yes' : 'No';

    if (!is_email($email)) {
        wp_send_json_error('Invalid email');
    }

    // Get filename from URL
    $filename = basename(parse_url($file, PHP_URL_PATH));
    $filename = urldecode($filename);

    $to      = get_theme_mod('resource_download_email', 'info@farthingaleslegal.co.uk');
    $subject = 'New resource download — ' . $name;
    $headers = ['Content-Type: text/html; charset=UTF-8'];

    $logo_url = get_theme_mod('email_logo', '');

    $message = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
    </head>
    <body style="margin:0;padding:0;background-color:#f4f4f4;font-family:Arial,sans-serif;">
        <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4;padding:30px 0;">
            <tr>
                <td align="center">
                    <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:6px;overflow:hidden;">
                        
                        <!-- Header -->
                        <tr>
                            <td style="background-color:#ffffff;padding:30px;text-align:center;">
                                ' . ($logo_url ? '<img src="' . esc_url($logo_url) . '" alt="Logo" style="max-height:60px;max-width:200px;">' : '<h2 style="color:#ffffff;margin:0;">' . get_bloginfo('name') . '</h2>') . '
                            </td>
                        </tr>

                        <!-- Body -->
                        <tr>
                            <td style="padding:40px 30px;">
                                <h2 style="margin:0 0 20px;color:#1a1a1a;font-size:20px;">New Resource Download</h2>
                                <p style="margin:0 0 30px;color:#555555;font-size:15px;">Someone has downloaded a resource from your website.</p>
                                
                                <!-- Details table -->
                                <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #eeeeee;border-radius:4px;">
                                    <tr>
                                        <td style="padding:12px 16px;background:#f9f9f9;border-bottom:1px solid #eeeeee;width:35%;">
                                            <strong style="color:#1a1a1a;font-size:14px;">Name</strong>
                                        </td>
                                        <td style="padding:12px 16px;border-bottom:1px solid #eeeeee;">
                                            <span style="color:#555555;font-size:14px;">' . esc_html($name) . '</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding:12px 16px;background:#f9f9f9;border-bottom:1px solid #eeeeee;">
                                            <strong style="color:#1a1a1a;font-size:14px;">Email</strong>
                                        </td>
                                        <td style="padding:12px 16px;border-bottom:1px solid #eeeeee;">
                                            <a href="mailto:' . esc_attr($email) . '" style="color:#555555;font-size:14px;">' . esc_html($email) . '</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding:12px 16px;background:#f9f9f9;border-bottom:1px solid #eeeeee;">
                                            <strong style="color:#1a1a1a;font-size:14px;">File</strong>
                                        </td>
                                        <td style="padding:12px 16px;border-bottom:1px solid #eeeeee;">
                                            <a href="' . esc_url($file) . '" style="color:#0073aa;font-size:14px;">' . esc_html($filename) . '</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding:12px 16px;background:#f9f9f9;border-bottom:1px solid #eeeeee;">
                                            <strong style="color:#1a1a1a;font-size:14px;">Mailing List</strong>
                                        </td>
                                        <td style="padding:12px 16px;border-bottom:1px solid #eeeeee;">
                                            <span style="color:#555555;font-size:14px;">' . $opt_in_label . '</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding:12px 16px;background:#f9f9f9;">
                                            <strong style="color:#1a1a1a;font-size:14px;">Date</strong>
                                        </td>
                                        <td style="padding:12px 16px;">
                                            <span style="color:#555555;font-size:14px;">' . date('j F Y, g:ia') . '</span>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <!-- Footer -->
                        <tr>
                            <td style="padding:20px 30px;background:#f9f9f9;border-top:1px solid #eeeeee;text-align:center;">
                                <p style="margin:0;color:#999999;font-size:12px;">' . get_bloginfo('name') . ' &mdash; ' . get_bloginfo('url') . '</p>
                            </td>
                        </tr>

                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>';

    wp_mail($to, $subject, $message, $headers);
    wp_send_json_success('emailed');
}

function get_latest_tagged_post( string $tag_slug, array $args = [] ) {

    $defaults = [
        'post_type'      => 'post',
        'posts_per_page' => 1,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'tag'            => $tag_slug,
    ];

    $query = new WP_Query( array_merge( $defaults, $args ) );

    if ( ! $query->have_posts() ) {
        return false;
    }

    $query->the_post();

    $data = [
        'title' => get_the_title(),
        'link'  => get_permalink(),
        'id'    => get_the_ID(),
    ];

    wp_reset_postdata();

    return $data;
}



// ------------------------------------------------------------
// Branded email notification
// ------------------------------------------------------------
// 1. Force CF7 emails to HTML
add_filter('wp_mail_content_type', function($content_type) {
    return 'text/html';
});

// 2. Add your HTML template wrapper
add_filter('wpcf7_mail_components', function($components, $contact_form, $mail) {
    // Get the logo URL
$logo_id = get_theme_mod('email_logo');
$logo_url = '';

// Convert attachment ID to URL
if ($logo_id) {
    $logo_url = wp_get_attachment_image_url($logo_id, 'full');
}

// Fallback to site icon
if (empty($logo_url)) {
    $site_icon_id = get_option('site_icon');
    if ($site_icon_id) {
        $logo_url = wp_get_attachment_image_url($site_icon_id, 'full');
    }
}
    $site_name = wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);
    $site_url = home_url('/');
    
    // Fallback to site icon
    if (empty($logo_url)) {
        $site_icon_id = get_option('site_icon');
        if ($site_icon_id) {
            $logo_url = wp_get_attachment_image_url($site_icon_id, 'full');
        }
    }
    
    // Get the message and convert to paragraphs
    $message_html = wpautop($components['body']);
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head><meta charset="utf-8"></head>
    <body style="margin:0; padding:0; background:#f6f6f6;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f6f6f6; padding:24px 12px;">
            <tr><td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background:#fff; border-radius:12px; overflow:hidden;">
                    <tr>
                        <td style="padding:22px 24px;">';
    
    if (!empty($logo_url)) {
        $html .= '<a href="'.esc_url($site_url).'"><img src="'.esc_url($logo_url).'" alt="'.esc_attr($site_name).'" style="max-height:46px; height:auto; display:block;"></a>';
    } else {
        $html .= '<div style="font-weight:700; font-size:18px;">'.esc_html($site_name).'</div>';
    }
    
    $html .= '
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px; font-family:Arial, sans-serif; font-size:15px; line-height:1.5; color:#222;">
                            '.$message_html.'
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 24px; background:#fafafa; border-top:1px solid #eee; font-size:12px; color:#666; font-family:Arial, sans-serif;">
                            <a href="'.esc_url($site_url).'" style="color:#666; text-decoration:none;">'.$site_name.'</a><br>
                        </td>
                    </tr>
                </table>
            </td></tr>
        </table>
    </body>
    </html>';
    
    $components['body'] = $html;
    
    return $components;
}, 10, 3);

// 3. Reset content type after CF7 sends (important!)
add_action('wpcf7_mail_sent', function() {
    remove_filter('wp_mail_content_type', function($content_type) {
        return 'text/html';
    });
});




// Shortcode for booking link
function avidd_booking_link_shortcode() {
	return esc_url( get_theme_mod( 'booking_link' ) );
}
add_shortcode( 'booking_link', 'avidd_booking_link_shortcode' );

// Replace #booking_link# in block and menu output
add_filter( 'render_block', function( $block_content, $block ) {
	if ( empty( $block_content ) ) {
		return $block_content;
	}
	$booking_link = esc_url( get_theme_mod( 'booking_link' ) );
	if ( $booking_link ) {
		$block_content = str_replace( '#booking_link#', $booking_link, $block_content );
	}
	return $block_content;
}, 10, 2 );

add_filter( 'nav_menu_link_attributes', function( $atts, $item, $args, $depth ) {
	$booking_link = esc_url( get_theme_mod( 'booking_link' ) );
	if ( isset( $atts['href'] ) && $atts['href'] === '#booking_link#' && $booking_link ) {
		$atts['href'] = $booking_link;
	}
	return $atts;
}, 10, 4 );

add_filter( 'acf/load_value/name=intro_button_url', function( $value, $post_id, $field ) {
    if ( empty( $value ) ) {
        return $value;
    }
    $booking_link = esc_url( get_theme_mod( 'booking_link' ) );
    if ( $booking_link ) {
        $value = str_replace( '#booking_link#', $booking_link, $value );
    }
    return $value;
}, 10, 3 );
