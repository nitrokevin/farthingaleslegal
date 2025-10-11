<?php
/**
 * The template for displaying pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages and that
 * other "pages" on your WordPress site will use a different template.
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

get_header();

?>

<header class="page-hero">
    <div class="marketing">
        <div class="tagline">
            <h1 class="entry-title"><?php the_title(); ?></h1>
            <p><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p>

            <?php 
            $intro_url  = get_field('intro_button_url');
            $intro_text = get_field('intro_button_text');
            if ( $intro_url && $intro_text ) : ?>
                <div class="intro-button">
                    <a href="<?php echo esc_url($intro_url); ?>" class="button theme-color-2">
                        <?php echo esc_html($intro_text); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <?php if ( has_post_thumbnail() ) : 
            $thumb_id = get_post_thumbnail_id(); 
            $alt_text = get_post_meta($thumb_id, '_wp_attachment_image_alt', true); 
        ?>
            <div class="hero-image">
                <?php 
                    echo wp_get_attachment_image( 
                        $thumb_id, 
                        'fp-large', // desired size
                        false, 
                        [
                            'class' => '',
                            'alt'   => $alt_text ?: get_the_title(),
                            'sizes' => '(max-width: 600px) 100vw, (max-width: 1200px) 50vw, 50vw',
                        ] 
                    ); 
                ?>
            </div>
        <?php endif; ?>
    </div>
</header>
<?php if ( !empty( get_the_content() ) ) {?> 
<div class="main-container">
	<div class="main-grid">
		<main class="main-content-full-width">
			<?php while ( have_posts() ) : the_post(); ?>
				<?php get_template_part( 'template-parts/content', 'page' ); ?>
			
			<?php endwhile; ?>
		</main>
	</div>
</div>
<?php } ?>
<?php
get_footer();
