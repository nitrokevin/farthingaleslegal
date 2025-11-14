<?php
/*
Template Name: Front
*/
get_header(); 
 ?>


<header class="front-hero">
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
                        'fp-large', // adjust size if needed
                        false, 
                        [
                            'class' => '',
                            'alt'   => $alt_text ?: get_the_title(),
                             'sizes' => '(max-width: 640px) 100vw, (max-width: 1200px) 50vw, 600px'
                        ] 
                    ); 
                ?>
            </div>
        <?php endif; ?>
    </div>
</header>


<?php do_action( 'foundationpress_before_content' ); ?>
<?php while ( have_posts() ) : the_post(); ?>
<section class="intro" role="main">
	<div class="fp-intro">
		<div <?php post_class(); ?> id="post-<?php the_ID(); ?>">
			<?php do_action( 'foundationpress_page_before_entry_content' ); ?>
			<div class="entry-content">
				<?php the_content(); ?>
			</div>
		</div>
	</div>
</section>
<?php endwhile; ?>
<?php do_action( 'foundationpress_after_content' ); ?>


<?php get_footer();