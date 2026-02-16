<?php
$term_id = get_field('resource_type_taxonomy');
$term    = get_term($term_id, 'resource_categories');

if ($term && !is_wp_error($term)) {
    $swiper_id = 'resources-' . esc_attr($term->slug);
}
?>
<div id="<?php echo $swiper_id; ?>" class="swiper resourcescarousel">
    <div class="swiper-button-prev" aria-label="Go to previous slide"></div>
    <div class="swiper-button-next" aria-label="Next slide"></div>
	  <!-- If we need pagination -->
  <div class="swiper-pagination"></div>

    <div class="swiper-wrapper">
        	   <?php
           $args = array(
                    'post_type'      => 'resources',
                    'posts_per_page' => -1,
                    'tax_query'      => array(
                        array(
                            'taxonomy' => 'resource_categories',
                            'field'    => 'term_id',
                            'terms'    => get_field('resource_type_taxonomy'), // Use ACF field value
                        ),
                    ),
                    'orderby'        => 'date',  // Order by date
                    'order'          => 'DESC',  // Descending order (most recent first)
                );

            $resources_query = new WP_Query($args);

            while ($resources_query->have_posts()) : $resources_query->the_post();
                $file = get_field('file', get_the_ID());
                $file_url  = $file['url'];

                // Get the attachment ID from the file URL
                $attachment_id = attachment_url_to_postid($file_url);

                // Get the PDF thumbnail image URL
                $thumbnail_url = wp_get_attachment_image_src($attachment_id, 'fp-small');

                if ($thumbnail_url) :
            ?>
                <div class="swiper-slide">
                        <a href="<?php echo esc_url($file_url); ?>" 
                class="download-link" 
                data-file-url="<?php echo esc_url($file_url); ?>">
                <img src="<?php echo esc_url($thumbnail_url[0]); ?>" 
                        alt="<?php the_title_attribute(); ?>" 
                        class="pdf-image" />
                </a>
                        <div class="info align-self-top">
                            <?php the_title(); ?>
                        </div>
                
                </div>
            <?php endif ?>
        <?php endwhile ?>
  <?php wp_reset_postdata(); ?>
    </div>
</div>

<div class="reveal" id="downloadModal" data-reveal>
  <button class="close-button" data-close aria-label="Close modal" type="button">
    <span aria-hidden="true">&times;</span>
  </button>

  <p><strong>Please enter your name and email address to access the file.</strong></p>

<form id="emailCaptureForm">
  <label for="name">Name</label>
  <input type="text" id="name" name="name" required>

  <label for="email">Email</label>
  <input type="email" id="email" name="email" required>

  <input type="hidden" id="file_url" name="file_url">

  <button type="submit" class="button">Download</button>


</form>

  <div id="formMessage" class="callout success" style="display:none;">
    Thank you — your download will start shortly.
  </div>
</div>