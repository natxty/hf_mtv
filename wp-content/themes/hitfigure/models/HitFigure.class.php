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
		$this->app_actions = new AppActions();
		
		// Check if we're logged in...
		$this->instantiate_admin();
	}


	
	public function is_logged_in() {
		if (!$this->admin) {
			$url = "http://" . $_SERVER['HTTP_HOST']  . $_SERVER['REQUEST_URI'];
			$redirect = wp_login_url( $url );
			wp_redirect( $redirect, '302' );
			exit;
		}
	}
	


	private function instantiate_admin() {
		$user = UserCollection::get_current();
		$roles = $user->roles;
		
		if (!$roles) { // Probably just a vistor on the front end...
			return;
		}
				
		if (in_array('administrator', $roles) || in_array('hitfigure', $roles)) {
			$this->admin = new Admin();
		} elseif ( in_array('manufacturer', $roles) ) {
			$this->admin = new ManufacturerAdmin();
		} elseif ( in_array('dealer', $roles) ) {
			$this->admin = new DealerAdmin();
		} elseif ( in_array('salesperson', $roles) ) {
			$this->admin = new SalesPersonAdmin();
		} elseif ( in_array('accountant', $roles) ) {
			$this->admin = new AccountantAdmin();
		}
		
	}
	
	
	
	public function template_vars($vars = array()) {
		if ( $this->admin ) {
			$vars = $vars + $this->admin->get_admin_vars();
		}
		return $this->get_wp_data() + $this->get_header_vars() + $this->get_footer_vars() + $vars;
	}


	
	public function get_header_vars($vars = array()) {
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
		
		return $vars + array(
			'title'			=> $title,
			'stylesheet'	=> $stylesheet,
			'body_class'	=> $body_class,
			'wp_head'		=> $wp_head
		);
	}



	public function get_footer_vars($vars = array()) {
		ob_start();
		wp_footer();
		$wp_footer = ob_get_contents();
		ob_end_clean();
		
		return $vars + array('wp_footer'=>$wp_footer);	
	}
	
	
	
	public function get_wp_data($vars = array()) {
		$time = time();
		
		return $vars + array(
			'template_directory'=>get_bloginfo('template_directory'),
			'imgdir'=>get_bloginfo('template_directory') . '/images',
			'logouturl'=>wp_logout_url( get_bloginfo('wpurl') . '/wp-admin/' ),
			'siteurl'=>get_bloginfo('siteurl'),
			'date_long' => date("M d, Y", $time),
			'date_short' => date("m/d/Y", $time),
			'date_year' => date("Y", $time)
		);
	}
	
	
	
	public function new_vehicle($args) {
		$attachments = array();
		
		if ( isset($args['attachments']) ) {
			$attachments = $args['attachments'];
			unset($args['attachments']);
		}
		
		$post_meta = array();
		$post_meta_keys = array(
			'vehicle_vin',
			'vehicle_year',
			'vehicle_make',
			'vehicle_model',
			'vehicle_mileage',
			'vehicle_trim',
			'vehicle_transmission',
			'vehicle_exteriorcolor',
			'vehicle_interiorcolor',
			'vehicle_accidents',
			'vehicle_accidents_explain',
			'vehicle_tires',
			'vehicle_paintworkperformed',
			'vehicle_paintworkperformed_explain',
			'vehicle_paintworkneeded',
			'vehicle_paintworkneeded_explain',
			'vehicle_smoker',
			'vehicle_interiorcondition',
			'vehicle_overalldesc',
			'vehicle_titleowner',
			'vehicle_replacingifsold',
			'seller_firstname',
			'seller_lastname',
			'seller_email',
			'seller_phone',
			'seller_address1',
			'seller_address2',
			'seller_city',
			'seller_state',
			'seller_zipcode'			
		);
		
		foreach ($post_meta_keys as $post_meta_key) {
			if ( isset($args[$post_meta_key]) ) {
				$post_meta[$post_meta_key] = $args[$post_meta_key];
				unset($args[$post_meta_key]);
			}
		}
		
		if ($post_meta) {
			$args['post_meta'] = $post_meta;
			$args['post_title'] = $post_meta['vehicle_year'] . ' ' . $post_meta['vehicle_make'] . ' ' . $post_meta['vehicle_make'];
		}
		
		$vehicle = new Vehicle($args);
		$vehicle->save();
		
		$post_id = $vehicle->id;

		/* 
		 *	Go through our attachments, save them and attach them to our post...
		 */ 

		require_once(ABSPATH . 'wp-admin/includes/image.php');
		
		$vehicle_attachments = array();
		
		foreach ($attachments as $attachment) {
			
			$filename = $attachment;
			
			$wp_filetype = wp_check_filetype(basename($filename), null );
			
			$attachment = array(
			 'post_mime_type' => $wp_filetype['type'],
			 'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
			 'post_content' => '',
			 'post_status' => 'inherit'			
			 );
			
			$attach_id = wp_insert_attachment( $attachment, $filename, $post_id );
			$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
			wp_update_attachment_metadata( $attach_id, $attach_data );
			
			$vehicle_attachments[] = new VehicleAttachment(array('id'=>$attach_id));
		}
		
		$vehicle->add_attachments($vehicle_attachments);
		
		/*
		 * Save our closest dealers
		 */
		$results = $vehicle->save_dealers(); 
		
		// Do something with the results here... like send emails and crap... but for now just return them...
		
		$this->trigger_action('new_lead', array('vehicle'=>$vehicle,'dealers'=>$results));
		
		return $vehicle;
	}
	
	public function trigger_action($type, $args) {
		$this->app_actions->trigger_action($type, $args);
	}
	
	public function send_email($subject, $message, $to, $from="HitFigure@Hitfigure.com", $amputated = null) {
		$headers = "From: $from" . "\r\n" .
    	"Reply-To: $from" . "\r\n" .
   	 	'X-Mailer: PHP/' . phpversion();
		
		

   	 	
   	 	// For now, just override the $to so it always go to me, Colin!
   	 	$message = $to . "\r\n" . $message;
   	 	$to = 'dev@magneticcreative.com';
		
		//mail($to, $subject, $message, $headers);
		
		if($amputated) {
			//1. prep our non-html version(s)
			//1.a we need to get some of the useful info out before we crush it with strip_tags
			
			$plain_text = strip_tags($amputated);
			$notice_text = str_replace(array("\r\n", "\r", "\n", "\t"), '', $plain_text);
			
			
			//2. set up a nice boundary to separate our multi-parts 
			$semi_rand = md5(time());
			$mime_boundary = "==MULTIPART_BOUNDARY_$semi_rand";
			$mime_boundary_header = chr(34) . $mime_boundary . chr(34);
			
			//3. make some new headers:		
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: multipart/alternative;\n" . 
			"     boundary=" . $mime_boundary_header;
			
			//3. put it all together!
			
			$body = "$notice_text
	
--$mime_boundary
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 7bit

$plain_text

--$mime_boundary
Content-Type: text/html; charset=UTF-8
Content-Transfer-Encoding: 7bit

$message
--$mime_boundary--"; 

		} else {
			//no partial, stick to just email for the moment
			$headers  .= 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
			$body = $message;
			
		}
   	 	
   	 	mail($to, $subject, $body, $headers);
	}
	
}