<?php
/**
 * Global Content Selector Block Template
 *
 * Dynamic repeater renderer for selected options page with layout selection.
 */

// Ensure ACF is available
if (!function_exists('get_field')) {
    echo '<p><em>' . esc_html__('Advanced Custom Fields is required for this block.', 'ashwoodhomes') . '</em></p>';
    return;
}

/**
 * Render repeater rows recursively
 *
 * @param string $field_name The ACF field name.
 * @param string $context The ACF context (e.g., options page ID).
 * @param int $level The nesting level for indentation.
 */
if (!function_exists('render_repeater_rows')) {
    function render_repeater_rows($field_name, $context, $level = 0) {
        if (!have_rows($field_name, $context)) {
            return;
        }

        $indent_class = 'nested-level-' . $level;
        echo '<div class="repeater-block ' . esc_attr($indent_class) . '">';
        echo '<h4 class="repeater-heading">' . esc_html(ucwords(str_replace('_', ' ', $field_name))) . '</h4>';

        while (have_rows($field_name, $context)) {
            the_row();
            echo '<div class="repeater-item grid-x grid-margin-x">';
            $subfields = get_row(true);

            foreach ($subfields as $sub_key => $sub_value) {
                echo '<div class="cell small-12">';
                if (is_array($sub_value) && have_rows($sub_key, $context)) {
                    render_repeater_rows($sub_key, $context, $level + 1);
                } elseif ($sub_key === 'image' && is_array($sub_value) && isset($sub_value['url'])) {
                    echo '<p><img src="' . esc_url($sub_value['url']) . '" alt="' . esc_attr($sub_value['alt'] ?? '') . '" style="max-width: 200px;" /></p>';
                } elseif ($sub_key === 'categories' && is_array($sub_value)) {
                    $terms = array_map(function($term) {
                        return esc_html($term->name);
                    }, $sub_value);
                    echo '<p>' . implode(', ', $terms) . '</p>';
                } elseif (is_array($sub_value)) {
                    echo '<p>' . esc_html(json_encode($sub_value)) . '</p>';
                } else {
                    echo '<p>' . wp_kses_post($sub_value) . '</p>';
                }
                echo '</div>';
            }

            echo '</div>'; // .repeater-item
        }

        echo '</div>'; // .repeater-block
    }
}

// Unique block ID
// $id = !empty($block['anchor']) ? $block['anchor'] : 'global-content-' . ($block['id'] ?? wp_rand());

// Block class & alignment
$className = 'block-global-content-selector';
if (!empty($block['className'])) {
    $className .= ' ' . esc_attr($block['className']);
}
if (!empty($block['align'])) {
    $className .= ' align' . esc_attr($block['align']);
}

// Color & gradient support
$style = '';
$classes = [];
if (!empty($block['backgroundColor'])) {
    $classes[] = 'has-background';
    $classes[] = 'has-' . esc_attr($block['backgroundColor']) . '-background-color';
    $style .= 'background-color: var(--wp--preset--color--' . esc_attr($block['backgroundColor']) . ');';
}
if (!empty($block['gradient'])) {
    $classes[] = 'has-background';
    $classes[] = 'has-' . esc_attr($block['gradient']) . '-gradient-background';
    $style .= 'background: var(--wp--preset--gradient--' . esc_attr($block['gradient']) . ');';
}
if (!empty($block['textColor'])) {
    $classes[] = 'has-text-color';
    $classes[] = 'has-' . esc_attr($block['textColor']) . '-color';
    $style .= 'color: var(--wp--preset--color--' . esc_attr($block['textColor']) . ');';
}
$className .= ' ' . implode(' ', array_map('esc_attr', $classes));

// Selected options page slug and layout
$options_page = get_field('options_page_selector') ?: '';
$layout = get_field('layout_style') ?: 'list'; // Default to 'list' if not set
$options_context = 'options'; // Use 'options' context since post_id => options
$repeater_field = $options_page === 'faqs' ? 'faq_repeater' : ($options_page === 'people' ? 'people_repeater' : '');

?>

<section id="<?php echo esc_attr($block['anchor']); ?>" class="wp-block <?php echo esc_attr($className); ?>" style="<?php echo esc_attr($style); ?>">
    <div class="block-global-content-container grid-x grid-margin-x">
        <div class="block-global-content-content cell">
            <?php
            if (!$options_page) {
                echo '<p><em>' . esc_html__('No options page selected.', 'ashwoodhomes') . '</em></p>';
            } elseif (!$repeater_field) {
                echo '<p><em>' . esc_html__('Invalid options page selected.', 'ashwoodhomes') . '</em></p>';
            } elseif (!have_rows($repeater_field, $options_context)) {
                echo '<p><em>' . esc_html__('No data found for this options page.', 'ashwoodhomes') . '</em></p>';
            } else {
                // Render the selected repeater
                if ($layout === 'accordion') {
                    echo '<div class="accordion" data-accordion>';
                    while (have_rows($repeater_field, $options_context)) {
                        the_row();
                        $heading = get_sub_field('header') ?: get_sub_field('name') ?: ucwords(str_replace('_', ' ', $repeater_field));
                        echo '<div class="accordion-item" data-accordion-item>';
                        echo '<a href="#" class="accordion-title">' . esc_html($heading) . '</a>';
                        echo '<div class="accordion-content" data-tab-content>';

                        $subfields = get_row(true);
                        foreach ($subfields as $sub_key => $sub_value) {
                            // Skip header or name as it's used in the accordion title
                            if ($sub_key === 'header' || $sub_key === 'name') {
                                continue;
                            }
                            if ($sub_key === 'image' && is_array($sub_value) && isset($sub_value['url'])) {
                                echo '<p><img src="' . esc_url($sub_value['url']) . '" alt="' . esc_attr($sub_value['alt'] ?? '') . '" style="max-width: 200px;" /></p>';
                            } elseif ($sub_key === 'categories' && is_array($sub_value)) {
                                $terms = array_map(function($term) {
                                    return esc_html($term->name);
                                }, $sub_value);
                                echo '<p>' . implode(', ', $terms) . '</p>';
                            } elseif (is_array($sub_value) && have_rows($sub_key, $options_context)) {
                                render_repeater_rows($sub_key, $options_context, 1);
                            } elseif (is_array($sub_value)) {
                                echo '<p>' . esc_html(json_encode($sub_value)) . '</p>';
                            } else {
                                echo '<p>' . wp_kses_post($sub_value) . '</p>';
                            }
                        }

                        echo '</div></div>'; // content + accordion item
                    }
                    echo '</div>'; // accordion
                } elseif ($layout === 'list') {
                    echo '<ul class="global-list">';
                    while (have_rows($repeater_field, $options_context)) {
                        the_row();
                        $heading = get_sub_field('header') ?: get_sub_field('name') ?: ucwords(str_replace('_', ' ', $repeater_field));
                        echo '<li><strong>' . esc_html($heading) . '</strong>';
                        $subfields = get_row(true);
                        if ($subfields) {
                            echo '<ul>';
                            foreach ($subfields as $sub_key => $sub_value) {
                                // Skip header or name as it's used in the list heading
                                if ($sub_key === 'header' || $sub_key === 'name') {
                                    continue;
                                }
                                echo '<li>';
                                if ($sub_key === 'image' && is_array($sub_value) && isset($sub_value['url'])) {
                                    echo '<img src="' . esc_url($sub_value['url']) . '" alt="' . esc_attr($sub_value['alt'] ?? '') . '" style="max-width: 200px;" />';
                                } elseif ($sub_key === 'categories' && is_array($sub_value)) {
                                    $terms = array_map(function($term) {
                                        return esc_html($term->name);
                                    }, $sub_value);
                                    echo implode(', ', $terms);
                                } elseif (is_array($sub_value)) {
                                    echo esc_html(json_encode($sub_value));
                                } else {
                                    echo wp_kses_post($sub_value);
                                }
                                echo '</li>';
                            }
                            echo '</ul>';
                        }
                        echo '</li>';
                    }
                    echo '</ul>';
                     } elseif ($layout === 'columns') {
                  
                    while (have_rows($repeater_field, $options_context)) {
                          echo '<div class="global-content-columns grid-x grid-padding-x">';
                        the_row();
                        $heading = get_sub_field('header') ?: get_sub_field('name') ?: ucwords(str_replace('_', ' ', $repeater_field));
                        echo '<div class="global-content-column-first cell small-12 medium-4 medium-offset-1" >';
                       
                        echo '<p>' . esc_html('LOCAL COMMUNITY FEEDBACK') . '</p>';
                        echo '<p>' . esc_html($heading) . '</p>';
                        echo '<hr class="bottom-line">';
                        echo '</div>';
                        echo '<div class="global-content-column-last cell small-12 medium-7">';
                        echo '<p>' . esc_html('OUR RESPONSE') . '</p>';
                        
                        $subfields = get_row(true);
                        foreach ($subfields as $sub_key => $sub_value) {
                            // Skip header or name as it's used in the accordion title
                            if ($sub_key === 'header' || $sub_key === 'name') {
                                continue;
                            }
                            if ($sub_key === 'image' && is_array($sub_value) && isset($sub_value['url'])) {
                                echo '<p><img src="' . esc_url($sub_value['url']) . '" alt="' . esc_attr($sub_value['alt'] ?? '') . '" style="max-width: 200px;" /></p>';
                            } elseif ($sub_key === 'categories' && is_array($sub_value)) {
                                $terms = array_map(function($term) {
                                    return esc_html($term->name);
                                }, $sub_value);
                                echo '<p>' . implode(', ', $terms) . '</p>';
                            } elseif (is_array($sub_value) && have_rows($sub_key, $options_context)) {
                                render_repeater_rows($sub_key, $options_context, 1);
                            } elseif (is_array($sub_value)) {
                                echo '<p>' . esc_html(json_encode($sub_value)) . '</p>';
                            } else {
                                echo '<p>' . wp_kses_post($sub_value) . '</p>';
                            }
                        }

                        echo '</div>';
                         echo '</div>'; // 
                    }
                    //
               } elseif ($layout === 'vertical-tab') {

    echo '<div class="grid-x grid-margin-x vertical-tabs-wrap">';
        // Tabs list (left column)
        echo '<div class="cell small-12 medium-3">';
            echo '<ul class="block-tabs vertical tabs" data-tabs id="' . esc_attr($id) . '">';
            
            $counter = 0;
            while (have_rows($repeater_field, $options_context)) {
                the_row();
                $counter++;
                $heading = get_sub_field('header') ?: get_sub_field('name') ?: 'Tab ' . $counter;
                echo '<li class="tabs-title' . ($counter === 1 ? ' is-active' : '') . '">';
                echo '<a href="#tab-' . $counter . '">' . esc_html($heading) . '</a>';
                echo '</li>';
            }

            echo '</ul>';
        echo '</div>';

        // Reset repeater for content
        $counter = 0;
        reset_rows();

        // Tabs content (right column)
        echo '<div class="cell small-12 medium-9">';
            echo '<div class="tabs-content vertical ' . esc_attr($tab_bar_background_color ?? '') . '" data-tabs-content="' . esc_attr($id) . '">';
            
            while (have_rows($repeater_field, $options_context)) {
                the_row();
                $counter++;
                $subfields = get_row(true);

                echo '<div class="tabs-panel' . ($counter === 1 ? ' is-active' : '') . '" id="tab-' . $counter . '">';

                foreach ($subfields as $sub_key => $sub_value) {
                    if (in_array($sub_key, ['header', 'name'], true)) {
                        continue;
                    }

                    echo '<p>';
                    if ($sub_key === 'image' && is_array($sub_value) && isset($sub_value['url'])) {
                        echo '<img src="' . esc_url($sub_value['url']) . '" alt="' . esc_attr($sub_value['alt'] ?? '') . '" style="max-width:200px;" />';
                    } elseif ($sub_key === 'categories' && is_array($sub_value)) {
                        $terms = array_map(fn($term) => esc_html($term->name), $sub_value);
                        echo implode(', ', $terms);
                    } elseif (is_array($sub_value)) {
                        echo esc_html(json_encode($sub_value));
                    } else {
                        echo wp_kses_post($sub_value);
                    }
                    echo '</p>';
                }

                echo '</div>'; // .tabs-panel
            }

            echo '</div>'; // .tabs-content
        echo '</div>'; // .cell medium-9
    echo '</div>'; // .grid-x
}
            }
            ?>
        </div>
    </div>
</section>