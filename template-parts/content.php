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

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php if ( ! is_single() && has_post_thumbnail() ) : ?>
		<div class="post-thumbnail">
			<a href="<?php echo esc_url( get_permalink() ); ?>">
				<?php the_post_thumbnail( 'medium' ); ?>
			</a>
		</div>
	<?php endif; ?>
	<header>
	<?php
		if ( is_single() ) {
			// echo '<h1 class="entry-title">' . esc_html( get_the_title() ) . '</h1>';
		} else {
			echo '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . esc_html( get_the_title() ) . '</a></h2>';
		}
	?>
		
	</header>
	<div class="entry-content">
		<?php if ( is_single() ) { 
			the_content();
		} else { 
			the_excerpt(); ?>
			<p><a class="read-more" href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html__( 'Continue Reading', 'foundationpress' ); ?></a></p>
		<?php } ?>
		
	</div>
</article>
