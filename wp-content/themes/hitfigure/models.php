<?php

namespace hitfigure\models;
use WPException, WP_Error, WP_User, DateTime;

// Bring over the WP models from MTV... can't access these in our model unless we do it like this, boo!
require_once( dirname(__FILE__) . '/models/mtv_wp_models.php' );

require_once( dirname(__FILE__) . '/models/form_models.php' );

function AdminAppFactory() {
	// This will eventually choose from the different types of AdminApp's based on role
	// For now just return the Super AdminApp!@!@!
	return AdminApp::getInstance();
}

/*	Admin App

	The meat and potatos
	A singleton class that we use to ride a windshield through the universe
*/

class AdminApp {
	public static $instance 	= null;
	public $attributes			= array();

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

	private function init(){
		/* pass */
	}
	
	public function user() {
		return ClientCollection::get_current();
	}
	
	/* Roles */
	
	public function roles() {
		return $this->user()->roles;
	}
	
	public function is_dealer() {
		if ( in_array('dealer', $this->roles()) ) {
			return True;
		} 
	}
	
	public function is_manufacturer() {
		if ( in_array('manufacturer', $this->roles()) ) {
			return True;
		} 	
	}
	
	public function is_hitfigure() {
		if ( in_array('hitfigure', $this->roles()) || in_array('Administrator', $this->roles()) ) {
			return True;
		} 	
	}
	
	/* Capabilities */
	
	public function capabilities() {
		return $this->user()->capabilities;
	}
	
	public function can_manage_manufacturers() {
		if ( in_array('manage_manufacturers', $this->capabilities()) ) {
			return True;
		}
	}
	
	public function can_manage_dealers() {
		if ( in_array('manage_dealers', $this->capabilities()) ) {
			return True;
		}	
	}

	public function can_bid() {
		if ( in_array('bid', $this->capabilities()) ) {
			return True;
		}	
	}
	
	public function register_client( $type ) {
		return $this->update_client($type);
	}
	
	public function edit_client( $id ) {
		$type	= Null;
		
		$client = new Client(array('id'=>$id));
		$client->fetch();
				
		if (!$this->is_hitfigure()) {
			// Do some additional checks
		
			if ( in_array('dealer',$client->roles) ) {
				$type = 'dealer';
			} elseif ( in_array('manufacturer',$client->roles) ) {
				$type = 'manufacturer';
			}			
			
			if ( !$type ) {
				// Not the right type of user...
				return -1;
			}
			
			if ( (int)$client->user_parent != $this->user()->id) {
				// Can't edit this 
				return -1; 
			}
		}
		
		return $this->update_client( $type, $client );		
	}
	
	private function update_client( $type, $client=Null ) {
		// Create or Update a Client
		
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
		
		$i = new \Input('business_name');
		$i->setProperties(array(
			'name' =>'business_name',
			'text' =>'Dealer Name',
			'required'=>True
		));
		$f->add($i);
		
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
	
		/* This needs to be a drop down...
		$i = new \Input('state');
		$i->setProperties(array(
			'name' =>'state',
			'text' =>'State',
			'required'=>True
		));
		$f->add($i);
		*/
		
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
					'user_parent'		=>$this->user()->id
				);
							
				switch($type) {
					case 'manufacturer':
						$client = new Manufacturer($args);
						break;
					case 'dealer':
						$client = new Dealer($args);
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
		}	
	
		return $f->render();
	}
	
	
	/* Stolen from the MTV Model class */
	
    public function __toString() {
        return get_called_class();
    }

    public function __get($name) {
        return $this->attributes[$name];
    }

    public function __set( $name, $val ) {
        $this->set( array( $name => $val ) );
    }

    public function __unset( $name ) {
        $this->clear( $name );
    }

    public function __isset( $name ) {
        return isset($this->attributes[$name]);
    }	
    
    public function clear() {
        foreach ( func_get_args() as $arg ) {
            unset( $this->attributes[$arg] );
        }
    }

    public function set( $args, $fetching=false ) {
        $this->attributes = array_merge( $this->attributes, (array) $args );
    }    
	
}



/* Vehicle Models */

class VehicleCollection extends PostCollection {
	public static $model = "hitfigure\models\Vehicle";
	
	public static $default_filter = array(
		'post_type' => 'cpt-vehicle',
        'posts_per_page' => 10,
        'order' => 'DESC',
        'paged' => '1'
    );
    
    public static function getVehicleByID( $id ) {
    	// Should use ::get($args) here, but gives an error...
		$vehicles = self::filter(array('p'=>$id, 'posts_per_page'=>1));
		if (!count($vehicles)) {
			return Null;
		}
		return $vehicles->current();    
	}
	
	public static function is_active( $id ) {
		add_filter( 'posts_where', 'hitfigure\models\filter_where_48hours');
		$vehicle = self::getVehicleByID( $id ); 
		remove_filter( 'posts_where', 'hitfigure\models\filter_where_48hours' );
				
		if ($vehicle) {
			return $vehicle;
		}
	}
	
	public static function activeLeads($args = array()) {
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
		// Constrain by role, expired, distance
		$args = array(
			// nothing here yet...
		);
		
		$vehicles_json = array();
		
		
		$admin = AdminAppFactory();
		if (!$admin->is_hitfigure()) { // add additional where statement to limit to 50 miles
			add_filter( 'posts_where', 'hitfigure\models\filter_where_lt_50miles');
		}
		
		$vehicles = self::activeLeads($args);
		
		if (!$admin->is_hitfigure()) { // remove additional where statement to limit to 50 miles
			remove_filter( 'posts_where', 'hitfigure\models\filter_where_lt_50miles');
		}
		
		foreach ($vehicles as $vehicle) {
			$vehicles_json[] = $vehicle->to_json();
		}
		
		return $vehicles_json;
	}
	
}


// Our Filter For Date
function filter_where_48hours( $where = '' ) {
	$where .= " AND post_date > '" . date('Y-m-d h:i:s', strtotime('-2 days')) . "'";
	return $where;
}

function filter_where_lt_50miles( $where = '' ) {
	global $wpdb;
	
	$admin = AdminAppFactory()->user();
	$_lat_filter = $admin->lat;
	$_lng_filter = $admin->lng;
		
	$where .= "AND
(
SELECT
	TRUE
FROM
	(
		SELECT 
			pm.meta_value AS latitude,
			p.ID AS ID
		FROM 
			wp_posts AS p
		INNER JOIN
			wp_postmeta AS pm
		ON
			p.ID = pm.post_id
		WHERE
			pm.meta_key = 'lat'
	) lat
INNER JOIN
	(
		SELECT 
			pm.meta_value AS longitude,
			p.ID AS ID
		FROM 
			wp_posts AS p
		INNER JOIN
			wp_postmeta AS pm
		ON
			p.ID = pm.post_id
		WHERE
			pm.meta_key = 'lng'
	) lng
ON
	lng.ID = lat.ID
WHERE
	((ACOS(SIN( $_lat_filter * PI() / 180) * SIN(lat.latitude * PI() / 180) + COS( $_lat_filter * PI() / 180) * COS(lat.latitude * PI() / 180) * COS(($_lng_filter - lng.longitude) * PI() / 180)) * 180 / PI()) * 60 * 1.1515 * 0.621371192) < 50
	AND
	lat.ID = wp_posts.ID
) IS NOT NULL";

	return $where;
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
    	$user = AdminAppFactory()->user();
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
    	$user = AdminAppFactory()->user();
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

     	$user = AdminAppFactory()->user();
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
	
	public static function get_json_client_data() {	
		$constraints = array(); 
		
		$adminapp = AdminAppFactory();
		
		if (!$adminapp->is_hitfigure()) {
			// apply constraints
			$contraints = array('meta_key'=>'user_parent', 'meta_value'=>$adminapp->user()->id);
		}
		
		$clients = static::filter($contraints);
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
        // Register
        if ( empty($this->id) ) {
            // Validate username and email
            
        // Update
        } else {
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
