<?php

namespace hitfigure\models;

/*
 * These classes represent the different privilage levels.
 * Functions in the Admin class are overridden depending on 
 * whether the user can act on them.
 *
 * The HitFigure class chooses which of these classes are the
 * current admin. 
 *
 */


// The Basic (mortal) non-administrator admin
class BasicAdmin extends Admin {

	protected function edit_client_vars( $client ) {
		// Check if we're editing the current users profile...
		if ((int)$client->id != $this->user->id) { 
			// Check if the current user is the parent of this client...	
			if ( (int)$client->user_parent != $this->user->id) {  
				$this->nopriv(); 
			}
		}
		return parent::edit_client_vars( $client );
	}
	
	protected function get_lead_data_vars() {
		// Add our limiting filter
		add_filter( 'posts_where', 'hitfigure\models\filter_where_registered_for_lead' );
		$vars = parent::get_lead_data_vars();
		remove_filter( 'posts_where', 'hitfigure\models\filter_where_registered_for_lead' );
		return $vars;
	}
	
	protected function get_client_data_vars($type, $args=array()) {
		$args = array('meta_key'=>'user_parent', 'meta_value'=>$this->user->id);
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
		// Dealers can create Sales People and Accountants
		if ( ($type == 'salesperson') || ($type == 'accountant') ) {
			return parent::register_client_vars($type);
		} else {
			$this->nopriv();
		}
	}
	
	public function get_admin_vars($vars = array()) {
		$vars = parent::get_admin_vars($vars);
		$vars['hide_manufacturers'] 	= True;
		$vars['hide_add_manufacturer'] 	= True;
		$vars['hide_dealers'] 			= True;
		$vars['hide_add_dealer'] 		= True;
		
		$vars['hide_salesperson'] 		= False;
		$vars['hide_add_salesperson'] 	= False;
		$vars['hide_accountant'] 		= False;
		$vars['hide_add_accountant'] 	= False;		
		
		return $vars;
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
	
	public function get_admin_vars($vars = array()) {
		$vars = parent::get_admin_vars($vars);
		$vars['hide_manufacturers'] 	= True;
		$vars['hide_add_manufacturer'] 	= True;
		
		return $vars;
	}	
	
}



class DealerEmployee extends DealerAdmin {
	protected function register_client_vars($type) {
		$this->nopriv;
	}

	protected function add_business_name_input($f) {
		return;
	}
	
	protected function add_additional_info_inputs($f) {
		return;
	}

	protected function get_new_client_form_args($f, $id) {
		parent::get_new_client_form_args_dealer_employee($f, $id);
	}
	
	protected function set_edit_client_form_values($f, $client) {
		parent::set_edit_client_form_values_dealer_employee($f, $client);
	}

	protected function get_lead_data_vars() {
		// We have to do something really sneaky here, don't tell anyone!
		// Since Employees should see what the dealer sees we're going to swap out
		// the current users info for the dealers info... but just for a second!!! No one will notice!!!
		
		$current_user 	= $this->user;
		$dealers_id 	= $this->user->user_parent;
		$dealer 		= new Client(array('id'=>$dealer_id));
		$dealer->fetch();
		$this->user = $dealer;
		
		add_filter( 'posts_where', 'hitfigure\models\filter_where_registered_for_lead' );
		$vars = parent::get_lead_data_vars();
		remove_filter( 'posts_where', 'hitfigure\models\filter_where_registered_for_lead' );
		
		// Ok, now that we're done with that we can go back...see, no harm done!
		$this->user = $current_user;
		
		return $vars;
	}

}



class SalesPersonAdmin extends DealerEmployee {
	

}



class AccountantAdmin extends DealerEmployee {

}