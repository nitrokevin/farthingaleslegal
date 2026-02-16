<?php
/**
 * The template for displaying all single posts and attachments
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

get_header(); ?>

<header class="page-hero post">
    <div class="marketing">
        <div class="tagline">
            <h1 class="entry-title"><?php the_title(); ?></h1>
    
        </div>

    </div>
</header>

<div class="main-container">
	<div class="main-grid">
		<main class="main-content-full-width">
			<?php while ( have_posts() ) : the_post(); ?>
				<?php get_template_part( 'template-parts/content', 'services' ); ?>
			<?php endwhile; ?>
		</main>
	</div>
</div>
<?php get_footer();
