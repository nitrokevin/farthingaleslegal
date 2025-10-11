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

    // Load custom colour palette
    $colors = [];
    if (file_exists(get_template_directory() . '/colors.php')) {
        include get_template_directory() . '/colors.php';
        if (!is_array($colors)) {
            $colors = [];
        }
    }

    $default_colours = [];
    foreach ($colors as $name => $hex) {
        if (strtolower($hex) === 'transparent') continue;
        $label = ucwords(str_replace(['-', '_'], ' ', $name));
        $default_colours[] = '"' . ltrim($hex, '#') . '"';
        $default_colours[] = '"' . $label . '"';
    }
    $init['textcolor_map'] = '[' . implode(', ', $default_colours) . ']';

    // Add custom style formats
    $init['style_formats'] = json_encode([
        [
            'title' => 'Standard Button main colour',
            'selector' => 'a',
            'classes' => 'button hollow',
        ],
        [
            'title' => 'Standard Button secondary colour',
            'selector' => 'a',
            'classes' => 'button hollow secondary',
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