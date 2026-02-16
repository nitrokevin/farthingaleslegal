<?php
/**
 * The template for displaying archive pages
 *
 * Used to display archive-type pages if nothing more specific matches a query.
 * For example, puts together date-based pages if no date.php file exists.
 *
 * If you'd like to further customize these archive views, you may create a
 * new template file for each one. For example, tag.php (Tag archives),
 * category.php (Category archives), author.php (Author archives), etc.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */
get_header(); 
$intro_text = get_field('intro_text');
?>

<header class="page-hero">
    <div class="marketing">
        <div class="tagline">
            <h1 class="entry-title">What we do</h1>
            <p><?php echo esc_html( $intro_text ); ?></p>

            <?php 
            $intro_button_url  = get_field('intro_button_url');
            $intro_button_text = get_field('intro_button_text');
            if ( $intro_button_url && $intro_button_text ) : ?>
                <div class="intro-button">
                    <a href="<?php echo esc_url($intro_button_url); ?>" class="button theme-color-2">
                        <?php echo esc_html($intro_button_text); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
	</div>

</header>
<div class="main-container">
	<div class="main-grid">
		<main class="main-content-full-width services-archive cards-container">
		<?php if ( have_posts() ) : ?>

			<?php /* Start the Loop */ ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<?php get_template_part( 'template-parts/content', 'services' ); ?>
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
				<nav id="post-nav">
					<div class="post-previous"><?php next_posts_link( __( '&larr; Older posts', 'foundationpress' ) ); ?></div>
					<div class="post-next"><?php previous_posts_link( __( 'Newer posts &rarr;', 'foundationpress' ) ); ?></div>
				</nav>
			<?php endif; ?>

		</main>
		<!-- <?php get_sidebar(); ?> -->

	</div>
</div>

<?php get_footer();
