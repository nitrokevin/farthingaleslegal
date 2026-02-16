<?php
/**
 * THEME JSON BRIDGE: ACF & KIRKI INTEGRATION
 * * This script provides helper functions to bridge the gap between theme.json 
 * and third-party plugins (ACF, Kirki). 
 * * Key Features:
 * - Extracts color and gradient palettes directly from theme.json.
 * - Formats data for ACF Swatch and Kirki choice fields.
 * - Automatically maps HEX/Gradient CSS values back to CSS Slugs via filters
 * (e.g., converting '#0073aa' to 'primary') for consistent CSS variable usage.
 */
/**
 * Get color and gradient choices from theme.json for ACF / Kirki / templates
 *
 * @param array $options Options:
 *   'include_colors'   => bool, default true
 *   'include_gradients'=> bool, default true
 *   'use_css_value'    => bool, default false; for gradients, return CSS value instead of slug
 *   'for_kirki'        => bool, default false; if true, returns choices formatted for Kirki
 *   'for_acf'          => bool, default false; if true, returns choices formatted for ACF swatch fields with gradients
 *
 * @return array Flat associative array suitable for ACF 'choices' (HEX or slug keys => Name) or plain array for Kirki
 */

function get_theme_design_choices($options = []) {
    $defaults = [
        'include_colors' => true,
        'include_gradients' => true,
        'use_css_value' => false,
        'for_kirki' => false,
        'for_acf' => false,
    ];

    $options = array_merge($defaults, $options);
    $theme_json_path = get_stylesheet_directory() . '/theme.json';
    $choices_for_editor = [];

    if (!file_exists($theme_json_path)) {
        return $choices_for_editor;
    }

    $json = json_decode(file_get_contents($theme_json_path), true);

    // Colors
    if ($options['include_colors'] && isset($json['settings']['color']['palette'])) {
        foreach ($json['settings']['color']['palette'] as $color) {
            if ($options['for_acf'] && $options['use_css_value']) {
                // For ACF swatch fields with gradients and colors, use HEX => HEX
                if (isset($color['color']) && preg_match('/^#([a-f0-9]{3}){1,2}$/i', $color['color'])) {
                    $choices_for_editor[$color['color']] = $color['color'];
                }
            } else {
                $key = $color['color'] ?? sanitize_title($color['slug'] ?? '');
                if ($key) $choices_for_editor[$key] = $color['name'];
            }
        }
        if ($options['for_acf'] && $options['use_css_value'] && empty($choices_for_editor)) {
            $choices_for_editor['#000000'] = '#000000';
        }
    } elseif ($options['for_acf'] && $options['use_css_value']) {
        $choices_for_editor['#000000'] = '#000000';
    }

    // Gradients (supports linear, radial, conic, and repeating variants)
    if ($options['include_gradients'] && isset($json['settings']['color']['gradients'])) {
        foreach ($json['settings']['color']['gradients'] as $gradient) {
            if ($options['for_acf'] && $options['use_css_value'] && isset($gradient['gradient'])) {
                // For ACF swatch fields with gradients, return CSS => CSS
                $choices_for_editor[$gradient['gradient']] = $gradient['gradient'];
            } elseif ($options['for_acf']) {
                // For ACF swatch fields without use_css_value, return slug => name
                $slug = sanitize_title($gradient['slug'] ?? '');
                if ($slug) $choices_for_editor[$slug] = $gradient['name'];
            } elseif ($options['for_kirki']) {
                $slug = sanitize_title($gradient['slug'] ?? '');
                if ($slug) $choices_for_editor[$slug] = $gradient['name'];
            } elseif ($options['use_css_value']) {
                $slug = sanitize_title($gradient['slug'] ?? '');
                if ($slug && isset($gradient['gradient'])) {
                    $choices_for_editor[$slug] = $gradient['gradient'];
                }
            } else {
                $slug = sanitize_title($gradient['slug'] ?? '');
                if ($slug) $choices_for_editor[$slug] = $gradient['name'];
            }
        }
    }

    return $choices_for_editor;
}

// ACF filter to convert HEX or gradient CSS value to slug when returning the value for swatch fields
add_filter('acf/format_value/type=swatch', function($value, $post_id, $field) {
    if (empty($value)) {
        return $value;
    }

    // Check if it's a hex color or any type of gradient (linear, radial, conic, repeating, etc.)
    $is_hex = preg_match('/^#([a-f0-9]{3}){1,2}$/i', $value);
    $is_gradient = (strpos($value, 'gradient(') !== false);
    
    if (!$is_hex && !$is_gradient) {
        return $value;
    }

    $slug_map = [];
    $theme_json_path = get_stylesheet_directory() . '/theme.json';
    if (file_exists($theme_json_path)) {
        $json = json_decode(file_get_contents($theme_json_path), true);

        // Colors
        if (isset($json['settings']['color']['palette'])) {
            foreach ($json['settings']['color']['palette'] as $color) {
                if (!empty($color['color'])) {
                    $slug = sanitize_title($color['slug'] ?? '');
                    if ($slug) {
                        $slug_map[$color['color']] = $slug;
                    }
                }
            }
        }

        // Gradients (linear, radial, conic, repeating)
        if (isset($json['settings']['color']['gradients'])) {
            foreach ($json['settings']['color']['gradients'] as $gradient) {
                $slug = sanitize_title($gradient['slug'] ?? '');
                if ($slug && isset($gradient['gradient'])) {
                    // Normalize whitespace for comparison
                    $normalized_gradient = preg_replace('/\s+/', ' ', trim($gradient['gradient']));
                    $normalized_value = preg_replace('/\s+/', ' ', trim($value));
                    
                    $slug_map[$gradient['gradient']] = $slug;
                    $slug_map[$normalized_gradient] = $slug;
                    
                    // Also map the normalized version of the incoming value
                    if ($normalized_gradient === $normalized_value) {
                        return $slug;
                    }
                }
            }
        }
    }

    return $slug_map[$value] ?? $value;
}, 10, 3);

function aviddjson_hex_to_slug( $value ) {
    if (empty($value)) {
        return $value;
    }

    // Must be hex for slug lookup
    if (!preg_match('/^#([a-f0-9]{3}){1,2}$/i', $value)) {
        return $value;
    }

    $theme_json_path = get_stylesheet_directory() . '/theme.json';
    if (!file_exists($theme_json_path)) {
        return $value;
    }

    $json = json_decode(file_get_contents($theme_json_path), true);
    $palette = $json['settings']['color']['palette'] ?? [];

    $slug_map = [];

    foreach ($palette as $color) {
        if (!empty($color['color']) && !empty($color['slug'])) {
            $hex  = strtolower($color['color']);
            $slug = sanitize_title($color['slug']);
            $slug_map[$hex] = $slug;
        }
    }

    // Convert hex to slug if it exists, otherwise return original hex
    $value_lower = strtolower($value);
    return $slug_map[$value_lower] ?? $value;
}

add_filter('acf/format_value/type=color_picker', function($value, $post_id, $field) {
    return aviddjson_hex_to_slug($value);
}, 10, 3);
?>