<?php

namespace hitfigure\models;

/*
 * These classes represent the different privilage levels
 * functions in the Admin class are overridden depending on 
 * whether the user can act on them.
 *
 * The HitFigure class chooses which of these classes are the
 * current admin. 
 *
 */


// The Basic (mortal) non-administrator admin
class BasicAdmin extends Admin {

	protected function edit_client_vars( $client ) {
		// Check if the current user is the parent of this client
		if ( (int)$client->user_parent != $this->user->id) { 
			$this->nopriv(); 
		}
	}
	
	protected function get_lead_data_vars() {
		// Add our limiting filter
		// This will have to be amended, actually, since manufacturers have a 
		// greater range....
		add_filter( 'posts_where', 'hitfigure\models\filter_where_lt_50miles' );
		$vars = parent::get_lead_data_vars();
		remove_filter( 'posts_where', 'hitfigure\models\filter_where_lt_50miles' );
		return $vars;
	}
	
	protected function get_client_data_vars($type, $args=array()) {
		$args = array('meta_key'=>'user_parent', 'meta_value'=>$hitfigure->admin->user->id);
		return parent::get_client_data_vars($type, $args);
	}
	
	protected function nopriv() {
		$hitfigure = HitFigure::getInstance();
		display_mustache_template('nopriv', $hitfigure->template_vars());
		exit;
	}
}



class DealerAdmin extends BasicAdmin {
	protected function register_client_vars( $type ) {
		// Dealers can't create anyone right now...
		$this->nopriv();
	}
}



class ManufacturerAdmin extends BasicAdmin {

	protected function register_client_vars( $type ) {
		if ($type == 'dealer') { // Manufacturers can only create dealers
			return parent::register_client_vars($type);
		} else {
			$this->nopriv();
		}
	}
}