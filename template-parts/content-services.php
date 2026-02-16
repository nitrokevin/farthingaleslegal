<?php
/**
 * Template for displaying content
 *
 * Used for both single and archive pages.
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */
$service_features = get_field('service_features');

// Determine if we are on an archive/index/search page
$is_archive = ! is_single();
?>

<article id="<?php echo esc_attr( get_post_field( 'post_name', get_post() ) ); ?>" 
         <?php post_class( $is_archive ? 'card full-width' : 'standard-post' ); ?>>

    <header class="card-content">

        <?php if ( $is_archive && has_post_thumbnail() ) : ?>
            <div class="post-thumbnail">
              
                    <?php the_post_thumbnail( 'medium' ); ?>
               
            </div>
        <?php endif; ?>

        <?php if ( is_single() ) : ?>
			    
        
        <?php else : ?>
            <h2 class="entry-title">
             
                    <?php echo esc_html( get_the_title() ); ?>
               
            </h2>
        <?php endif; ?>

        <div class="align-self-top">
            <?php the_content(); ?>
        </div>

        <?php if ( $is_archive ) : ?>
            <?php
            $service_slug = sanitize_title( get_the_title() );

            $case_study = get_latest_tagged_post( $service_slug, [
                'category_name' => 'case-studies',
            ] );

            if ( $case_study ) : ?>
                <a href="<?php echo esc_url( $case_study['link'] ); ?>" class="button small">
                    <i class="fa-duotone fa-solid fa-folder-open fa-xl"></i>
                    Case Study: <?php echo esc_html( $case_study['title'] ); ?>
                </a>
            <?php endif; ?>

            <?php
            $article = get_latest_tagged_post( $service_slug, [
                'category_name' => 'insights',
            ] );

            if ( $article ) : ?>
                <a href="<?php echo esc_url( $article['link'] ); ?>" class="button small theme-color-2">
                    <i class="fa-duotone fa-solid fa-file-lines fa-xl"></i>
                    Related article: <?php echo esc_html( $article['title'] ); ?>
                </a>
            <?php endif; ?>
        <?php endif; ?>

    </header>

    <div class="service-features entry-content card-content">
        <?php echo $service_features; ?>

        <?php if ( $is_archive ) : ?>
            <a href="/contact" class="has-theme-color-2-color">
                Book a <?php echo esc_html( get_the_title() ); ?> consultation
                <i class="fa-light fa-arrow-right-long"></i>
            </a>
        <?php endif; ?>
    </div>

</article>