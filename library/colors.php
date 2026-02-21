<?php
/**
 * THEME JSON BRIDGE: ACF & KIRKI INTEGRATION
 *
 * Bridges the gap between theme.json and third-party plugins (ACF, Kirki).
 *
 * Key Features:
 * - Extracts color and gradient palettes directly from theme.json.
 * - Formats data for ACF Theme Swatch and Kirki choice fields.
 * - Optionally converts slugs to Gutenberg background classes on output
 *   (e.g. 'primary'       → 'has-primary-background-color',
 *         'primary-radial' → 'has-primary-radial-gradient-background')
 * - Or returns raw slugs for use in custom CSS, data attributes, etc.
 */


// =============================================================================
// DATA HELPERS
// =============================================================================

/**
 * Get color and gradient choices from theme.json for ACF / Kirki / templates.
 *
 * @param array $options {
 *   'include_colors'    => bool  Include solid colors. Default true.
 *   'include_gradients' => bool  Include gradients. Default true.
 *   'use_css_value'     => bool  Use raw CSS (HEX/gradient string) as array key. Default false.
 *   'for_kirki'         => bool  Format for Kirki choice fields. Default false.
 *   'for_acf'           => bool  Format for ACF Theme Swatch fields. Default false.
 * }
 * @return array Associative array of key => label choices.
 */
function get_theme_design_choices( $options = [] ) {
    $options = array_merge([
        'include_colors'    => true,
        'include_gradients' => true,
        'use_css_value'     => false,
        'for_kirki'         => false,
        'for_acf'           => false,
    ], $options );

    $theme_json = aviddj_get_theme_json();
    $choices    = [];

    if ( ! $theme_json ) return $choices;

    // Colors
    if ( $options['include_colors'] && ! empty( $theme_json['settings']['color']['palette'] ) ) {
        foreach ( $theme_json['settings']['color']['palette'] as $color ) {
            if ( $options['for_acf'] && $options['use_css_value'] ) {
                // ACF swatch with CSS keys — use HEX as key, name as label
                if ( ! empty( $color['color'] ) && preg_match( '/^#([a-f0-9]{3}){1,2}$/i', $color['color'] ) ) {
                    $choices[ $color['color'] ] = $color['name'] ?? $color['color'];
                }
            } else {
                $key = $color['color'] ?? sanitize_title( $color['slug'] ?? '' );
                if ( $key ) $choices[ $key ] = $color['name'] ?? $key;
            }
        }

        // Fallback so ACF swatch always has at least one option
        if ( $options['for_acf'] && $options['use_css_value'] && empty( $choices ) ) {
            $choices['#000000'] = 'Black';
        }
    } elseif ( $options['for_acf'] && $options['use_css_value'] ) {
        $choices['#000000'] = 'Black';
    }

    // Gradients
    if ( $options['include_gradients'] && ! empty( $theme_json['settings']['color']['gradients'] ) ) {
        foreach ( $theme_json['settings']['color']['gradients'] as $gradient ) {
            $slug = sanitize_title( $gradient['slug'] ?? '' );
            $name = $gradient['name'] ?? $slug;
            $css  = $gradient['gradient'] ?? '';

            if ( ! $slug ) continue;

            if ( $options['for_acf'] && $options['use_css_value'] ) {
                // ACF swatch with CSS keys — use gradient CSS string as key
                if ( $css ) $choices[ $css ] = $name;
            } elseif ( $options['for_acf'] || $options['for_kirki'] ) {
                $choices[ $slug ] = $name;
            } elseif ( $options['use_css_value'] ) {
                if ( $css ) $choices[ $slug ] = $css;
            } else {
                $choices[ $slug ] = $name;
            }
        }
    }

    return $choices;
}

/**
 * Convert a HEX color to its theme.json slug.
 *
 * @param  string $value HEX color string e.g. '#640FA1'.
 * @return string Slug if found, original value otherwise.
 */
function aviddjson_hex_to_slug( $value ) {
    if ( empty( $value ) || ! preg_match( '/^#([a-f0-9]{3}){1,2}$/i', $value ) ) {
        return $value;
    }

    $theme_json = aviddj_get_theme_json();
    if ( ! $theme_json ) return $value;

    foreach ( $theme_json['settings']['color']['palette'] ?? [] as $color ) {
        if ( ! empty( $color['color'] ) && ! empty( $color['slug'] ) ) {
            if ( strtolower( $color['color'] ) === strtolower( $value ) ) {
                return sanitize_title( $color['slug'] );
            }
        }
    }

    return $value;
}

/**
 * Convert a gradient CSS string to its theme.json slug.
 * Normalises whitespace before comparing to handle minor formatting differences.
 *
 * @param  string $value Gradient CSS string e.g. 'linear-gradient(135deg, #640FA1, #210535)'.
 * @return string Slug if found, original value otherwise.
 */
function aviddjson_gradient_to_slug( $value ) {
    if ( empty( $value ) || strpos( $value, 'gradient(' ) === false ) {
        return $value;
    }

    $theme_json = aviddj_get_theme_json();
    if ( ! $theme_json ) return $value;

    $normalized_value = preg_replace( '/\s+/', ' ', trim( $value ) );

    foreach ( $theme_json['settings']['color']['gradients'] ?? [] as $gradient ) {
        $slug       = sanitize_title( $gradient['slug'] ?? '' );
        $normalized = preg_replace( '/\s+/', ' ', trim( $gradient['gradient'] ?? '' ) );

        if ( $slug && $normalized === $normalized_value ) {
            return $slug;
        }
    }

    return $value;
}

/**
 * Convert a theme.json slug to a Gutenberg background class.
 *
 * Checks the color palette first — since some slugs exist in both the palette
 * and gradients (e.g. 'primary'), colors take priority. Falls back to gradients,
 * then defaults to a background-color class.
 *
 * @param  string $slug Theme color or gradient slug e.g. 'primary', 'primary-radial'.
 * @return string Gutenberg class e.g. 'has-primary-background-color'
 *                or 'has-primary-radial-gradient-background'.
 */
function aviddj_get_background_class( $slug ) {
    if ( empty( $slug ) ) return '';

    $theme_json = aviddj_get_theme_json();

    // Check colors first — palette slugs take priority over gradient slugs
    foreach ( $theme_json['settings']['color']['palette'] ?? [] as $color ) {
        if ( sanitize_title( $color['slug'] ?? '' ) === $slug ) {
            return 'has-' . $slug . '-background-color';
        }
    }

    // Then check gradients
    foreach ( $theme_json['settings']['color']['gradients'] ?? [] as $gradient ) {
        if ( sanitize_title( $gradient['slug'] ?? '' ) === $slug ) {
            return 'has-' . $slug . '-gradient-background';
        }
    }

    // Fallback — assume color
    return 'has-' . $slug . '-background-color';
}

/**
 * Internal helper: read and cache theme.json for the current theme.
 * Returns null if the file doesn't exist or contains invalid JSON.
 *
 * @return array|null Decoded theme.json array or null on failure.
 */
function aviddj_get_theme_json() {
    static $cache = null;

    if ( $cache !== null ) return $cache;

    $path = get_stylesheet_directory() . '/theme.json';

    if ( ! file_exists( $path ) ) return null;

    $decoded = json_decode( file_get_contents( $path ), true );

    if ( json_last_error() !== JSON_ERROR_NONE ) return null;

    $cache = $decoded;
    return $cache;
}


// =============================================================================
// ACF FILTERS
// =============================================================================

/**
 * Convert color_picker HEX values to their theme.json slug on format.
 */
add_filter( 'acf/format_value/type=color_picker', function( $value, $post_id, $field ) {
    return aviddjson_hex_to_slug( $value );
}, 10, 3 );


// =============================================================================
// ACF THEME SWATCH FIELD TYPE
// =============================================================================

/**
 * Custom ACF field type that renders color and gradient swatches from theme.json.
 *
 * - Choices are auto-populated from theme.json via load_field — no hardcoding needed.
 * - Stores the raw CSS value (HEX or gradient string) while editing.
 * - 'gutenberg_classes' ON  → get_field() returns 'has-primary-background-color'
 * - 'gutenberg_classes' OFF → get_field() returns the raw slug e.g. 'primary'
 */
class ACF_Field_Theme_Swatch extends acf_field {

    public function __construct() {
        $this->name     = 'theme_swatch';
        $this->label    = 'Theme Swatch';
        $this->category = 'choice';
        $this->defaults = [
            'choices'           => [],
            'allow_null'        => 0,
            'default_value'     => '',
            'include_colors'    => true,
            'include_gradients' => true,
            'gutenberg_classes' => true,
        ];
        parent::__construct();
    }

    public function render_field( $field ) {
        $choices = $field['choices'] ?? [];
        $value   = $field['value'] ?? '';
        $name    = $field['name'];
        $null    = $field['allow_null'] ?? 0;

        if ( empty( $choices ) && ! $null ) return;
        ?>
        <div class="acf-theme-swatches">

            <?php if ( $null ) : ?>
                <label class="swatch-item swatch-item--null">
                    <input type="radio" name="<?php echo esc_attr( $name ); ?>" value="" <?php checked( $value, '' ); ?> />
                    <span class="swatch swatch--none" title="<?php esc_attr_e( 'None' ); ?>">
                        <span class="swatch__cross"></span>
                    </span>
                </label>
            <?php endif; ?>

            <?php foreach ( $choices as $css_value => $label ) :
                $is_gradient = strpos( $css_value, 'gradient(' ) !== false;
                $class       = 'swatch ' . ( $is_gradient ? 'swatch--gradient' : 'swatch--color' );
            ?>
                <label class="swatch-item" title="<?php echo esc_attr( $label ); ?>">
                    <input
                        type="radio"
                        name="<?php echo esc_attr( $name ); ?>"
                        value="<?php echo esc_attr( $css_value ); ?>"
                        <?php checked( $value, $css_value ); ?>
                    />
                    <span class="<?php echo esc_attr( $class ); ?>" style="background: <?php echo esc_attr( $css_value ); ?>;"></span>
                </label>
            <?php endforeach; ?>

        </div>
        <?php
    }

    public function render_field_settings( $field ) {
        acf_render_field_setting( $field, [
            'label'         => 'Include Colors',
            'name'          => 'include_colors',
            'type'          => 'true_false',
            'ui'            => 1,
            'default_value' => 1,
        ]);

        acf_render_field_setting( $field, [
            'label'         => 'Include Gradients',
            'name'          => 'include_gradients',
            'type'          => 'true_false',
            'ui'            => 1,
            'default_value' => 1,
        ]);

        acf_render_field_setting( $field, [
            'label'         => 'Gutenberg Classes',
            'name'          => 'gutenberg_classes',
            'type'          => 'true_false',
            'ui'            => 1,
            'default_value' => 1,
            'instructions'  => 'ON returns e.g. "has-primary-background-color". OFF returns the raw slug e.g. "primary".',
        ]);
    }

    /**
     * Auto-populate choices from theme.json so field groups stay clean.
     * Uses ! empty() to safely handle ACF passing settings as "1"/"0" strings.
     */
    public function load_field( $field ) {
        $field['choices'] = get_theme_design_choices([
            'include_colors'    => ! empty( $field['include_colors'] ),
            'include_gradients' => ! empty( $field['include_gradients'] ),
            'for_acf'           => true,
            'use_css_value'     => true,
        ]);

        return $field;
    }

    /**
     * Convert stored CSS value to either a Gutenberg background class or raw slug.
     *
     * gutenberg_classes ON:
     *   '#640FA1'                    → 'has-primary-background-color'
     *   'linear-gradient(135deg, …)' → 'has-primary-gradient-background'
     *   'radial-gradient(ellipse …)' → 'has-primary-radial-gradient-background'
     *
     * gutenberg_classes OFF:
     *   '#640FA1'                    → 'primary'
     *   'linear-gradient(135deg, …)' → 'primary'
     *   'radial-gradient(ellipse …)' → 'primary-radial'
     */
    public function format_value( $value, $post_id, $field ) {
        if ( empty( $value ) ) return $value;

        // Convert HEX to slug
        if ( preg_match( '/^#([a-f0-9]{3}){1,2}$/i', $value ) ) {
            $value = aviddjson_hex_to_slug( $value );
        }

        // Convert gradient CSS string to slug
        if ( strpos( $value, 'gradient(' ) !== false ) {
            $value = aviddjson_gradient_to_slug( $value );
        }

        // Return Gutenberg class or raw slug depending on field setting
        if ( ! empty( $field['gutenberg_classes'] ) ) {
            return aviddj_get_background_class( $value );
        }

        return $value;
    }
}

add_action( 'acf/include_field_types', function () {
    new ACF_Field_Theme_Swatch();
});