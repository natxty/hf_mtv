<?php

namespace hitfigure\models;

/*
 * Our main app
 */
 

class HitFigure {
	public static $instance 	= null;



	private function __construct() {
		$this->init();
	}
	
	
	
	static function getInstance() {
		if ( !self::$instance ) {
			$className = __CLASS__;
			self::$instance = new $className();
		}
		return self::$instance;
	}



	private function init() {
		// Check if we're logged in...
		$this->instantiate_admin();
	}


	
	private function instantiate_admin() {
		$user = UserCollection::get_current();
		$roles = $user->roles;
		
		if (in_array('administrator', $roles) || in_array('hitfigure', $roles)) {
			$this->admin = new Admin();
		} elseif ( in_array('manufacturer', $roles) ) {
			$this->admin = new ManufacturerAdmin();
		} elseif ( in_array('dealer', $roles) ) {
			$this->admin = new DealerAdmin();
		}
		
	}
	
	
	
	public function template_vars($vars = array()) {
		return $this->get_wp_data() + $this->get_header_vars() + $this->get_footer_vars() + $vars;
	}


	
	public function get_header_vars() {
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



	public function get_footer_vars() {
		ob_start();
		wp_footer();
		$wp_footer = ob_get_contents();
		ob_end_clean();
		
		return array('wp_footer'=>$wp_footer);	
	}
	
	
	
	function get_wp_data() {
		return array(
			'template_directory'=>get_bloginfo('template_directory'),
			'imgdir'=>get_bloginfo('template_directory') . '/images'
		);
	}	

}