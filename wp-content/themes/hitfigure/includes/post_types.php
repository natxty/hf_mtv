<?php


function addPostTypes($settings) {
	foreach ($settings as $info) {
	
		add_action('init', function() use ($info) {
		
		  extract( shortcode_atts( array(
		  	'post_type_name'		=> '',
		  	'name'					=> '',
		  	'menu_name'				=> '',
			'singular_name' 		=> '',
			'plural_name' 			=> '',

		    'public' 				=> true,
		    'publicly_queryable' 	=> true,
		    'show_ui' 				=> true, 
		    'show_in_menu' 			=> true, 
		    'query_var' 			=> true,
		    'rewrite' 				=> true,
		    'capability_type' 		=> 'post',
		    'has_archive' 			=> true, 
		    'hierarchical' 			=> true,
		    'menu_position' 		=> null,
		    'supports' 				=> array('title','editor','author','thumbnail','excerpt','comments','page-attributes')
		  ), $info ) );
		
		
		  $labels = array(
		    'name' 				=> $name,
		    'singular_name' 	=> $singular_name,
		    'add_new'	 		=> 'Add New',
		    'add_new_item' 		=> 'Add New ' . $singular_name,
		    'edit_item' 		=> 'Edit ' . $singular_name,
		    'new_item' 			=> 'New ' . $singular_name,
		    'all_items' 		=> 'All ' . $plural_name,
		    'view_item' 		=> 'View ' . $singular_name,
		    'search_items' 		=> 'Search ' . $plural_name,
		    'not_found' 		=> 'No ' . $plural_name . ' found',
		    'not_found_in_trash'=> 'No ' . $plural_name . ' found in Trash', 
		    'parent_item_colon' => '',
		    'menu_name' 		=> $menu_name
		
		  );
		  
		  $args = array(
		    'labels' 			=> $labels,
		    'public' 			=> $public,
		    'publicly_queryable'=> $publicaly_queryably,
		    'show_ui' 			=> $show_ui, 
		    'show_in_menu' 		=> $show_in_menu, 
		    'query_var' 		=> $query_var,
		    'rewrite' 			=> $rewrite,
		    'capability_type' 	=> $capability_type,
		    'has_archive' 		=> $has_archive, 
		    'hierarchical' 		=> $hierarchical,
		    'menu_position' 	=> $menu_position,
		    'supports' 			=> $supports
		  );
		  register_post_type($post_type_name,$args);
		});
		
	}
}


addPostTypes
(
	array(
		array
		(
			'post_type_name' 		=> 'cpt-vehicle',
			'name'					=> 'Vehicle',
			'menu_name'				=> 'Vehicle',
			'singular_name'			=> 'Vehicle',
			'plural_name'			=> 'Vehicles',
			'public'				=> false,
			'publicly_queryable'	=> false,
			'hierarchical'			=> false,
			'has_archive'			=> false,
			'supports'				=> array('title')
		),

		array
		(
			'post_type_name' 		=> 'cpt-alert',
			'name'					=> 'Alert',
			'menu_name'				=> 'Alert',
			'singular_name'			=> 'Alert',
			'plural_name'			=> 'Alerts',
			'public'				=> false,
			'publicly_queryable'	=> false,
			'hierarchical'			=> false,
			'has_archive'			=> false
		),

		array
		(
			'post_type_name' 		=> 'cpt-bid',
			'name'					=> 'Bid',
			'menu_name'				=> 'Bid',
			'singular_name'			=> 'Bid',
			'plural_name'			=> 'Bids',
			'public'				=> false,
			'publicly_queryable'	=> false,
			'hierarchical'			=> false,
			'has_archive'			=> false
		),
		
		array
		(
		 	'post_type_name' 		=> 'cpt-contact',
			'name'					=> 'Contact',
			'menu_name'				=> 'Contact',
			'singular_name'			=> 'Contact',
			'plural_name'			=> 'Contacts',
			'public'				=> false,
			'publicly_queryable'	=> false,
			'hierarchical'			=> false,
			'has_archive'			=> false
		 )

	)
);

