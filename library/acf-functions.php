<?php if (function_exists('acf_add_local_field_group')) {

//Page Options
acf_add_local_field_group(array(
	'key' => 'group_5c756aae12c9d',
	'title' => 'Page Options',
	'fields' => array(
		 array(
            'key' => 'field_intro_text',
            'label' => 'Intro Text',
            'name' => 'intro_text',
            'type' => 'text',
            'instructions' => 'Text shown below the title.',
            'required' => 0,
        ),
	
		 array(
            'key' => 'field_intro_button_text',
            'label' => 'Button Text',
            'name' => 'intro_button_text',
            'type' => 'text',
            'instructions' => 'Text shown on the intro button.',
            'required' => 0,
        ),
        array(
            'key' => 'field_intro_button_url',
            'label' => 'Button URL',
            'name' => 'intro_button_url',
            'type' => 'page_link',
            'instructions' => 'Where the button should link to.',
            'required' => 0,
        ),
		
	),
	'location' => array(	
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'page',
			),
		),
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'post',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'side',
	'style' => 'seamless',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => true,
	'description' => '',
	'show_in_rest' => false,
));


//service

acf_add_local_field_group(array(
	'key' => 'group_5c75uguf6aae1dhweh',
	'title' => 'Service Features',
	'fields' => array(
			array(
				'key' => 'field_service_features',
				'label' => 'Service features',
				'name' => 'service_features',
				'type' => 'wysiwyg',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
			
				
			),
		
	),
	'location' => array(	
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'services',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'normal',
	'style' => 'seamless',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => true,
	'description' => '',
	'show_in_rest' => false,
));



//FAQ OPTIONS PAGE
acf_add_local_field_group(array(
	'key' => 'group_5d54589f208266',
	'title' => 'Options Page',
	'fields' => array(
		
		array(
			'key' => 'field_5c34ede232af66',
			'label' => 'FAQ',
			'name' => 'faq_repeater',
			'type' => 'repeater',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'collapsed' => '',
			'min' => 0,
			'max' => 0,
			'layout' => 'block',
			'button_label' => 'Add Accordion',
			'sub_fields' => array(
				array(
					'key' => 'field_5c34ee0032af76',
					'label' => 'Header',
					'name' => 'header',
					'type' => 'text',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '100',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
				),

				array(
					'key' => 'field_614b0df41e61b6',
					'label' => 'Categories',
					'name' => 'categories',
					'type' => 'taxonomy',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '50',
						'class' => '',
						'id' => '',
					),
					'taxonomy' => 'category',
					'field_type' => 'checkbox',
					'add_term' => 0,
					'save_terms' => 0,
					'load_terms' => 0,
					'return_format' => 'object',
					'multiple' => 0,
					'allow_null' => 0,
				),
				array(
					'key' => 'field_5c34ee0932af86',
					'label' => 'Content',
					'name' => 'content',
					'type' => 'wysiwyg',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'tabs' => 'all',
					'toolbar' => 'full',
					'media_upload' => 1,
					'delay' => 0,
				),
			),
		),
	

),

	'location' => array(
		array(
			array(
				'param' => 'options_page',
				'operator' => '==',
				'value' => 'faqs',
			),
		),
	),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => true,
		'description' => '',
));


acf_add_local_field_group(array(
		'key' => 'group_5d54589f208269',
		'title' => 'People',
		'fields' => array(
		
	
			array(
				'key' => 'field_5c34ede232af66f1',
				'label' => 'People',
				'name' => 'people_repeater',
				'type' => 'repeater',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'collapsed' => '',
				'min' => 0,
				'max' => 0,
				'layout' => 'block',
				'button_label' => 'Add Person',
				'sub_fields' => array(
				
					array(
						'key' => 'field_5c812c9h28a19c2',
						'label' => 'Image',
						'name' => 'image',
						'type' => 'image',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '50',
							'class' => '',
							'id' => '',
						),
						'return_format' => 'array',
						'preview_size' => 'thumbnail',
						'library' => 'all',
	
					),
					
					array(
						'key' => 'field_5c34ee003g2af746',
						'label' => 'Name',
						'name' => 'name',
						'type' => 'text',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '50',
							'class' => '',
							'id' => '',
						),
					
					),
					array(
						'key' => 'field_5c34ee0032arf746',
						'label' => 'Job Title',
						'name' => 'job',
						'type' => 'text',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '50',
							'class' => '',
							'id' => '',
						),
					
					),
				
					array(
						'key' => 'field_524ee20032arf746',
						'label' => 'Email',
						'name' => 'email',
						'type' => 'text',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '50',
							'class' => '',
							'id' => '',
						),
					
					),
				
				
					array(
						'key' => 'field_5c34ere0932af86',
						'label' => 'Biography',
						'name' => 'biography',
						'type' => 'wysiwyg',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'tabs' => 'all',
						'toolbar' => 'full',
						'media_upload' => 0,
						'delay' => 0,
					),
				),
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'options_page',
					'operator' => '==',
					'value' => 'people',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => true,
		'description' => '',
	));


	//Resources

acf_add_local_field_group(array(
	'key' => 'group_5c75uguf6aae12c9d',
	'title' => 'Resources',
	'fields' => array(
			array(
				'key' => 'field_resources_file',
				'label' => 'File',
				'name' => 'file',
				'type' => 'file',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'return_format' => 'array',
				'preview_size' => 'thumbnail',
				'library' => 'all',
				
			),
		
	),
	'location' => array(	
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'resources',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'normal',
	'style' => 'seamless',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => true,
	'description' => '',
	'show_in_rest' => false,
));

	

} //END ACF 






if (function_exists('acf_add_options_page')) {
	acf_add_options_page(array(
		'page_title' 	=> 'FAQs',
		'menu_title'	=> 'FAQs',
		'menu_slug' 	=> 'faqs',
		'capability'	=> 'edit_posts',
		'redirect'		=> false
	));
	acf_add_options_page(array(
		'page_title' 	=> 'People',
		'menu_title'	=> 'People',
		'menu_slug' 	=> 'people',
		'capability'	=> 'edit_posts',
		'icon_url' => 'dashicons-buddicons-buddypress-logo',
		'redirect'		=> false
	));
}


add_action('admin_head', 'gutenberg_sidebar');

function gutenberg_sidebar() {
  echo 
  '<style>
    .edit-post-sidebar {width: 400px;}
  </style>';
}
add_action('acf/input/admin_head', 'my_acf_admin_head5');
function my_acf_admin_head5()
{

	?>
<style type="text/css">
	.acf-editor-wrap iframe {
		min-height: 100px;
		height: 150px !important;
	}

	ul.acf-swatch-list.acf-hl>li {
		margin-right: .1rem;

	}

	ul.acf-swatch-list label {
		font-size: 0;
	}

	ul.acf-swatch-list .swatch-toggle {
		border-radius: 50%;
		border: 1px solid #aaaaaa;
	}
</style>
<?php

}
