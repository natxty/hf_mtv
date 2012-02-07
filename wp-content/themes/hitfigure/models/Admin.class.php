<?php

namespace hitfigure\models;


/*
 * Our base Admin class
 * Each of our other Admin classes will apply contraints to this.
 * For consistency, any method that should be overridden is protected. 
 */
 
 
class Admin {
	public function __construct() {
		$this->user = UserCollection::get_current();
	}
	
	
	
	
	protected function nopriv() {
		$hitfigure = HitFigure::getInstance();
		display_mustache_template('nopriv', $hitfigure->template_vars());
		exit;
	}	



	public function register_client( $type ) {
		return $this->register_client_vars($type);
	}



	protected function register_client_vars( $type ) {
		return array('form'=>$this->update_client($type));
	}



	public function edit_client( $id ) {
		$type	= Null;
		
		$client = new Client(array('id'=>$id));
		$client->fetch();
		
		return $this->edit_client_vars($client);
	}



	protected function edit_client_vars( $client ) {
		$type = $client->roles[0];
		$form = $this->update_client( $type, $client );
		$adminvars = array('form'=>$form);
		
		if ( isset($_GET['clientregistered']) ) {
			$adminvars['clientregistered'] = True;
			$adminvars['clienttype'] = $_GET['clientregistered']; // -- This is the Type of client
		}		
		
		return 	$adminvars;
	}
	
	
	
	protected function update_client( $type, $client=Null ) {
		// Create or Update a Client
		
		$dealer_employees = array('salesperson', 'accountant');
		
		// If we're just updating a client we have it as an object already
		$id = Null; // -- !$id is used to check if we are registering a new user or not		
		if ($client) {
			$id = $client->id;
		}
	
		$f = new \FormHelper('register_client');
		$f->method = 'POST';
		if (!$id) {
			$f->action = '/admin/new/'.$type;
		} else {
			$f->action = '/admin/edit/'.$id;
		}
		
		if ( !in_array($type, $dealer_employees) ) {
			$this->add_business_name_input($f);
		}
		
		$this->add_basic_info_inputs($f);
		
		if ( !in_array($type, $dealer_employees) ) {
			$this->add_additional_info_inputs($f);		
		}
		
		$this->add_account_inputs($f);
		
		$b = new \Button('submit');
		$b->setProperties(array(
			'name'	=> 'submit',
			'text'	=> 'Submit'
		));
		$f->add($b);
		
		
		
		if ($id) { // Do some additional stuff if we're editing
			$f->password->required = False;
			$f->confirmpassword->required = False;
			$f->user_login->required = False;
			$f->user_login->readonly = True;
		}
		
		
		
		if ( isset($_REQUEST['submit']) ) {
			
			$f->applyUserInput(True);
			
			if (!$f->validate()) {
			
				$f->password->value = '';
				$f->confirmpassword->value = '';
			
			} else {
				// Validation Success, do our post-form processing
				$client = Null;
				
				if ( !in_array($type, $dealer_employees) ) {
					$args = $this->get_new_client_form_args($f, $id);
				} else {
					$args = $this->get_new_client_form_args_dealer_employee($f, $id);
				}
							
				switch($type) {
					case 'manufacturer':
						$client = new Manufacturer($args);
						break;
					case 'dealer':
						$client = new Dealer($args);
						break;
					case 'salesperson':
						$client = new SalesPerson($args);
						break;
					case 'accountant':
						$client = new Accountant($args);
						break;
				}
				
				if ( !$id && is_wp_error($result = $client->register()) ) { // Register and check for errors
					$f->password->value = '';
					$f->confirmpassword->value = '';														
				} else {
					$client->save();
					if (!$id) {
						$url = get_bloginfo('wpurl').'/admin/edit/'.$client->id.'/?clientregistered='.ucfirst($type);
						wp_redirect( $url, '302' );
					}
				}
			}
		} else {
			
			if ($id) { // Set our values to our client
			
				if ( !in_array($type, $dealer_employees) ) {
					$this->set_edit_client_form_values($f, $client);
				} else {
					$this->set_edit_client_form_values_dealer_employee($f, $client);
				}
			}	
		}	
	
		return $f->render();
	}



	protected function add_business_name_input($f) {
		$i = new \Input('business_name');
		$i->setProperties(array(
			'name' =>'business_name',
			'text' =>'Dealer Name',
			'required'=>True
		));
		$f->add($i);		
	}
	
	
	
	protected function add_basic_info_inputs($f) {
		$i = new \Input('first_name');
		$i->setProperties(array(
			'name' =>'first_name',
			'text' =>'First Name',
			'required'=>True
		));
		$f->add($i);	
	
		$i = new \Input('last_name');
		$i->setProperties(array(
			'name' =>'last_name',
			'text' =>'Last Name',
			'required'=>True
		));
		$f->add($i);	
		
		$i = new \Input('user_email');
		$i->setProperties(array(
			'name' =>'user_email',
			'text' =>'Email',
			'required'=>True,
			'validate_func'=>function($self) use ($id) {
				if ( !$id && email_exists($self->value) ) {
		        	return 'This email address is already registered.';
		        }
		        return True;
			}
		));
		$f->add($i);	
	}
	
	
	
	protected function add_additional_info_inputs($f) {
		$i = new \Input('phone');
		$i->setProperties(array(
			'name' =>'phone',
			'text' =>'Phone',
			'required'=>True
		));
		$f->add($i);			
	
		$i = new \Input('address');
		$i->setProperties(array(
			'name' =>'address',
			'text' =>'Street Address',
			'required'=>True
		));	
		$f->add($i);			
		
		$i = new \Input('address2');
		$i->setProperties(array(
			'name' =>'address2',
			'text' =>'Address Line 2',
		));
		$f->add($i);			
	
		$i = new \Input('city');
		$i->setProperties(array(
			'name' =>'city',
			'text' =>'City',
			'required'=>True
		));
		$f->add($i);			
		
		$s = state_select_form('state', array(
			'name' =>'state',
			'text' =>'State',
			'required'=>True
		));
		$f->add($s);
	
		$i = new \Input('zipcode');
		$i->setProperties(array(
			'name' =>'zipcode',
			'text' =>'Zip Code',
			'required'=>True
		));
		$f->add($i);			
	}
	
	
	
	protected function add_account_inputs($f) {
		$i = new \Input('user_login');
		$i->setProperties(array(
			'name' =>'user_login',
			'text' =>'Username',
			'required'=>True,
			'validate_func'=>function($self) use ($id) {
		        $user_id = username_exists( $self->value );
		        if (!$id && $user_id) {
		        	return 'This user login is already registered.';
		        }
		        return True;	
			}
		));
		$f->add($i);	
		
		$i = new \Input('password');
		$i->setProperties(array(
			'name' 		=>'password',
			'text' 		=>'Password',
			'secret'	=>True,
			'required'	=>True
		));	
		$f->add($i);		
	
		$i = new \Input('confirmpassword');
		$i->setProperties(array(
			'name' 		=>'confirmpassword',
			'text' 		=>'Confirm Password',
			'secret'	=>True,
			'required'	=>True,
			'validate_func'=>function($self) use($f) {
				if ($f->password->value && $f->password->value!=$self->value) {
					return "Passwords do not match.";
				}
				return True;
			}
		));	
		$f->add($i);	
	}
	
	
	
	protected function get_new_client_form_args($f, $id) {
		$args = array(
			'id'				=>$id,
			'business_name'		=>$f->business_name->value,
			'first_name'		=>$f->first_name->value,
			'last_name'			=>$f->last_name->value,
			'user_email'		=>$f->user_email->value,
			'phone'				=>$f->phone->value,
			'address'			=>$f->address->value,
			'address2'			=>$f->address2->value,
			'city'				=>$f->city->value,
			'state'				=>$f->state->value,
			'zipcode'			=>$f->zipcode->value,
			'user_login'		=>$f->user_login->value,
			'user_pass'			=>$f->password->value,
			'user_parent'		=>$this->user->id
		);	
		
		return $args;
	}
	
	
	
	protected function set_edit_client_form_values($f, $client) {
		$f->business_name->value 	= $client->business_name;
		$f->first_name->value 		= $client->first_name;
		$f->last_name->value		= $client->last_name;
		$f->user_email->value		= $client->data->user_email;
		$f->phone->value			= $client->phone;
		$f->address->value			= $client->address;
		$f->address2->value			= $client->address2;
		$f->city->value				= $client->city;
		$f->state->value			= $client->state;
		$f->zipcode->value			= $client->zipcode;
		$f->user_login->value		= $client->data->user_login;
	}



	protected function get_new_client_form_args_dealer_employee($f, $id) {
		$args = array(
			'id'				=>$id,
			'first_name'		=>$f->first_name->value,
			'last_name'			=>$f->last_name->value,
			'user_email'		=>$f->user_email->value,
			'user_login'		=>$f->user_login->value,
			'user_pass'			=>$f->password->value,
			'user_parent'		=>$this->user->id
		);	
		
		return $args;
	}
	
	
	
	protected function set_edit_client_form_values_dealer_employee($f, $client) {
		$f->first_name->value 		= $client->first_name;
		$f->last_name->value		= $client->last_name;
		$f->user_email->value		= $client->data->user_email;
		$f->user_login->value		= $client->data->user_login;
	}



	protected function bid_status_text($status) {
		if ($status === -1) {
			return 'No Bids';
		} elseif ($status === 0) {
			return 'Losing';
		} elseif ($status === 1) {
			return 'Winning!';
		}
	}
	
	
	
	public function view_lead( $id ) {
	
		$vehicle = new Vehicle(array('id'=>$id));	
		$vehicle->fetch();
		
		$min_amount 			= BidCollection::getMinAmount($vehicle->id);
		$highest_amount 		= BidCollection::getHighestBid($vehicle->id);
		$your_highest_amount 	= BidCollection::yourHighestBid($vehicle->id);
		$bid_status 			= $this->bid_status_text(BidCollection::bidStatus($vehicle->id));
		
		$bidvars = array(
			'timeleft'				=>VehicleCollection::time_left($id),
			'min_amount'			=>money_format('%i', $min_amount),
			'highest_amount'		=>money_format('%i', $highest_amount),
			'your_highest_amount'	=>money_format('%i', $your_highest_amount),
			'bid_status'			=>$bid_status
		);
		
		$attachments = array('attachments'=>$vehicle->get_attachments());
			
		return $vehicle->attributes + $vehicle->post_meta + $bidvars + $attachments;
	}
	
	
	
	public function view_alert( $id ) {
		
		$alerts = AlertCollection::filter(array(
			'p'				=>$id,
			'meta_query' 	=> array(
					array(
						'key' 		=> 'user_id',
						'value'	 	=> $this->user->id
					)
				)	
		));
		
		if (!count($alerts)) {
			$this->no_priv();
		}
		
		$alert = $alerts->current();
		
		$vars = array(
			'alert_message'=>$alert->post_content
		);
		
		return $vars;
	}
	
	
	
	public function get_client_data($type) {
		return $this->get_client_data_vars($type);
	}
	
	
	
	protected function get_client_data_vars($type, $args = array()) {
	
		if ($type == 'dealer') {
			$json = DealerCollection::get_json_client_data($args);
		} elseif ($type == 'manufacturer') {
			$json = ManufacturerCollection::get_json_client_data($args);
		} elseif ($type == 'salesperson') {
			$json = SalesPersonCollection::get_json_client_data($args);
		} elseif ($type == 'accountant') {
			$json = AccountantCollection::get_json_client_data($args);
		}

		return array('aaData'=>$json);	
	}
	
	
	
	public function get_lead_data() {
		return $this->get_lead_data_vars();
	}
	
	
	
	protected function get_lead_data_vars() {
		$json = VehicleCollection::get_json_vehicle_data();
		return array('aaData'=>$json);	
	}
	
	
	
	public function get_alert_data() {
		return $this->get_alert_data_vars();
	}
	
	
	
	protected function get_alert_data_vars() {
		$json = AlertCollection::get_json_alert_data();
		return array('aaData'=>$json);
	}
	
	
	
	public function bid( $id ) {
		return $this->get_bid_vars($id);
	}
	
	
	
	protected function get_bid_vars($id) {
		$bidvars = array(
			'lead_found'=>False,
			'lead_is_valid'=>False,
			'oktogo'=>False,
			'confirming_bid'=>False,
			'placing_bid'=>False,
			'errors'=>Null,
			'bid_placed'=>Null,
			'min_amount'=>Null,
			'yourbidamount'=>Null
		);
	
		// Here we have to make sure that the Vehicle exists...
		$vehicle = VehicleCollection::get_by_id($id);
		
		if (!$vehicle) {
			$bidvars['lead_found'] = False;
		} else {
			$bidvars['lead_found'] = True;
			
			// Check out lead is valid
			if ( !VehicleCollection::is_active($id) ) {
				$bidvars['lead_is_valid'] = False;
			} else {
				$bidvars['lead_is_valid'] = True;
				
				$bidvars['oktogo'] = True;
				
				$min_amount = BidCollection::getMinAmount($vehicle->id);
				
				if ( !isset($_REQUEST['confirm']) && !isset($_REQUEST['revise']) && !isset($_REQUEST['submit']) ) {
					$f = $this->place_bid_form($id, $min_amount);
					$bidvars['placing_bid'] = True;
					$bidvars['min_amount'] = $min_amount;
				}
				
				if ( isset($_REQUEST['submit'])	) {
					$f = $this->place_bid_form($id, $min_amount);
					$f->applyUserInput(True);
					if (!$f->validate()) {		
						// Probably do nothing, input validation will catch it
						$bidvars['placing_bid'] = True;
						$bidvars['errors'] = True;
						$bidvars['min_amount'] = $min_amount;
					} else { // Validated!
						$bidvars['confirming_bid'] = True;
						$yourbidamount = $f->amount->value;
						$bidvars['yourbidamount'] = $yourbidamount;
						$f = $this->confirm_bid_form($id);
					}
				} elseif ( isset($_REQUEST['confirm']) ) {
					$f = $this->confirm_bid_form($id);
					$f->applyUserInput(True);
					// Bid confirmed... just display a confirmation message
					$yourbidamount = $f->amount->value;
					$bid = BidCollection::place($f->amount->value,$id, $vehicle->post_title);
					
					$hitfigure = HitFigure::getInstance();
					$hitfigure->trigger_action('bid_placed', array(
						'vehicle'	=>$vehicle,
						'bid'		=>$bid
					));
					
					$bidvars['bid_placed'] = True;
					$bidvars['yourbidamount'] = $yourbidamount;
					$f = null;
				} elseif ( isset($_REQUEST['revise']) ) {
					// Back to the top...
					$bidvars['placing_bid'] = True;
					$bidvars['min_amount'] = $min_amount;
					$f =$this->place_bid_form($id, $min_amount);
				}
			}
		}
		
		$form = $f ? $f->render() : '';
		$vars = array('form'=>$form) + $vehicle->attributes + $vehicle->post_meta + $bidvars;
		
		return $vars;	
	}
	
	
	
	protected function place_bid_form( $id, $min_amount = 250 ) {
		$f = new \FormHelper('place_bid');
		$f->method = "POST";	
	
		$i = new \Input('amount');
		$i->setProperties(array(
			'name' =>'amount',
			'text' =>'Amount',
			'required'=>True,
			'validate_func'=>function($self) use ($min_amount) {
				// Check if this is more than Min Amount
				$amount = BidCollection::convertAmount($self->value);
				if ($amount <= $min_amount) {
					return "Amount must be higher than the minimum amount";
				}
				return True;
			}
		));
		$f->add($i);
		
		$b = new \Button('submit');
		$b->setProperties(array(
			'name'	=> 'submit',
			'text'	=> 'Submit'
		));
		$f->add($b);
		
		return $f;
	}
	
	
		
	protected function confirm_bid_form( $id ) {
		$amount = $_REQUEST['amount'];
	
		// Display our 'Confirmation' page...
		$f = new \FormHelper('place_bid');
		$f->method = "POST";
		
		$i = new \HiddenInput('amount');
		$i->setProperties(array(
			'name'=>'amount',
			'value'=>$amount
		));
		$f->add($i);
		
		$b = new \Button('confirm');
		$b->setProperties(array(
			'name'	=> 'confirm',
			'text'	=> 'Confirm'
		));
		$f->add($b);
		
		$b = new \Button('revise');
		$b->setProperties(array(
			'name'	=> 'revise',
			'text'	=> 'Revise'
		));
		$f->add($b);	
	
		return $f;
	}


	public function get_admin_vars($vars = array()) {
		$vars['current_user_id'] 		= $this->user->id;
		$vars['hide_salesperson'] 		= True;
		$vars['hide_add_salesperson'] 	= True;
		$vars['hide_accountant'] 		= True;
		$vars['hide_add_accountant'] 	= True;
		
		return $vars;
	}
}