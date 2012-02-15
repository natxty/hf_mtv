<?php

namespace hitfigure\models;


/*
 * Our base Admin class
 * Each of our other Admin classes will apply contraints to this.
 * For consistency, any method that should be overridden is protected. 
 */
 
 
class Admin {
	public $role = '';

	public function __construct($role) {
		$this->role = $role;
		$this->user = ClientCollection::get_current();
	}



	protected function nopriv() {
		$hitfigure = HitFigure::getInstance();
		display_mustache_template('nopriv', $hitfigure->template_vars());
		exit;
	}		



	public function dashboard() { // Your adventure starts here...
		$hitfigure = HitFigure::getInstance();
	
   		$filter = array(
			'meta_query' => array(
					
					array(
						'key' 		=> 'user_id',
						'value'	 	=> $hitfigure->admin->user->id
					)
					,array(
						'key' 		=> 'alert_dismissed',
						'value'		=> true,
						'compare' 	=> '!='
					)
				),
			
			'posts_per_page' => 3
   		);

		$results = AlertCollection::filter($filter);
		
		$current_alerts = array();
		
		foreach ($results as $alert) {
			$vars 					= $alert->get_vars();
			$vehicle_id 			= $alert->post_meta['vehicle_id'];
			$vehicle 				= new Vehicle(array('id'=>$vehicle_id));
			$vars['vehicle_url']	= $vehicle->get_url();
			
			$current_alerts[] = $vars;
		}
		
		
		$results = VehicleCollection::registered_active_leads(array('posts_per_page'=>3));
		
		$hitfigure_watch = array();
		
		foreach ($results as $vehicle) {
			$hitfigure_watch[] = $vehicle->get_vars();
		}
		
		$stats_unbid_vehicles = array();
		$stats_active_bids 	= array();
		$stats_leading_bids = array();
		
		global $wpdb;
		
		$two_days_ago = date('Y-m-d h:i:s', strtotime('-2 days'));
		
		/*
		$results = 	$wpdb->get_results("
			SELECT
				(
					SELECT 
						COUNT(*) 
					FROM 
					( 
						SELECT
							p.ID 
						FROM 
							wp_posts as p 
						INNER JOIN 
							wp_postmeta as bm 
						ON 
							bm.meta_value = 1 
						INNER JOIN 
							wp_posts as b 
						ON 
							b.ID = bm.post_id
						WHERE
							b.post_type = 'cpt-bid'
							AND
							bm.meta_key = 'user_id'
							AND
							b.post_parent = p.ID
							AND
							p.post_status = 'publish'
							AND
							post_date > $two_days_ago
						GROUP BY 
							p.ID 
					) as tmp
				) as stats_active_bids,
				(
					SELECT
						COUNT(*)
					FROM
						
		
		
		");
		*/
		
		$args = array(
			'current_alerts' 			=> $current_alerts,
			'hitfigure_watch'			=> $hitfigure_watch,
			'stats_unbid_vehicles'		=> null,
			'stats_active_bids'			=> null,
			'stats_leading_bids'		=> null,
			'pgheader'					=> $this->user->business_name
		);
		
		if (count($current_alerts)) {
			$hitfigure->vars->add('has_current_alerts', True);
		}
		
		$hitfigure->vars->add($args);
		
		return $args;	
	}



	public function set_current_nav_link($page) {
		$hitfigure = HitFigure::getInstance();
		$hitfigure->vars->merge('is_'.$page,'current');	
	}
	
	

	public function register_client( $type ) {
		$this->register_client_vars($type);
	}



	protected function register_client_vars( $type ) {
		$form = $this->update_client($type);
		$hitfigure = HitFigure::getInstance();
		$hitfigure->vars->add('form', $form);
		$this->set_current_nav_link('register_client_'.$type);	
	}



	public function edit_client( $id ) {
		$type	= Null;
		
		$client = new Client(array('id'=>$id));
		$client->fetch();
				
		$this->edit_client_vars($client);
	}



	protected function edit_client_vars( $client ) {
		$type = $client->roles[0];
		$form = $this->update_client( $type, $client );
		$adminvars = array('form'=>$form);
		
		if ( isset($_GET['clientregistered']) ) {
			$adminvars['clientregistered'] = True;
			$adminvars['clienttype'] = $_GET['clientregistered']; // -- This is the Type of client
		}		
		
		$hitfigure = HitFigure::getInstance();
		$hitfigure->vars->add($adminvars);
		
		$title 		= "Edit ".ucfirst($type)." | ".$client->business_name;
		$pgheader 	= "Editing " .$client->business_name;

		$hitfigure->vars->merge("title", $title);
		$hitfigure->vars->merge("pgheader", $pgheader);
		
		if($client->id == $this->user->id) {
			$this->set_current_nav_link('edit_profile');
		} else {
			$this->set_current_nav_link('edit_client_'.$type);
		}
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
		
		$this->add_basic_info_inputs($f, $client);
		
		if ( !in_array($type, $dealer_employees) ) {
			$this->add_additional_info_inputs($f);		
		}
		
		$this->add_alert_email_opts($f, $type);
		
		$this->add_account_inputs($f, $id);
		
		$b = new \Button('submit');
		$b->setProperties(array(
			'name'	=> 'submit',
			'text'	=> 'Submit'
		));
		$f->add($b);
		
		
		
		if ($id) { // Do some additional stuff if we're editing
			$f->password->required 			= False;
			$f->confirmpassword->required 	= False;
			$f->user_login->required 		= False;
			$f->user_login->readonly 		= True;
		}
		
		
				
		if ( isset($_REQUEST['submit']) ) {
			
			$f->applyUserInput(True);
			
			if (!$f->validate()) {
			
				$f->password->value = '';
				$f->confirmpassword->value = '';
			
			} else {
				// Validation Success, do our post-form processing
				$client = null;
				
				if ( !in_array($type, $dealer_employees) ) {
					$args = $this->get_new_client_form_args($f, $id);
				} else {
					$args = $this->get_new_client_form_args_dealer_employee($f, $id);
				}
				
				$args = $args + $this->get_new_client_form_args_email_opts($f,$type);
				

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
				
				$this->set_edit_client_form_values_email_opts($f, $client, $type);
			}	
		}	
	
		return $f->render();
	}
	
	
	
	protected function get_alert_email_opt_list($type) {
		$a = array(
			'newbid'	=>'New Bids',
			'outbid'	=>'Outbid',
			'newlead'	=>'New Leads',
			'won'		=>'Won Leads'
		);
		
		if ($type == 'accountant') {
			return array(); // Accountants don't get nuthin...
		}
		
		return $a;
	}
	
	
	
	protected function add_alert_email_opts($f, $type) {
		$opt_list = $this->get_alert_email_opt_list($type);
		foreach( $opt_list as $name=>$text) {
			$this->get_alert_email_radioboxes($text, 	'alert_email_opt_'.$name, 	$f);
		}
	}
	
	
	
	protected function get_alert_email_radioboxes($text, $id, $f) {
	    $l = new \Label($id.'_label');
	    $l->setProperties(array(
							  'text' => $text
	                      ));
	    $f->add($l);		
		
	    $r = new \RadioGroup($id);
		$r->setProperties(array(
			'name'=>$id
		));
	
		$r->add_radio_button($id.'_y', array('text'=>'Yes', 'value'=> 'Yes'));
		$r->add_radio_button($id.'_n', array('text'=>'No', 'value'=> 'No'));
	    $f->add($r);
	}



	protected function add_business_name_input($f) {
		$i = new \Input('business_name');
		$i->setProperties(array(
			'name' =>'business_name',
			'text' =>'Business Name',
			'required'=>True
		));
		$f->add($i);		
	}
	
	
	
	protected function add_basic_info_inputs($f, $client) {
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
			'validate_func'=>function($self) use ($client) {
				if ( ($client && ($client->data->user_email!=$self->value)) && email_exists($self->value) ) {
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
	
	
	
	protected function add_account_inputs($f, $id) {
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
	
	
	
	protected function get_new_client_form_args_email_opts($f, $type) {
		$args = array();
		$opt_list = $this->get_alert_email_opt_list($type); 
		
		foreach($opt_list as $name=>$text) {
			$id = 'alert_email_opt_'.$name;
			$args[$id] = $f->{$id}->value;
		}
		
		return $args;
	}



	protected function set_edit_client_form_values_email_opts($f, $client, $type) {
		$opt_list = $this->get_alert_email_opt_list($type); 
		
		foreach($opt_list as $name=>$text) {
			$id = 'alert_email_opt_'.$name;
			$value = $client->{$id};
			$f->{$id}->value = $value ? $value : 'Yes';
		}	
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
	
		$this->get_view_lead_vars($vehicle);
	}
	
	
	
	protected function get_view_lead_vars( $vehicle ) {
		$hitfigure = HitFigure::getInstance();
	
		$min_amount 			= BidCollection::getMinAmount($vehicle->id);
		$highest_amount 		= BidCollection::getHighestBid($vehicle->id);
		$your_highest_amount 	= BidCollection::yourHighestBid($vehicle->id);
		$bid_status 			= $this->bid_status_text(BidCollection::bidStatus($vehicle->id));
		
		$expired_with_winner = $vehicle->expired_with_winner() == $this->user->id;
		
		if ( ($this->role == 'administrator') || $expired_with_winner ) {
			$hitfigure->vars->add('lead_view_seller_info',True);
			if ($expired_with_winner) {
				$hitfigure->vars->add('lead_view_seller_email_form_button', True);
			}
		}
		
		$bidvars = array(
			'timeleft'				=>VehicleCollection::time_left($vehicle->id),
			'min_amount'			=>BidCollection::mf($min_amount),
			'highest_amount'		=>BidCollection::mf($highest_amount),
			'your_highest_amount'	=>BidCollection::mf($your_highest_amount),
			'bid_status'			=>$bid_status
		);
		
		$attachments = array('attachments'=>$vehicle->get_attachments());
			
		$vehicle_name 		= $vehicle->post_title;
		$vehicle_post_date	= $vehicle->get_post_date();
			
		
		$hitfigure->vars->add($vehicle->get_vars());
		$hitfigure->vars->add($bidvars);
		$hitfigure->vars->add($attachments);
		$hitfigure->vars->add("pgheader","$vehicle_name <small>$vehicle_post_date</small>");
		$hitfigure->vars->merge("title","Lead | $vehicle_name");
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
			$this->nopriv();
		}
		
		$alert = $alerts->current();

		$this->get_view_alert_vars($alert);
	}
	
	
	
	protected function get_view_alert_vars( $alert ) {
		$hitfigure = HitFigure::getInstance();
		
		$alert_post_date = $alert->get_post_date();
		
		$vars = array(
			'alert_message'	=>$alert->post_content,
			'pgheader'		=>$alert->post_title ." <small>$alert_post_date</small>"
		);		
		
		$hitfigure->vars->merge('title', 'View Alert | ' . $alert->post_title);
		$hitfigure->vars->add($alert->get_vars());
		$hitfigure->vars->add($vars);	
	}
	
	
	
	public function dismiss_alert($alert_id, $redirect) {
		AlertCollection::dismiss_alert($alert_id);		
		
		if ($redirect) {
			wp_redirect($redirect, '302');
		}
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
	
	
	
	public function get_won_lead_data() {
		return $this->get_won_lead_data_vars();
	}



	protected function get_won_lead_data_vars() {
		$args = array(
			'meta_query'=>array(
					array(
						'key' 		=> 'winner_id',
						'value'	 	=> $this->user->id
					)				
			)
		);
		$json = VehicleCollection::get_json_vehicle_data($args, false);
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
		$this->get_bid_vars($id);
	}
	
	
	
	protected function get_bid_vars($id) {
		$hitfigure = HitFigure::getInstance();
		
		$bidvars = array(
			'lead_found'=>False,
			'lead_is_valid'=>False,
			'oktogo'=>False,
			'confirming_bid'=>False,
			'placing_bid'=>False,
			'errors'=>Null,
			'bid_placed'=>Null,
			'min_amount'=>Null,
			'yourbidamount'=>Null,
		);
	
		// Here we have to make sure that the Vehicle exists...
		$vehicle = VehicleCollection::get_by_id($id);
		
		if (!$vehicle) {
			$bidvars['lead_found'] = False;
			$title = 'Not Found';
			$hitfigure->vars->merge('title', $title);
			$hitfigure->vars->add('pgheader',$title);
			
		} else {
			$bidvars['lead_found'] = True;		
			
			// Check out lead is valid
			if ( !VehicleCollection::is_active($id) ) {
				$bidvars['lead_is_valid'] = False;
				$title = "Lead Expired";
				$hitfigure->vars->merge('title', $title);
				$hitfigure->vars->add('pgheader',$title);					
			} else {
				$title = "Bidding on ".$vehicle->post_title;
				$hitfigure->vars->merge('title', $title);
				$hitfigure->vars->add('pgheader',$title);
								
				$bidvars['lead_is_valid'] = True;
				
				$bidvars['oktogo'] = True;
				
				$min_amount = BidCollection::getMinAmount($vehicle->id);
				
				if ( !isset($_REQUEST['confirm']) && !isset($_REQUEST['revise']) && !isset($_REQUEST['submit']) ) {
					$f = $this->place_bid_form($id, $min_amount);
					$bidvars['placing_bid'] = True;
					$bidvars['min_amount'] = BidCollection::mf($min_amount);
				}
				
				if ( isset($_REQUEST['submit'])	) {
					$f = $this->place_bid_form($id, $min_amount);
					$f->applyUserInput(True);
					if (!$f->validate()) {		
						// Probably do nothing, input validation will catch it
						$bidvars['placing_bid'] = True;
						$bidvars['errors'] = True;
						$bidvars['min_amount'] = BidCollection::mf($min_amount);
					} else { // Validated!
						$bidvars['confirming_bid'] = True;
						$yourbidamount = $f->amount->value;
						$bidvars['yourbidamount'] = BidCollection::mf($yourbidamount);
						$f = $this->confirm_bid_form($id);
					}
				} elseif ( isset($_REQUEST['confirm']) ) {
					$f = $this->confirm_bid_form($id);
					$f->applyUserInput(True);
					// Bid confirmed... just display a confirmation message
					$yourbidamount 	= $f->amount->value;
					$bid 			= BidCollection::place($f->amount->value,$id, $vehicle->post_title);
					
					$hitfigure->trigger_action('bid_placed', array(
						'vehicle'	=>$vehicle,
						'bid'		=>$bid
					));
					
					$bidvars['bid_placed'] = True;
					$bidvars['yourbidamount'] = BidCollection::mf($yourbidamount);
					$f = null;
				} elseif ( isset($_REQUEST['revise']) ) {
					// Back to the top...
					$bidvars['placing_bid'] = True;
					$bidvars['min_amount'] 	= BidCollection::mf($min_amount);
					$f = $this->place_bid_form($id, $min_amount);
				}
			}
		}
		
		$form = $f ? $f->render() : '';
		$vars = array('form'=>$form) + $vehicle->get_vars() + $bidvars;
		
		
		$hitfigure->vars->add($vars);
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
				if ($amount < $min_amount) {
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
		
		return $vars + $this->user->get_vars();
	}
	
	
	
	public function email_seller_form($vehicle_id) {
		$vehicle = VehicleCollection::get_by_id($vehicle_id);
		
		if ($this->set_lead_status_seller_email_vars($vehicle)) {
			$this->get_email_seller_form_vars($vehicle);
		}
	} 
	
	
	public function set_lead_status_bid_vars($vehicle) {
		// Returns true if lead is found and is active, IE 'ok2go'
		$hitfigure = HitFigure::getInstance();
		
		// Check if lead is found
		if (!$vehicle) {
			$title = 'Not Found';
			$hitfigure->vars->merge('title', $title);
			$hitfigure->vars->add('pgheader',$title);
			$hitfigure->vars->add('lead_found',false);
			
		} else {
			$hitfigure->vars->add('lead_found',true);
			
			// Check if lead is active
			if ( !VehicleCollection::is_active($vehicle->id) ) {
				$hitfigure->vars->add('lead_is_valid',false);
				$title = "Lead Expired";
				$hitfigure->vars->merge('title', $title);
				$hitfigure->vars->add('pgheader',$title);				
			} else {
				$hitfigure->vars->add('lead_is_valid',true);
				$hitfigure->vars->add('oktogo',true);
				return true;
			}
		}	
	}
	
	
	
	public function set_lead_status_seller_email_vars($vehicle) {
		// Returns true if lead is found, expired, and the current user should be able to email the seller, IE 'ok2go'
		$hitfigure = HitFigure::getInstance();
		
		// Check if lead is found
		if (!$vehicle) {
			$title = 'Not Found';
			$hitfigure->vars->merge('title', $title);
			$hitfigure->vars->add('pgheader',$title);
			$hitfigure->vars->add('lead_found',false);
			
		} else {
			$hitfigure->vars->add('lead_found',true);
			
			// Check if lead is expired and the current user is the winner
			if ( $vehicle->expired_with_winner() != $this->user->id ) {
				$this->nopriv(); // Stop right there!			
			} else {
				$hitfigure->vars->add('lead_is_valid',true);
				$hitfigure->vars->add('oktogo',true);
				return true;
			}
		}	
	}
	
	
	
	protected function get_email_seller_form_vars($vehicle) {
		$hitfigure = HitFigure::getInstance();
		
		$title = 'Send email to seller of '.$vehicle->post_title;
		$hitfigure->vars->merge('title', $title);
		$hitfigure->vars->add('pgheader',$title);
	
		$f = new \FormHelper('email_seller_form');
		$f->method = 'POST';
		
		$t = new \TextArea('email_message');
		$t->setProperties(array(
			'name'		=>'email_message',
			'text'		=>'Message',
			'required'	=>True
		));
		$f->add($t);
		
		$b = new \Button('submit');
		$b->setProperties(array(
			'name'	=>'submit',
			'value'	=>'submit',
			'text'	=>'Submit'
		));
		$f->add($b);

	
		if ( isset($_REQUEST['submit'])) {
			$f->applyUserInput(True);
			if (!$f->validate()) {
				// Missing a required field?
				// Throw the form back at them...
				$hitfigure->vars->add('form',$f->render());	
			} else {
				// Send that email!!!
				$hitfigure->trigger_action('email_seller', array(
					'vehicle'	=> $vehicle,
					'message'	=> $t->value
				));
				
				// Modify our title
				$title = 'Sent email to seller of '.$vehicle->post_title;
				$hitfigure->vars->merge('title', $title);
				$hitfigure->vars->merge('pgheader',$title);				
				
				$hitfigure->vars->add('email_sent', True);
			}
		} else {
			// Give them the form...
			$hitfigure->vars->add('form',$f->render());
		}
	
		
		$hitfigure->vars->add($vehicle->get_vars());
	}


	
	public function view_clients($type) {
		$this->get_view_clients_vars($type);
	}
	
	
	
	protected function get_view_clients_vars($type) {
		$hitfigure = HitFigure::getInstance();
		
		extract(client_type_to_name($type));
		
		$hitfigure->vars->merge(array(
			'title'					=>'View ' . $pluralname,
			'pgheader'				=>'View ' . $pluralname,
			'client_type'			=>$type,
			'client_name'			=>$name
		));			
	}



	public function view_alerts() {
		$this->get_view_alerts_vars();
	}
	
	
	
	protected function get_view_alerts_vars() {
		$hitfigure = HitFigure::getInstance();
		
		$hitfigure->vars->merge(array(
			'title'					=>'View Alerts' ,
			'pgheader'				=>'View Alerts'
		));			
	}
	
	
	
	public function view_leads($type) {
		switch ($type) {
			case 'won':
				$this->get_view_won_leads_vars();
				break;
			default:
				$this->get_view_all_leads_vars();
		}
	}
	
	
	
	protected function get_view_all_leads_vars() {
		$hitfigure = HitFigure::getInstance();
	
		$hitfigure->vars->merge(array(
			'title'		=>'View Leads',
			'pgheader'	=>'View Leads',
			'is_all'	=>True
		));	
	}
	
	
	
	protected function get_view_won_leads_vars() {
		$hitfigure = HitFigure::getInstance();
		
		$hitfigure->vars->merge(array(
			'title'		=>'View Won Leads',
			'pgheader'	=>'View Won Leads',
			'is_all'	=>False
		));	
	}
}