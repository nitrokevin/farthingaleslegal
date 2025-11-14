<?php
/**
 * The default template for displaying content
 *
 * Used for both single and index/archive/search.
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

?>

<article id="<?php echo esc_attr( get_post_field( 'post_name', get_post() ) ); ?>" <?php post_class(); ?>>
	
	<header>
		
	<?php
		if ( is_single() ) {
			echo '<h1 class="entry-title">' . esc_html( get_the_title() ) . '</h1>';
		} else {
				echo '<h2 class="entry-title">' . esc_html( get_the_title() ) . '</h2>';
		}
	?>
	<?php if ( ! is_single() && has_post_thumbnail() ) : ?>
		<div class="post-thumbnail">
			<a href="<?php echo esc_url( get_permalink() ); ?>">
				<?php the_post_thumbnail( 'medium' ); ?>
			</a>
		</div>
	<?php endif; ?>
	<!-- <div class="align-self-bottom">
	<a href="/contact-us" class=" button small">Book a consultation</a>
	</div> -->
	</header>
	<div class="entry-content">
		<?php if ( is_single() ) { 
			the_content();
		} else { 
			the_content(); ?>
		<?php } ?>
		
	</div>
</article>
