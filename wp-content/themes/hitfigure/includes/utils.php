<?php

function get_header_vars() {
	global $page, $paged;

	$title  = wp_title( '|', false, 'right' );
	$title .= get_bloginfo( 'name' );

	// Add the blog description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		$title .= " | $site_description";

	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 )
		$title .= ' | ' . sprintf( 'Page %s', max( $paged, $page ) );

	$stylesheet = get_bloginfo( 'stylesheet_url' );
	$body_class = get_body_class();

	ob_start();
	wp_head();
	$wp_head = ob_get_contents();
	ob_end_clean();
	
	return array(
		'title'			=> $title,
		'stylesheet'	=> $stylesheet,
		'body_class'	=> $body_class,
		'wp_head'		=> $wp_head
	);
}

function get_footer_vars() {
	ob_start();
	wp_footer();
	$wp_footer = ob_get_contents();
	ob_end_clean();
	
	return array('wp_footer'=>$wp_footer);	
}

function get_page_queue($page) {

	$page_queue = array();
	
	if ( $page->post_parent ) {
		/* Use our Post Tree builder to get all the parents for this post */
		$post_tree = new \MagPostTree();
		$post_tree->args = array('post_type'=>'page', 'page_id'=>$page->ID);
		$post_tree->build_tree(\MagPostTree::$PARENTS);
		/* Flatten it, reverse it, and assign it to our $page_queue var */
		$page_queue = array_reverse($post_tree->flatten_tree());		
	} else {
		/* Only one page, so only one item */
		$page_queue = array($page);
	}
	
	/* Sub-Menu */
	$url = 'http://'.$_SERVER['HTTP_HOST'];
	
	foreach ($page_queue as $_page) {
			$ck_mag_subnav_slug = 'page-subnav-' . $_page->post_name;
			$sub_nav= wp_nav_menu( array(
						'theme_location'	=> $ck_mag_subnav_slug, 
						'menu'				=> $ck_mag_subnav_slug,
						'fallback_cb' 		=> function() {},
						'menu_class'		=> 'page-header-menu',
						'echo'				=> false
						));
			$_page->sub_nav = str_replace($url, '', $sub_nav);
	}
	// there should be an apply_filter here so extra stuff can be added to each page (like meta data)
	/* Better yet... take a look at MTV's PostCollection model first */
	return $page_queue;
}

function wp_data() {
	return array(
		'template_directory'=>get_bloginfo('template_directory'),
		'imgdir'=>get_bloginfo('template_directory') . '/images'
	);
}
