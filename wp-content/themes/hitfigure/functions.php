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

function init_mustache() {
	global $mustache;
	global $mustache_partials;

	$mustache_partials = get_mustache_partials();	
	$mustache = new Mustache(null, null, $mustache_partials);
}

function get_mustache_partials() {
	$dir = __DIR__.'/templates/partials/';
	$templates_files = scandir($dir);
	$partials = array();
	foreach ($templates_files as $file) {
		$tag_name = str_replace('.mustache', '', $file);
		if ( $tag_name !== $file ) {
			$template = $dir.$file;
			if (file_exists($template))
				$partials [ $tag_name ] = file_get_contents($template);
		}
	}
	return $partials;
}

function display_mustache_template($name,$vars=array(),$echo=True) {
	$mustache = new Mustache(null, null, get_mustache_partials());
	$template = __DIR__.'/templates/'.$name.'.mustache';
	if (file_exists($template))
		$s = $mustache->render( file_get_contents($template), $vars);
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
