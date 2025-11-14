<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * e.g., it puts together the home page when no home.php file exists.
 *
 * Learn more: {@link https://codex.wordpress.org/Template_Hierarchy}
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

get_header(); ?>
<header class="page-hero">
    <div class="marketing">
        <div class="tagline">
            <h1 class="entry-title"><?php echo get_the_title(get_option('page_for_posts')); ?></h1>
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
                        'fp-medium', // desired size
                        false, 
                        [
                            'class' => '',
                            'alt'   => $alt_text ?: get_the_title(),
                             'sizes' => '(max-width: 640px) 100vw, (max-width: 1200px) 50vw, 500px'
                        ] 
                    ); 
                ?>
            </div>
        <?php endif; ?>
    </div>
</header>
<div class="main-container">
	<div class="main-grid">
		<main class="main-content">
		<?php if ( have_posts() ) : ?>

			<?php /* Start the Loop */ ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<?php get_template_part( 'template-parts/content', get_post_format() ); ?>
			<?php endwhile; ?>

			<?php else : ?>
				<?php get_template_part( 'template-parts/content', 'none' ); ?>

			<?php endif; // End have_posts() check. ?>

			<?php /* Display navigation to next/previous pages when applicable */ ?>
			<?php
			if ( function_exists( 'foundationpress_pagination' ) ) :
				foundationpress_pagination();
			elseif ( is_paged() ) :
			?>
				<nav id="post-nav" aria-label="Post navigation">
					<div class="post-previous"><?php next_posts_link( __( '&larr; Older posts', 'foundationpress' ) ); ?></div>
					<div class="post-next"><?php previous_posts_link( __( 'Newer posts &rarr;', 'foundationpress' ) ); ?></div>
				</nav>
			<?php endif; ?>

		</main>
		<?php get_sidebar(); ?>

	</div>
</div>
<?php get_footer();
