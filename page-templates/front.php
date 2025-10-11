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
                            'sizes' => '(max-width: 600px) 100vw, (max-width: 1024px) 50vw, (max-width: 1920px) 50vw, 50vw',
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
<?php
if (have_rows('flexible_content')) :
	while (have_rows('flexible_content')) : the_row();
	get_template_part('template-parts/acf/flexible-article');
	get_template_part('template-parts/acf/flexible-grid');
	get_template_part('template-parts/acf/full-width-50-50');
	get_template_part('template-parts/acf/accordion');
	get_template_part('template-parts/acf/tab');
	get_template_part('template-parts/acf/carousel');
	endwhile;
endif;
?>

<?php get_footer();