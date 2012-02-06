<?php

namespace hitfigure\models;
use WPException, WP_Error, WP_User, DateTime;

/* Vehicle Models */

class VehicleCollection extends PostCollection {
	public static $model = "hitfigure\models\Vehicle";
	
	public static $default_filter = array(
		'post_type' => 'cpt-vehicle',
        'posts_per_page' => 10,
        'order' => 'DESC',
        'paged' => '1'
    );
    
    public static function get_by_id( $id ) {
    	/*
    	 * Should use ::get($args) here, but gives an error...
    	 * instead limit the query to one
    	 */ 
		$vehicles = self::filter(array('p'=>$id, 'posts_per_page'=>1));
		if (!count($vehicles)) {
			return Null;
		}
		return $vehicles->current();    
	}
	
	public static function is_active( $id ) {
		add_filter( 'posts_where', 'hitfigure\models\filter_where_48hours');
		$vehicle = self::get_by_id( $id ); 
		remove_filter( 'posts_where', 'hitfigure\models\filter_where_48hours' );
				
		if ($vehicle) {
			return $vehicle;
		}
	}
	
	public static function active_leads($args = array()) {
		add_filter( 'posts_where', 'hitfigure\models\filter_where_48hours');
		$vehicles = self::filter($args);
		remove_filter( 'posts_where', 'hitfigure\models\filter_where_48hours' );
		return $vehicles;
	}
	
	public static function time_left( $id ) {
		$vehicle = self::is_active($id);
		if ($vehicle) {
			return $vehicle->time_left();
		} else {
			return 'Expired';
		}
	}	
	
	public static function get_json_vehicle_data() {		
		$vehicles_json = array();
		$vehicles = self::active_leads();
		
		foreach ($vehicles as $vehicle) {
			$vehicles_json[] = $vehicle->to_json();
		}
		
		return $vehicles_json;
	}
		
}



class Vehicle extends Post {
	public $defaults = array('post_type' => 'cpt-vehicle');
	
	public function save_dealers() {
		global $wpdb;
	
    	// Run this when we create a new vehicle
    	$vehicle_id 		= $this->id;
    	$vehicle_zipcode 	= $this->post_meta['seller_zipcode'];
    	
    	$query = "CALL get_closestdealers($vehicle_id, $vehicle_zipcode)";
    	var_dump($query);
    	
    	$results = $wpdb->get_results("CALL get_closestdealers($vehicle_id, $vehicle_zipcode);");
    	$wpdb->show_errors();
    	echo $wpdb->print_error(); 
    	return $results; 	
	}
	
	public function get_dealers() {
		// Get the dealers for the vehicle from wp_closestdealers
		$vehicle_id = $this->id;
		return $wpdb->get_results("SELECT * FROM wp_closestdealers WHERE vehicle_id = $vehicle_id");
	}
	
	public function add_attachments($attachments) {
		// Attachements should be an array of VehicleAttachments.
		// They should be in the order they should appear.
		
		$post_id = $this->id;
		delete_post_meta( $post_id, '_attachments' );
		
		foreach ($attachments as $i=>$attachment) {
			$attachment_details = array (
	            'id' => $attachment->id,
	            'title' => $attachment->post_title,
	            'caption' => $attachment->post_content,
	            'order' => $i
	        );
	        
			// serialize data and encode
			$attachment_serialized = base64_encode( serialize( $attachment_details ) );

			// add individual attachment
			add_post_meta( $post_id, '_attachments', $attachment_serialized );	
		}
		
		// And that's it... we should have already set the post_parent when we made the attachments		
	}
	
	public function get_attachments() {
		$_attachments = array();
		$attachments = attachments_get_attachments($this->id);
		foreach($attachments as $attachment) {
			$_attachment = new VehicleAttachment(array('id'=>$attachment['id']));
			$_attachment->fetch();
			$_attachments[] = $_attachment;
		}
		return $_attachments;
	}
	
    public function save() {
       	parent::save();       	
    }	
	
	public function time_left($format="%d days %h hours %i minutes %s seconds") {
	
		$postDateStart 	= new DateTime($this->post_date);
		$postDateEnd 	= new DateTime($this->post_date);
		$postDateEnd->modify('+2 day');
					
		$todaysDate = new DateTime;
		
		if ($todaysDate > $postDateEnd) {
			// Expired...
			return;
		}
		
		$interval = $todaysDate->diff($postDateEnd);
		
		return $interval->format($format);	
	}
	
	public function to_json() {
		$json = array();
		
		$json['vehicle_model'] 		= isset($this->post_meta['vehicle_model']) ? $this->post_meta['vehicle_model'] : '';
		$json['vehicle_make'] 		= isset($this->post_meta['vehicle_make']) ? $this->post_meta['vehicle_make'] : '';
		$json['vehicle_mileage'] 	= isset($this->post_meta['vehicle_mileage']) ? $this->post_meta['vehicle_mileage'] : '';
		$json['time_left'] 			= $this->time_left('%d d %h hr %i mn');
		$json['bid_status'] 		= $this->bid_status();
		$json['bid_offers'] 		= $this->bid_offers() ? 'Yes' : 'No';
		$json['view_vehicle']		= display_mustache_template('viewitemlink', array('url'=>'/admin/lead/'.$this->id, 'text'=>'View Vehicle'), False);
		return $json;
	}
	
	public function bid_status() {
		$status = BidCollection::bidStatus($this->id);
		$status_text = '';
		
		if ($status === -1) {
			$status_text = '';
		} elseif ($status === 1) {
			$status_text = 'Leading';
		} elseif ($status === 0) {
			$status_text = 'Trailing';
		}
		
		return $status_text;	
	}
	
	public function bid_offers() {
		$status = BidCollection::bidStatus($this->id);
		
		if ($status != -1) {
			return True;
		}
	}	
	
	public function seller_new_bid_email($bid) {
		// Send off an email to the seller
		$vars = array('time_left'=>$this->time_left()) + $this->post_meta + $bid->post_meta;
		$message = display_mustache_template('sellernewbid', $vars, False);
		
		$hitfigure = HitFigure::getInstance();
		$hitfigure->sent_email("New Bid on your vehicle!", $message, $this->post_meta['seller_email']);
	}
	
}


/* Vechicle Image Attachment */

class VehicleAttachment extends Attachment {

	public function save() {
		// Here we have to update the Attachments plugin meta...

		parent::save();
	}

	public function parse( &$postdata ) { 
		$ret = parent::parse($postdata);
		
		$ret['vehicle_img_full'] 	= wp_get_attachment_image( $ret['id'], 'vehicle_img_full' );
		$ret['vehicle_img_thumb'] 	= wp_get_attachment_image( $ret['id'], 'vehicle_img_thumb' );
		
		return $ret;
	}
	
	
}


/* Alert Models */

class AlertCollection extends PostCollection {
	public static $model = "hitfigure\models\Alert";
	
	public static $default_filter = array(
		'post_type' => 'cpt-alert',
        'posts_per_page' => 10,
        'order' => 'DESC',
        'paged' => '1'
    );
    
    public function new_alert($alert_type, $user_id = Null) {
    	// Add a new alert
    	
    	$hitfigure = HitFigure::getInstance();
    	
    	if (!$user_id) { // Assume it's the current user
    		$user_id = $hitfigure->admin->user->id;
    	}
 
    	/* Choose our Alert subclass depending on type */
    	
    	$class_name = 'Alert'.ucfirst($type);
    	
    	if (!class_exists($class_name)) {
    		// No class matching this type
    		return;
    	}
    	
    	$args = array(
    		'user_id' => $user_id
    	);
    	
    	$class = new $class_name($args);
    	$class->send_email();
    	$class->save();
    
    }
    
    public function get_by_id( $id ) {
		$alerts = self::filter(array('p'=>$id, 'posts_per_page'=>1));
		if (!count($alerts)) {
			return Null;
		}
		return $alerts->current();     
    }
    
    public function dismiss_alert( $id ) {
    	// Simple way to find an alert by id and dismiss it
    	$alert = self::get_by_id($id);
    	if ($alert) {
    		$alert->fetch();
    		$alert->dismiss();
    	}
    }
}



class Alert extends Post {
	public $defaults = array('post_type' => 'cpt-alert');
	
	public function save() {
		
		if ( isset($this->attributes['user_id']) ) {
			$this->post_meta['user_id'] = $this->attributes['user_id'];
			unset($this->attributes['user_id']);
		}
		
		parent::save();
	}
	
	protected function render_content() {
		// Get our Mustache template and render it
		$template_name = 'alert'.$this->post_meta['alert_type'];
		$vars = $this->get_vars();
		
		// Get our user information...
		$client = new Client(array('id'=>$this->post_meta['user_id']));
		$client->fetch();
		$client_vars = $client->get_vars();
		
		$vars = $vars + $client_vars;
		
		$content = display_mustache_template($template_name, $vars);
		$this->post_content = $content;
	}
	
	public function get_vars() {
		$vars = array(
			'alert_title' 	=> $this->post_title,
			'alert_content'	=> $this->post_content,
			'alert_id'		=> $this->id
		);	
		// Add all our post_meta...
		$vars = $vars + $this->post_meta;
		
		return $vars;
	}
	
	public function send_email() {
		// Get the content and send it in an email
		// with appropriate headers etc.
		
		$client = new Client(array('id'=>$this->post_meta['user_id']));
		$client->fetch();
		$ok = $client->can_recieve_alert_for($this->post_meta['alert_type']);
		
		if (!$ok) {
			return;
		}
		
		$message 	= $this->post_content;
		$to 		= $client->user_email;
		$subject	= $this->post_title;
		
		$hitfigure = HitFigure::getInstance();
		$hitfigure->send_email($subject, $message, $to);
	}
	
	public function dismiss() {
		// Set the alert_dismissed to true and save it
		
		$this->post_meta['alert_dismissed'] = True;
		$this->save();
	}
	
}



class AlertWon extends Alert {
	public $defaults = array(
		'post_type' 		=> 'cpt-alert',
		'post_title'		=> 'Won! %s',		
		'post_meta'			=> array(
			'post_content'		=> null,
			'alert_type'		=> 'won',
			'alert_status'		=> 'green',
			'alert_dismissed'	=> false
		)
	);
}



class AlertOutbid extends Alert {
	public $defaults = array(
		'post_type' 		=> 'cpt-alert',
		'post_title'		=> 'Outbid! %s',		
		'post_meta'			=> array(
			'post_content'		=> null,
			'alert_type'		=> 'outbid',
			'alert_status'		=> 'red',
			'alert_dismissed'	=> false
		)
	);
}



class AlertNewbid extends Alert {
	public $defaults = array(
		'post_type' 		=> 'cpt-alert',
		'post_title'		=> 'New bid! %s',		
		'post_meta'			=> array(
			'post_content'		=> null,
			'alert_type'		=> 'newbid',
			'alert_status'		=> 'blue',
			'alert_dismissed'	=> false
		)
	);
}



/* Bid Models */

/*
 * NOTE: $parent_id refers to the vehicle id that bid belongs to.
 */

class BidCollection extends PostCollection {
	public static $model = "hitfigure\models\Bid";
	
	public static $default_filter = array(
		'post_type' => 'cpt-bid',
        'posts_per_page' => 10,
        'order' => 'DESC',
        'paged' => '1'
    );
    
    public static function getHighestBid($parent_id) {
    	$bids = self::getTopBid($parent_id);
    	if (!count($bids)) {
    		// No Bids Yet...
    		return 0;
    	}
    	$bid = $bids->current();
    	    	    	
    	$amount = self::convertAmount($bid->post_meta['amount']);
    	return $amount;
    }
    
    public static function getHighestBidder($parent_id) {
    	$bids = self::getTopBid($parent_id);
     	if (!count($bids)) {
    		// No Bids Yet...
    		return;
    	}
    	$bid = $bids->current();
    	
    	$bidder_id = $bid->post_meta['user_id'];
    	$client = new Client(array('id'=>$bidder_id));
    	
    	return $client;
    }
    
    public static function getTopBid($parent_id) {
    	return self::filter(array('post_parent'=>$parent_id, 'posts_per_page'=>1));
    }
    
    public static function getMinAmount($parent_id) {
    	$amount = self::getHighestBid($parent_id);
    	return self::determineMinAmount($amount);
    }
    
    private static function determineMinAmount($amount) {
    	// Min Amount Algorithm
    	if ($amount < 50000) {
    		return $amount+250;
    	} else {
    		return $amount+500;
    	}    
    }
    
    public static function place($amount, $parent_id, $lead_name) {
    	$hitfigure = HitFigure::getInstance();
    	$user = $hitfigure->admin->user;
    	$user_id = $user->id;
    	$user_login = $user->data->user_login;
    	
    	$args = array(
    		'post_title'=>"Bid on $lead_name for $amount by $user_login",
    		'post_parent'=>$parent_id,
    		'post_status'=>'publish',
    		'post_meta'=>array(
    			'lead_name'=>$lead_name,
 	   			'user_id'=>$user_id,
    			'user_login'=>$user_login,
    			'amount'=>self::convertAmount($amount) 			
    		)
    	);
    	
    	$bid = new Bid($args);
    	$bid->save();
    	
    	return $bid;
    }
    
    public static function convertAmount($amount) {
		return 0+$amount;   
    }

	public static function yourHighestBid($parent_id) {
		$hitfigure = HitFigure::getInstance();
    	$user = $hitfigure->admin->user;
    	$user_id = $user->id;
    	$bids = self::filter(array('meta_key'=>'user_id', 'meta_value'=>$user_id, 'parent_id'=>$parent_id, 'posts_per_page'=>1));
    	if (!count($bids)) {
    		// No Bids Yet...
    		return 0;
    	}
    	$bid = $bids->current();
    	$amount = self::convertAmount($bid->post_meta['amount']);
    	return $amount;
	}
    
    public static function bidStatus($parent_id) { 
   		$bids = self::getTopBid($parent_id);
   		if (!count($bids)) {
   			return -1;
   		}

		$hitfigure = HitFigure::getInstance();
     	$user = $hitfigure->admin->user;
    	$user_id = $user->id;    		
   		
   		$bid = $bids->current();
   		   		
   		if ( (int)$bid->post_meta['user_id'] == (int)$user_id ) {
   			return 1;
   		} else {
   			return 0;
   		}
    }
}



class Bid extends Post {
	public $defaults = array('post_type' => 'cpt-bid');
}



/* Client Model */

class ClientCollection extends UserCollection {
	public static $model = "hitfigure\models\Client";	
	public static $role = Null;

	public static function filter( $kwargs ) {
		$kwargs['role'] = static::$role;
		return parent::filter( $kwargs );
	}
	
	public static function get_json_client_data($args = array()) {	
		$clients = static::filter($args);
		$clients_json = array();
		
		foreach ($clients as $client) {
			$clients_json[] = $client->to_json();
		}
		
		return $clients_json;
	}
	
}



class Client extends User {
	public $defaults = array();
	public $role = '';
	public $metakeys = array(
		'business_name',
		'first_name',
		'last_name',	
		'phone',
		'address',
		'address2',
		'city',			
		'state',			
		'zipcode',
		'user_parent'
	);

    public function validate() {       
        if ( !empty($this->id) ) {
            // Don't accidently set our password to empty
            if ( isset($this->user_pass) && trim($this->user_pass) == '' )
                unset( $this->user_pass );
        }
    }

    public function register() {
        $this->validate();

        $this->user_meta = array_merge(
            array_diff_assoc($this->attributes, parse_user($this->attributes)),
            array('user_pass' => wp_hash_password($this->user_pass))
        );

		$r = wp_create_user( $this->user_login, $this->user_pass, $this->user_email );
		if ( !is_wp_error($r) ) {
			$this->id = $r;
		}
		
		return $r; // either WP_Error or ID;
    }
    
    
    public function get_vars() {
    	$vars = array(
    		'user_login' 			=> $this->user_login,
			'user_business_name' 	=> $this->business_name,
			'user_first_name' 		=> $this->first_name,
			'user_last_name'		=> $this->last_name,
			'user_email'			=> $this->data->user_email,
			'user_phone'			=> $this->phone,
			'user_address1'			=> $this->address,
			'user_address2'			=> $this->address2,
			'user_city'				=> $this->city,
			'user_state'			=> $this->state,
			'user_zipcode'			=> $this->zipcode,
			'user_login'			=> $this->data->user_login
    	);
    	
    	return $vars;
    }
    
    public function parse( &$postdata ) { // Modify to also get our usermeta
        $ret 		=& parent::parse( $postdata );
        $id			= $ret['id'];
        
        foreach ($this->metakeys as $key) {
        	 $ret[$key] = get_user_meta($id, $key, True);
        }
        
        return $ret;
    } 
    
    public function save() {
     	parent::save();
    }
    
    public function to_json() {
   		$json = array();
		
		$json['business_name'] = $this->business_name;
		$json['city'] = $this->city;
		$json['state'] = $this->state;
		$json['reports'] = 0; // Probably a link to the reports page?
		$json['edit_client'] = display_mustache_template('viewitemlink', array('url'=>'/admin/edit/'.$this->id, 'text'=>'Edit '.ucfirst($this->role)), False);
		
		return $json; 
    }
    
    
    public function can_recieve_alert_for($type) {
    	// Check user prefs, for now just say... 
    	return true;
    }
    
}



/* Dealer Model */

class DealerCollection extends ClientCollection {
	public static $model 	= "hitfigure\models\Dealer";
	public static $role 	= "dealer";
	
	
	
	
}



class Dealer extends Client {
	public $role = 'dealer';
	
	public function register() {
		$r = parent::register();
		if ( !is_wp_error($r) ) {
			// Set the role for this user...
			$u = new WP_User( $r );
			$u->set_role( 'dealer' );	
		}
		return $r;
	}
}



/* Manufacturer Model */ 

class ManufacturerCollection extends ClientCollection {
	public static $model 	= "hitfigure\models\Manufacturer";
	public static $role 	= "manufacturer";

}



class Manufacturer extends Client {
	public $role = 'manufacturer';
	
	public function register() {
		$r = parent::register();
		if ( !is_wp_error($r) ) {
			// Set the role for this user...
			$u = new WP_User( $r );
			$u->set_role( 'manufacturer' );	
		}
		return $r;
	}
}