<?php
if ( ! function_exists( 'foundationpress_gutenberg_support' ) ) :
    function foundationpress_gutenberg_support() {
        // Load colors
        include 'colors.php';
        $editor_colors = array();

        foreach ( $colors as $slug => $color ) {
            $name = ucwords(str_replace(array('-', '_'), ' ', $slug));
            $editor_colors[] = array(
                'name'  => __( $name, 'foundationpress' ),
                'slug'  => $slug,
                'color' => $color,
            );
        }

        add_theme_support( 'editor-color-palette', $editor_colors );


        // Load gradients
        include 'gradients.php';
        $editor_gradients = array();

        foreach ( $gradients as $slug => $gradient ) {
            $name = ucwords(str_replace(array('-', '_'), ' ', $slug));
            $editor_gradients[] = array(
                'name'     => __( $name, 'foundationpress' ),
                'slug'     => $slug,
                'gradient' => $gradient,
            );
        }

        add_theme_support( 'editor-gradient-presets', $editor_gradients );

        // Optional: disable WP defaults
        add_theme_support( 'disable-custom-gradients' ); // stops user-defined ones too
    }
    add_action( 'after_setup_theme', 'foundationpress_gutenberg_support' );
endif;