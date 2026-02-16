<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the "off-canvas-wrap" div and all content after.
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */
$contact_phone = get_theme_mod('contact_phone_number');
$contact_email = get_theme_mod('contact_email');
$footer_company_number = get_theme_mod('footer_company_number');
$footer_copyright = get_theme_mod('footer_copyright');
$contact_address_1 = get_theme_mod('contact_address_1');
$contact_address_2 = get_theme_mod('contact_address_2');
$contact_address_3 = get_theme_mod('contact_address_3');
$contact_address_4 = get_theme_mod('contact_address_4');
$contact_address_5 = get_theme_mod('contact_address_5');
$contact_address_6 = get_theme_mod('contact_address_6');
$footer_background_image = get_theme_mod('footer_background_image');
$site_name = get_bloginfo('name', 'display');

$socials = [
    'facebook' => get_theme_mod('social-facebook-url'),
    'x' => get_theme_mod('social-x-url'),
    'instagram' => get_theme_mod('social-instagram-url'),
    'linkedin' => get_theme_mod('social-linkedin-url'),
    'pinterest' => get_theme_mod('social-pinterest-url'),
    'youtube' => get_theme_mod('social-youtube-url'),
    'tiktok' => get_theme_mod('social-tiktok-url'),
];

$social_icons = [
    'facebook' => 'fa-brands fa-facebook-f fa-fw',
    'x' => 'fa-brands fa-x-twitter fa-fw',
    'instagram' => 'fa-brands fa-instagram fa-fw',
    'linkedin' => 'fa-brands fa-linkedin-in fa-fw',
    'pinterest' => 'fa-brands fa-pinterest fa-fw',
    'youtube' => 'fa-brands fa-youtube fa-fw',
    'tiktok' => 'fa-brands fa-tiktok fa-fw',
];



?>

<footer class="footer" >
    <div class="footer-container">
        <div class="footer-grid">

            <section>
                <p class="subheader">Get in touch</p>
                  <ul class="footer-contact menu  footer-menu">
                    <li><?php echo esc_html($contact_phone); ?></li>
                    <li><?php echo esc_html($contact_email); ?></li>
                </ul>
                <?php foundationpress_footer_nav_l(); ?>
                <ul class="social-links menu  footer-menu align-left">
                    <?php foreach ($social_icons as $key => $icon_class) : ?>
                        <?php if (!empty($socials[$key])) : ?>
                            <li><a href="<?php echo esc_url($socials[$key]); ?>" rel="noreferrer" target="_blank" aria-label="<?php echo ucfirst($key); ?>">
                                    <i class="<?php echo esc_attr($icon_class); ?>"></i>
                                </a></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>

            </section>
            <section>
                <p class="subheader">Correspondence Address</p>
                  <ul class="footer-contact menu  footer-menu">
                    <li><?php echo esc_html($contact_address_1); ?></li>
                    <li><?php echo esc_html($contact_address_2); ?></li>
                    <li><?php echo esc_html($contact_address_3); ?></li>
                    <li><?php echo esc_html($contact_address_4); ?></li>
                    <li><?php echo esc_html($contact_address_5); ?></li>
                    <li><?php echo esc_html($contact_address_6); ?></li>
                </ul>
                
              
            </section>
            <section>
                <p class="subheader">Legal</p>
                <?php foundationpress_footer_nav_r(); ?>
                
                 <?php
                    $footer_links = avidd_get_repeater_data('footer_links');

                    if (!empty($footer_links)) { ?>
                        <div class="footer-links">
                            <?php foreach ($footer_links as $footer_link) : ?>
                                <?php if (!empty($footer_link['footer_image']) ) : ?>
                                    <a href="<?php echo esc_url($footer_link['link_url']); ?>">
                                        <?php echo wp_get_attachment_image($footer_link['footer_image'], 'thumbnail', false, ["class" => "footer-icon"]); ?>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php } ?>
                    <ul>
                     <li> Company number: <?php echo $footer_company_number ?></li>
                 <li>&copy; <?php echo esc_html($site_name) . ' ' . date('Y'); ?></li>
                    </ul>
            </section>
        </div>
    </div>
</footer>

<?php if (get_theme_mod('wpt_mobile_menu_layout') === 'offcanvas') : ?>
    </div><!-- Close off-canvas content -->
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>