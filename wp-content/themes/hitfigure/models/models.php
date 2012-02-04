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
    	$geodata = $this->get_geodata();
       	$this->attributes['post_meta']['lat'] = $geodata['lat'];
    	$this->attributes['post_meta']['lng'] = $geodata['lng'];
    	
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
		$json['time_left'] 			= $this->time_left('%h');
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
	
    public function get_geodata() {
       	$fulladdress = 	$this->post_meta['seller_address1'].' ';
       	$fulladdress .=	$this->post_meta['seller_address2'].' ';
       	$fulladdress .=	$this->post_meta['seller_city'].' ';
       	$fulladdress .=	$this->post_meta['seller_zipcode'].' ';
       	$fulladdress .=	$this->post_meta['seller_state'];	
       	
 		$resp = wp_remote_get( "http://maps.googleapis.com/maps/api/geocode/json?address=".urlencode($fulladdress)."&sensor=false" );
    	
    	$lat = null;
    	$lng = null;
    	
    	
    	if ($resp['response']['message'] == 'OK') {
    	 	$data = json_decode($resp['body']);	
    		$lat = $data->results[0]->geometry->location->lat;
    		$lng = $data->results[0]->geometry->location->lng;
    	}
 
    	
    	return array('lat'=>$lat, 'lng'=>$lng);
    }	
	
}


/* Vechicle Image Attachment */

class VehicleAttachment extends Attachment {
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
}



class Alert extends Post {
	public $defaults = array('post_type' => 'cpt-alert');
}



/* Bid Models */

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
		'user_parent',
		'lat',
		'lng'
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
    
    
    
    public function parse( &$postdata ) { // Modify to also get our usermeta
        $ret 		=& parent::parse( $postdata );
        $id			= $ret['id'];
        
        foreach ($this->metakeys as $key) {
        	 $ret[$key] = get_user_meta($id, $key, True);
        }
        
        return $ret;
    } 
    
    public function save() {
    	$geodata = $this->get_geodata();
    	$this->set($geodata);
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
    
    public function get_geodata() {
    	$fulladdress =	$this->address.' ';
    	$fulladdress .=	$this->address2.' ';
    	$fulladdress .=	$this->city.' ';
    	$fulladdress .=	$this->zipcode.' ';
    	$fulladdress .=	$this->state;
    	
 		$resp = wp_remote_get( "http://maps.googleapis.com/maps/api/geocode/json?address=".urlencode($fulladdress)."&sensor=false" );
    	
    	$lat = null;
    	$lng = null;
    	
    	
    	if ($resp['response']['message'] == 'OK') {
    	 	$data = json_decode($resp['body']);	
    		$lat = $data->results[0]->geometry->location->lat;
    		$lng = $data->results[0]->geometry->location->lng;
    	}
    	
    	return array('lat'=>$lat, 'lng'=>$lng);
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