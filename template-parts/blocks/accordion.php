<?php

/**
 * Accordion Block Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */
$section_background_color = get_field('accordion_background_color');
// Create id attribute allowing for custom "anchor" value.
$id = 'accordion-' . $block['id'];
if( !empty($block['anchor']) ) {
    $id = $block['anchor'];
}

// Create class attribute allowing for custom "className" and "align" values.
$className = 'block-accordion';
if( !empty($block['className']) ) {
    $className .= ' ' . $block['className'];
}
if( !empty($block['align']) ) {
    $className .= ' align' . $block['align'];
}

  // Prepare image sizes if needed
   $image = get_sub_field('accordion_background_image');
                if ($image) {
                    $small = $image['sizes']['featured-small'];
                    $medium = $image['sizes']['featured-medium'];
                    $large = $image['sizes']['featured-large']; 
                    $xlarge = $image['sizes']['featured-xlarge'];
                }


?>
<section id="<?php echo esc_attr($id); ?>" class="wp-block <?php echo esc_attr($className); ?>  <?php echo $section_background_color; ?> "  data-interchange="[<?php echo esc_url($small); ?>, small], [<?php echo esc_url($medium); ?>, medium], [<?php echo esc_url($large); ?>, large], [<?php echo esc_url($xlarge); ?>, xlarge]" data-type="background">
    <div class="block-accordion-container "> 
		<div class="block-accordion-grid" >
		<div class="block-accordion-content " >
			<?php get_template_part( 'template-parts/content', 'accordion' ); ?>
	
		</div>
	</div>
</section>
