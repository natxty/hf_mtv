<?php

namespace hitfigure\models;
use WPException, WP_Error, WP_User, DateTime, WP_Query;

/* Vehicle Models */

class VehicleCollection extends PostCollection {
	public static $model = "hitfigure\models\Vehicle";
	
	public static $default_filter = array(
		'post_type' 		=> 'cpt-vehicle',
        'posts_per_page' 	=> -1,
        'order' 			=> 'DESC',
        'paged' 			=> '1'
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
	
	public static function registered_active_leads($args = array()) {
		add_filter( 'posts_where', 'hitfigure\models\filter_where_registered_for_lead' );
		$vehicles = self::active_leads($args);
		remove_filter( 'posts_where', 'hitfigure\models\filter_where_registered_for_lead' );		
		return $vehicles;
	}
	
	public static function time_left( $id ) {
		$vehicle = self::is_active($id);
		if ($vehicle) {
			return $vehicle->time_left();
		}	
	}	
	
	public static function get_json_vehicle_data($args = array(), $active = true, $expired = false) {		
		$vehicles_json = array();
		if ($active) {
			$vehicles = self::active_leads($args);
		} else {
			if ($expired) {
				$vehicles = self::expired_leads($args);
			} else {	
				$vehicles = self::filter($args);
			}
		}
		
		foreach ($vehicles as $vehicle) {
			$vehicles_json[] = $vehicle->to_json();
		}
		
		return $vehicles_json;
	}
	
	public static function expired_leads($args = array()) {
		add_filter( 'posts_where', 'hitfigure\models\filter_where_48hours_past');
		$vehicles = self::filter($args);
		remove_filter( 'posts_where', 'hitfigure\models\filter_where_48hours_past' );
		return $vehicles;		
	}
	
	public static function is_expired( $id ) {
		add_filter( 'posts_where', 'hitfigure\models\filter_where_48hours_past');
		$vehicle = self::get_by_id( $id ); 
		remove_filter( 'posts_where', 'hitfigure\models\filter_where_48hours_past' );
				
		if ($vehicle) {
			return $vehicle;
		}
	}	
	
}



class Vehicle extends Post {
	public $defaults = array(
		'post_type' 	=> 'cpt-vehicle',
		'post_status'	=> 'publish'
	);
	
	public function save_dealers() {
		global $wpdb;
	
    	// Run this when we create a new vehicle
    	$vehicle_id 		= $this->id;
    	$vehicle_zipcode 	= $this->post_meta['seller_zipcode'];
    	
    	$query = "CALL get_closestdealers($vehicle_id, $vehicle_zipcode)";    	
    	$results = $wpdb->get_results("CALL get_closestdealers($vehicle_id, $vehicle_zipcode);");
    	
    	
    	$wpdb->db_connect(); // Brute force reset after stored procedure call
    	return $results; 	
	}
	
	public function get_full_name() {
		return $this->post_meta['vehicle_year'] . ' ' . $this->post_meta['vehicle_make'] . ' ' . $this->post_meta['vehicle_model'];
	}
	
	public function get_dealers() {
		// Get the dealers for the vehicle from wp_closestdealers
		$vehicle_id = $this->id;
		return $wpdb->get_results("SELECT * FROM wp_closestdealers WHERE vehicle_id = $vehicle_id");
	}
	
	public function add_attachments($attachments) {
		// Attachments should be an array of VehicleAttachments.
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
    	// If we don't unset this it gives all of these the same value
    	// and that results in the first image being repeated over and over
    	if ( isset($this->attributes['meta_data']['_attachments']) ) {
    		unset($this->attributes['meta_data']['_attachments']);
    	}
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
	
	public function expired_with_winner() {
		if (!$this->time_left()) { // Expired
			if ( (int)$this->post_meta['winner_id'] ) {
				return (int)$this->post_meta['winner_id'];
			} else {
				$client = BidCollection::getHighestBidder($this->id);
				return $client->id;
			}
		}
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
	
	public function get_vars() {
		$vars = $this->post_meta;
		$vars = $vars + array(
			'vehicle_title'		=>$this->post_title,
			'vehicle_name'		=>$this->post_title,
			'vehicle_id'		=>$this->id,
			'vehicle_url'		=>$this->get_url(),
			'vehicle_post_date'	=>$this->get_post_date(),
			'vehicle_time_left'	=>$this->time_left()
		);
		
		return $vars;
	}
	
	public function get_post_date($format="m/d/Y h:i") {
		return date($format, strtotime($this->post_date));
	}
	
	public function get_url() {
		return get_bloginfo('wpurl').'/admin/lead/' . $this->id;
	}
	
	public function seller_new_bid_email($bid) {
		// Send off an email to the seller
		$hitfigure = HitFigure::getInstance();
		
		$vars = array('time_left'=>$this->time_left(), 'vehicle_name'=>$this->post_title) + $this->post_meta + $bid->post_meta;
		$vars['amount'] = BidCollection::mf($vars['amount']);
		
		$message = display_mustache_template('sellernewbid', $vars, False);

		$vars = $hitfigure->get_wp_data(array(
			'message'			=>$message,
			'vehicle_name'		=>$this->post_title,
			'hf_says'			=>"New bid on",
			'email_header_img'	=>'hdr_seller-new-bid.jpg'
		));
		$html = display_mustache_template('emailshell', $vars, false);
		
		$hitfigure = HitFigure::getInstance();
		$hitfigure->send_email("New Bid on your vehicle!", $html, $this->post_meta['seller_email'], "HitFigure@Hitfigure.com", $message);
	}
	
	public function seller_won_bid_email($client) {
				
		$bid = BidCollection::getTopBid($this->id);
				
		$vars = array('vehicle_name'=>$this->post_title);
		$vars = $vars + $client->get_vars();
		$vars = $vars + $this->get_vars();
		$vars = $vars + $bid->get_vars();
		
		$message = display_mustache_template('sellerbidclosed', $vars, False);

		$hitfigure = HitFigure::getInstance();
		
		$vars = $hitfigure->get_wp_data(array(
			'message'			=>$message,
			'vehicle_name'		=>$this->post_title,
			'hf_says'			=>"Bidding has closed on",
			'email_header_img'	=>'hdr_seller-bidding-closed.jpg'
		));
		$html = display_mustache_template('emailshell', $vars, false);
		
		$hitfigure->send_email("Bidding has closed on your vehicle!", $html, $this->post_meta['seller_email'], "HitFigure@Hitfigure.com", $message);
	}
	
	
	
	public function seller_no_bid_email() {
		$vars = array('vehicle_name'=>$this->post_title) + $this->get_vars();
		
		$message = display_mustache_template('sellernobids', $vars, False);

		$hitfigure = HitFigure::getInstance();
		
		$vars = $hitfigure->get_wp_data(array(
			'message'			=>$message,
			'vehicle_name'		=>$this->post_title,
			'hf_says'			=>"Bidding has closed on",
			'email_header_img'	=>'hdr_seller-no-bids.jpg'
		));
		$html = display_mustache_template('emailshell', $vars, false);
		
		$hitfigure->send_email("Bidding has closed on your vehicle!", $html, $this->post_meta['seller_email'], "HitFigure@Hitfigure.com", $message);
	}	
	
	
	
	public function seller_confirm_new_lead_email($dealers = array()) {
		$hitfigure = HitFigure::getInstance();
		
		$vars = $hitfigure->get_wp_data() + $this->get_vars() + array('dealers'=>$dealers);
		
		$message = display_mustache_template('sellerconfirmnewlead', $vars, False);
		
		$vars = $hitfigure->get_wp_data(array(
			'message'			=>$message,
			'vehicle_name'		=>$this->post_title,
			'hf_says'			=>"New vehicle submitted",
			'email_header_img'	=>'hdr_seller-submitted.jpg'
		));
		$html = display_mustache_template('emailshell', $vars, false);

		$hitfigure = HitFigure::getInstance();
		$hitfigure->send_email("Thanks for submitting your vehicle!", $html, $this->post_meta['seller_email'], "HitFigure@Hitfigure.com", $message);		
	}
	
	
	public function seller_client_to_seller_email($message) {
		$hitfigure 	= HitFigure::getInstance();
		$vars 		= array('message'=>nl2p(stripslashes($message))) + $hitfigure->get_wp_data() + $this->get_vars() + $hitfigure->admin->user->get_vars();
		
		$amputated 	= display_mustache_template('sellerclienttoselleremail', $vars, False);
		
		$sender_name 	= $hitfigure->admin->user->business_name;
		$sender_email	= $hitfigure->admin->user->data->user_email;
		
		$vars = $hitfigure->get_wp_data(array(
			'message'			=>$amputated,
			'vehicle_name'		=>$this->post_title,
			'hf_says'			=>"Message from " . $sender_name,
			'email_header_img'	=>'hdr_seller-submitted.jpg'
		));		
		$html = display_mustache_template('emailshell', $vars, false);
		$hitfigure->send_email("New Message from ".$sender_name, $html, $this->post_meta['seller_email'], $sender_email, $amputated);
	}
	
}


/* Vehicle Image Attachment */

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
        'posts_per_page' => -1,
        'order' => 'DESC',
        'paged' => '1',
        'post_status'=>'publish'
    );
    
    public static function filter( $args = array() ) {
        $class = get_called_class();

        $ret = new $class();
        $ret->wp_query = new WP_Query( array_merge(static::$default_filter, $args) );
        $ret->wp_query->get_posts();
        
        foreach( $ret->wp_query->posts as $post ) {
			
			// Dynamically instantiate based on type
			$alert_type = get_post_meta($post->ID, 'alert_type', True);
			$model = "hitfigure\models\Alert".ucfirst($alert_type);
			
			if (!class_exists($model)) {
    			// No class matching this type
    			$model = self::$model;
    		}
			
            $p = new $model();

            try {
                $p->reload($post);
                $ret->add($p);
            } catch(ModelParseException $e) {
                # post is bad for some reason, skip it
                continue;
            }
        }

        return $ret;
    }    
    
    public static function new_alert($alert_type, $user_id = Null, $vehicle_id = Null) {
    	// Add a new alert
    	
    	$hitfigure = HitFigure::getInstance();
    	
    	if (!$user_id) { // Assume it's the current user
    		$user_id = $hitfigure->admin->user->id;
    	}
 
    	/* Choose our Alert subclass depending on type */
    	
    	$class_name = 'hitfigure\models\Alert'.ucfirst($alert_type);
    	
    	if (!class_exists($class_name)) {
    		// No class matching this type
    		return;
    	}
    	
    	$client = new Client(array('id'=>$user_id));
    	$client->fetch();
    	
    	$client_ids = array();
    	
    	if (in_array('dealer',$client->roles)) {
    		// We'll need to contact all the employees about this too...
    		$employees = ClientCollection::filter(array('meta_key'=>'user_parent','meta_value'=>$client->id));
    		foreach($employees as $employee) {
    			$client_ids[] = $employee->id;
    		}
    	}
    	
    	/*
    	 * Should the opposite also be true? That employee emails go up to Dealers?
    	 */
    	
    	// Finally, add the original user_id
    	$client_ids[] = $user_id;
    	
    	foreach( $client_ids as $client_id ) {
	    	$args = array(
	    		'user_id' => $client_id
	    	);
	    	
	    	if ($vehicle_id) {
	    		$args['vehicle_id'] = $vehicle_id;
	    	}
	    	
	    	$class = new $class_name($args);   	
	    	$class->save();
	    	
	    	$class->send_email();
		}
    	
    	return $class;
    }
    
    public static function get_by_id( $id ) {
		$alerts = self::filter(array('p'=>$id, 'posts_per_page'=>1));
		if (!count($alerts)) {
			return;
		}
		return $alerts->current();     
    }
    
    public static function dismiss_alert( $id ) {
    	// Simple way to find an alert by id and dismiss it
    	$alert = self::get_by_id($id);
    	if ($alert) {
    		$alert->dismiss();
    	}
    }
    
   	public static function get_json_alert_data() {
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
				)
   		);
   		
 
		$alerts = self::filter($filter);
		$json = array();

		
		foreach($alerts as $alert) {
			$json[] = $alert->to_json();
		}
		
		return $json;
   	}
}



class Alert extends Post {
	public $defaults = array('post_type' => 'cpt-alert', 'post_status'=>'publish');
	public $email_header_img = null;
	
	public function save() {
		
		if ( isset($this->attributes['user_id']) ) {
			$this->attributes['post_meta']['user_id'] = $this->attributes['user_id'];
			unset($this->attributes['user_id']);
		}
		
		if ( isset($this->attributes['vehicle_id']) ) {
			$this->attributes['post_meta']['vehicle_id'] = $this->attributes['vehicle_id'];
			unset($this->attributes['vehicle_id']);
		}
		
		$this->attributes['post_content'] = $this->render_content();
				
		parent::save();
	}
	
	protected function render_content($vars = array()) {
		// Get our Mustache template and render it
		$template_name = 'alert'.$this->post_meta['alert_type'];
		$vars = $vars + $this->get_vars();
		
		// Get our user information...
		$client = new Client(array('id'=>$this->post_meta['user_id']));
		$client->fetch();
		$client_vars = $client->get_vars();
		
		$hitfigure = HitFigure::getInstance();
		$vars = $hitfigure->get_wp_data() + $vars + $client_vars;
		
		$content = display_mustache_template($template_name, $vars, false);
		return $content;
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
		
		$vehicle_id = $this->post_meta['vehicle_id'];
		$vehicle = new Vehicle(array('id'=>$vehicle_id));
		
		$hitfigure = HitFigure::getInstance();
		$vars = $hitfigure->get_wp_data(array(
			'message'			=>$this->post_content, 
			'is_alert'			=>true,
			'vehicle_id'		=>$vehicle_id,
			'vehicle_name'		=>$vehicle->post_title,
			'hf_says'			=>$this->alert_type(),
			'email_header_img'	=>$this->email_header_img
		));
		
		$message 	= display_mustache_template('emailshell', $vars, false);
		$to 		= $client->data->user_email;
		$subject	= $this->post_title;
		
		$hitfigure = HitFigure::getInstance();
		$hitfigure->send_email($subject, $message, $to, "HitFigure@Hitfigure.com", $this->post_content);
	}
	
	public function dismiss() {
		// Set the alert_dismissed to true and save it
		$this->post_status = 'dismissed';
		$this->save();
	}
	
	public function to_json() {
		$vehicle = new Vehicle(array('id'=>$this->post_meta['vehicle_id']));
		$vehicle->fetch();
	
		$json = array(
			'alert_title'	=>$vehicle->get_full_name(),
			'view_alert' 	=>display_mustache_template('viewitemlink', array('url'=>'/admin/alert/'.$this->id, 'text'=>'View Alert'), False),
			'alert_type'	=>$this->alert_type() // Pretty version
		);
		return $json;
	}
	
	protected function alert_type() {
		return '';
	}
	
}

class AlertVehicle extends Alert {

	public function save() {
		if ( isset($this->attributes['vehicle_id']) ) {
			$vehicle_id 	= $this->attributes['vehicle_id'];
			$vehicle		= new Vehicle(array('id'=>$vehicle_id));
			$vehicle->fetch();
			$vehicle_name 	= $vehicle->post_meta['vehicle_year'] . ' ' . $vehicle->post_meta['vehicle_make'] . ' ' . $vehicle->post_meta['vehicle_model'];
			$this->attributes['post_title'] .= ' '.$vehicle_name;
		}	
		parent::save();
	}

	protected function render_content($vars = array()) {
		$vehicle_id = $this->attributes['post_meta']['vehicle_id'];
		$vehicle 	= new Vehicle(array('id'=>$vehicle_id));
		$vehicle->fetch();
		$vars 		= $vars + $vehicle->post_meta;
		return parent::render_content($vars);
	}

}



class AlertWon extends AlertVehicle  {
	public $defaults = array(
		'post_type' 		=> 'cpt-alert',
		'post_status'		=> 'publish',		
		'post_title'		=> 'Won!',		
		'post_meta'			=> array(
			'post_content'		=> null,
			'alert_type'		=> 'won',
			'alert_status'		=> 'green',
			'alert_dismissed'	=> false
		)
	);
	
	public $email_header_img = 'hdr_dealer-lead-won.jpg';
	
	protected function alert_type() {
		return 'Won';
	}

}



class AlertOutbid extends AlertVehicle  {
	public $defaults = array(
		'post_type' 		=> 'cpt-alert',
		'post_status'		=> 'publish',
		'post_title'		=> 'Outbid!',		
		'post_meta'			=> array(
			'post_content'		=> null,
			'alert_type'		=> 'outbid',
			'alert_status'		=> 'red',
			'alert_dismissed'	=> false
		)
	);
	
	public $email_header_img = 'hdr_dealer-outbid.jpg';
	
	protected function alert_type() {
		return 'Outbid';
	}	
	
}



class AlertNewbid extends AlertVehicle  {
	public $defaults = array(
		'post_type' 		=> 'cpt-alert',
		'post_status'		=> 'publish',		
		'post_title'		=> 'New bid!',		
		'post_meta'			=> array(
			'post_content'		=> null,
			'alert_type'		=> 'newbid',
			'alert_status'		=> 'blue',
			'alert_dismissed'	=> false
		)
	);
	
	public $email_header_img = 'hdr_dealer-bid-placed.jpg';
	
	protected function alert_type() {
		return 'New bid';
	}
		
}



class AlertNewlead extends AlertVehicle  {
	public $defaults = array(
		'post_type' 		=> 'cpt-alert',
		'post_status'		=> 'publish',		
		'post_title'		=> 'New lead!',		
		'post_meta'			=> array(
			'post_content'		=> null,
			'alert_type'		=> 'newlead',
			'alert_status'		=> 'blue',
			'alert_dismissed'	=> false
		)
	);
	
	public $email_header_img = 'hdr_dealer-new-lead.jpg';	

	protected function alert_type() {
		return 'New lead';
	}	
	
}


/* Bid Models */

/*
 * NOTE: $parent_id refers to the vehicle id that bid belongs to.
 */

class BidCollection extends PostCollection {
	public static $model = "hitfigure\models\Bid";
	
	public static $default_filter = array(
		'post_type' => 'cpt-bid',
        'posts_per_page' => -1,
        'order' => 'DESC',
        'paged' => '1'
    );
    
    public static function getHighestBid($parent_id) {
    	$bid = self::getTopBid($parent_id);
    	if (!$bid) {
    		// No Bids Yet...
    		return 0;
    	}
    	    	    	
    	$amount = self::convertAmount($bid->post_meta['amount']);
    	return $amount;
    }
    
    public static function getHighestBidder($parent_id) {
    	$bid = self::getTopBid($parent_id);
     	if (!$bid) {
    		// No Bids Yet...
    		return;
    	}
    	
    	$bidder_id = $bid->post_meta['user_id'];
    	$client = new Client(array('id'=>$bidder_id));
    	
    	return $client;
    }
    
    public static function getTopBid($parent_id) {
    	$bids = self::filter( array('post_parent'=>$parent_id, 'posts_per_page'=>1, 'post_status'=>'any') );
		
    	if (!count($bids)) {
    		return;
    	}
    	
    	$bid = $bids->current();
    	    	
    	return $bid;
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
    	$amount = 0+str_replace(",", "",(string)$amount);
		return $amount;
    }
    
    public static function mf($amount) {
    	// Convert to money format
    	$amount = self::convertAmount($amount);
    	return money_format('$%!i', $amount);
    }

	public static function yourHighestBid($parent_id) {
		$hitfigure = HitFigure::getInstance();
    	$user_id = $hitfigure->admin->user->id;

    	$bids = self::filter(array('meta_key'=>'user_id', 'meta_value'=>$user_id, 'post_parent'=>$parent_id, 'posts_per_page'=>1));
    	if (!count($bids)) {
    		// No Bids Yet...
    		return 0;
    	}
    	$bid = $bids->current();
    	$amount = self::convertAmount($bid->post_meta['amount']);
    	return $amount;
	}
    
    public static function bidStatus($parent_id) { 
   		$bid = self::getTopBid($parent_id);
   		if (!$bid) {
   			return -1;
   		}

		$hitfigure = HitFigure::getInstance();
     	$user = $hitfigure->admin->user;
    	$user_id = $user->id;    		
   		   		   		
   		if ( (int)$bid->post_meta['user_id'] == (int)$user_id ) {
   			return 1;
   		} else {
   			return 0;
   		}
    }
}



class Bid extends Post {
	public $defaults = array('post_type' => 'cpt-bid');
	
	public function get_vars() {
		return array(
			'bid_amount'=>BidCollection::mf($this->post_meta['amount'])
		);
	}
	
}



/* Client Model */

class ClientCollection extends UserCollection {
	public static $model = "hitfigure\models\Client";	
	public static $role = Null;

	public static $DEALER		= 'dealer';
	public static $MANUFACTURER	= 'manufacturer';	
	public static $SALESPERSON 	= 'salesperson';
	public static $ACCOUNTANT 	= 'accountant';
	
	public static function filter( $kwargs ) {
		$kwargs['role'] = static::$role;
		return parent::filter( $kwargs );		
	}
	
	
	
	/* NOT WORKING>>>>>>:(
	// We're doing some special stuff here, so...
	public static function filter( $kwargs ) {
		$kwargs['role'] = static::$role;
	
        $class = get_called_class();
        $users = get_users( $kwargs );
        $collection = new $class();
        foreach ($users as $u) {
        
        	// First we get the role...
        	$user = new WP_User( $u->ID );
        	if (!empty( $user->roles ) && is_array( $user->roles )) {
        		$type = $user->roles[0];
        	} else {
        		$type = null; // We don't know the type...
        	}
        	
        	$new_user = self::get_client((array)$u,$type);
            $collection->add( $new_user );
        }
        return $collection;	
	
	}
	*/
	
	
	public static function get_json_client_data($args = array()) {	
		$clients = static::filter($args);
		$clients_json = array();
		
		foreach ($clients as $client) {
			$clients_json[] = $client->to_json();
		}
		
		return $clients_json;
	}
	
	public static function get_client($args=array(),$type=null) {
		$client = null;
	
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
			default:
				$client = new Client($args);	
		}
		
		// Fetch if we have an id...
		if ($client && isset($args['id']) ) {
			$new_user->reload( $args );
		}
	
		return $client;
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
		'user_parent', 
		'alert_email_opt_newbid',
		'alert_email_opt_newlead',
		'alert_email_opt_won',
		'alert_email_opt_outbid'
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
    	// Check user prefs
    	if (in_array('accountant',$this->roles)) {
    		return false;
    	}
    	
    	$value = get_user_meta($this->id, 'alert_email_opt_'.$type, true);
    	if ($value != 'No') { // Not all users will have these set, so assume it's yes
    		return true;
    	}
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



/* Sales Person Model */

class SalesPersonCollection extends ClientCollection {
	public static $model 	= "hitfigure\models\SalesPerson";
	public static $role 	= "salesperson";	
}



class SalesPerson extends Client {
	public $role = 'salesperson';
	
	public function register() {
		$r = parent::register();
		if ( !is_wp_error($r) ) {
			// Set the role for this user...
			$u = new WP_User( $r );
			$u->set_role( 'salesperson' );	
		}
		return $r;
	}
}



/* Accountant Model */

class AccountantCollection extends ClientCollection {
	public static $model 	= "hitfigure\models\Accountant";
	public static $role 	= "accountant";	
}



class Accountant extends Client {
	public $role = 'accountant';
	
	public function register() {
		$r = parent::register();
		if ( !is_wp_error($r) ) {
			// Set the role for this user...
			$u = new WP_User( $r );
			$u->set_role( 'accountant' );	
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