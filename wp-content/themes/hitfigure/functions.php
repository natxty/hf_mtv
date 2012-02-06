<?php

$InstallFolder = '/';

// Configure MTV
if ( function_exists('mtv\register_app') ) {
    mtv\register_app('mtv-hitfigure', __DIR__);
}

// User Roles
add_role( 'hitfigure', 'HitFigure', array( 'manage_manufacturers', 'manage_dealers', 'bid' ) );
add_role( 'manufacturer', 'Manufacturer', array( 'manage_dealers', 'bid' ) );
add_role( 'dealer', 'Dealer', array( 'bid' ) );
add_role( 'salesperson', 'Sales Person', array( 'bid' ) );
add_role( 'accountant', 'Accountant', array( ) );

// Custom Status 
// -- this seems impossible, get_post_statuses() seems to only choose a limited number, but I don't know where it is called...
// This is because wp-admin/includes/meta-boxes.php around line 87 you see that the post statuses are hard-coded...lame...
/*
add_action('wp_loaded', function() {
	global $wp_post_statuses;
	register_post_status('Expired', array(
		'label' => 'Expired', 
		'internal'=>false, 
		'public'=>true,
		'_builtin'=> true,
		'label_count'=>_n_noop( 'Expired <span class="count">(%s)</span>', 'Expired <span class="count">(%s)</span>' ),
		'publicly_queryable' => 1,
        'show_in_admin_status_list' => 1,
        'show_in_admin_all_list' => 1
		)
	);
	print_r($wp_post_statuses);
});
*/

// Custom Image Sizes
add_image_size( 'vehicle_img_full', 324, 250, true );
add_image_size( 'vehicle_img_thumb', 50, 50, true );


/* LOTS OF REQUIRES */
// App
//require_once( dirname(__FILE__) . '/app/Leads.class.php' );
require_once( dirname(__FILE__) . '/includes/post_types.php' );
require_once( dirname(__FILE__) . '/includes/meta_boxes.php' );
require_once( dirname(__FILE__) . '/includes/user_additional_fields.php' );
require_once( dirname(__FILE__) . '/includes/utils.php' );
require_once( dirname(__FILE__) . '/includes/class_MagPostTree.php' );
require_once( dirname(__FILE__) . '/includes/tm/includes.php' );

/* START MUSTACHE FUNCTIONS
 * This should be a wrapper class or something...
 */
require_once( dirname(__FILE__) . '/includes/Mustache.php' );

global $mustache;
global $mustache_partials;
global $mustache_templates;

function init_mustache() {
	global $mustache;
	global $mustache_partials;
	global $mustache_templates;

	$mustache_partials 		= get_mustache_files('partials');
	$mustache_templates	 	= get_mustache_files('templates');
	
	//print_r($mustache_templates);
	
	$mustache 				= new Mustache(null, null, $mustache_partials);
}

function get_mustache_files($type) {
	if ($type == 'partials') {
		$dir = __DIR__.'/templates/partials/';
	} else {
		$dir = __DIR__.'/templates/';
	}
	$templates_files = scandir($dir);
	$files = array();
	foreach ($templates_files as $file) {
		$tag_name = str_replace('.mustache', '', $file);
		if ( $tag_name !== $file ) {
			$template = $dir.$file;
			if (file_exists($template))
				$files [ $tag_name ] = file_get_contents($template);
		}
	}
	return $files;
}

function display_mustache_template($name,$vars=array(),$echo=True) {
	global $mustache_partials, $mustache_templates;
		
	$mustache = new Mustache(null, null, $mustache_partials);
	if (array_key_exists($name, $mustache_templates))
		$s = $mustache->render( $mustache_templates[$name], $vars);
	if ($echo)
		echo $s;
		
	return $s;
}

init_mustache();
/* END MUSTACHE FUNCTIONS */

/* Simplify our menu creation */
$register_page_menus = function() {
	$pages = get_posts(array('numberposts'=>-1, 'post_type'=>'page'));
	
	$menus = array();
	foreach ($pages as $page) {
		$subnav_slug = 'page-subnav-' . $page->post_name;
		$menus[$subnav_slug]=$page->post_title;
	}
	
	register_nav_menus(	$menus );	
};

$register_page_menus();

// Setup enabled apps
global $apps;
$apps = array('mtv-hitfigure');
