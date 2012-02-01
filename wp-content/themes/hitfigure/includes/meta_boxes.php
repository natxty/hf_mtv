<?php

/* CPT-VEHICLE INFORMATION */
add_action( 'add_meta_boxes', function() {
    add_meta_box(
        'vehicle_info',
        'Vehicle Information', 
        'vehicle_info',
        'cpt-vehicle',
        'normal'
    );	
});

function vehicle_info( $post ) {
	// Use nonce for verification
	wp_nonce_field( plugin_basename( __FILE__ ), 'vehicle_info_noncename' );
	
	// Get Post Meta
	$vin							= get_post_meta($post->ID, 'vehicle_vin', true);
	$year							= get_post_meta($post->ID, 'vehicle_year', true);
	$make							= get_post_meta($post->ID, 'vehicle_make', true);
	$model							= get_post_meta($post->ID, 'vehicle_model', true);
	$mileage						= get_post_meta($post->ID, 'vehicle_mileage', true);
	$trim							= get_post_meta($post->ID, 'vehicle_trim', true);
	$transmission					= get_post_meta($post->ID, 'vehicle_transmission', true);
	$exteriorcolor					= get_post_meta($post->ID, 'vehicle_exteriorcolor', true);
	$interiorcolor					= get_post_meta($post->ID, 'vehicle_interiorcolor', true);
	
	$accidents						= get_post_meta($post->ID, 'vehicle_accidents', true);
	$accidents_explain				= get_post_meta($post->ID, 'vehicle_accidents_explain', true);
	
	$tires							= get_post_meta($post->ID, 'vehicle_tires', true);
	
	$paintworkperformed				= get_post_meta($post->ID, 'vehicle_paintworkperformed', true);
	$paintworkperformed_explain		= get_post_meta($post->ID, 'vehicle_paintworkperformed_explain', true);
	
	$paintworkneeded				= get_post_meta($post->ID, 'vehicle_paintworkneeded', true);
	$paintworkneeded_explain		= get_post_meta($post->ID, 'vehicle_paintworkneeded_explain', true);
	
	
	$smoker							= get_post_meta($post->ID, 'vehicle_smoker', true);
	$interiorcondition				= get_post_meta($post->ID, 'vehicle_interiorcondition', true);
	$overalldesc					= get_post_meta($post->ID, 'vehicle_overalldesc', true);
	$titleowner						= get_post_meta($post->ID, 'vehicle_titleowner', true);
	$replacingifsold				= get_post_meta($post->ID, 'vehicle_replacingifsold', true);
	
	
	// The actual fields for data entry
?>

		<p>
			<strong>VIN</strong>
		</p>
		<label class="screen-reader-text" for="vehicle_vin">
			Vehicle VIN
		</label>
		<input type="text" id="vehicle_vin" name="vehicle_vin" value="<?php echo $vin; ?>" size="38" />
	

		<p>
			<strong>Year</strong>
		</p>
		<label class="screen-reader-text" for="vehicle_year">
			Vehicle Year
		</label>
		<input type="text" id="vehicle_year" name="vehicle_year" value="<?php echo $year; ?>" size="38" />
	

		<p>
			<strong>Make</strong>
		</p>
		<label class="screen-reader-text" for="vehicle_make">
			Vehicle Make
		</label>
		<input type="text" id="vehicle_make" name="vehicle_make" value="<?php echo $make; ?>" size="38" />
	

		<p>
			<strong>Model</strong>
		</p>
		<label class="screen-reader-text" for="vehicle_model">
			Vehicle Model
		</label>
		<input type="text" id="vehicle_model" name="vehicle_model" value="<?php echo $model; ?>" size="38" />
	

		<p>
			<strong>Trim</strong>
		</p>
		<label class="screen-reader-text" for="vehicle_trim">
			Vehicle Trim
		</label>
		<input type="text" id="vehicle_trim" name="vehicle_trim" value="<?php echo $trim; ?>" size="38" />
	

		<p>
			<strong>Transmission</strong>
		</p>
		<label class="screen-reader-text" for="vehicle_transmission">
			Vehicle Transmission
		</label>
		<input type="text" id="vehicle_transmission" name="vehicle_transmission" value="<?php echo $transmission; ?>" size="38" />
	

		<p>
			<strong>Mileage</strong>
		</p>
		<label class="screen-reader-text" for="vehicle_mileage">
			Vehicle Mileage
		</label>
		<input type="text" id="vehicle_mileage" name="vehicle_mileage" value="<?php echo $mileage; ?>" size="38" />
	

		<p>
			<strong>Exterior Color</strong>
		</p>
		<label class="screen-reader-text" for="vehicle_exteriorcolor">
			Vehicle Exterior Color
		</label>
		<input type="text" id="vehicle_exteriorcolor" name="vehicle_exteriorcolor" value="<?php echo $exteriorcolor; ?>" size="38" />
	

		<p>
			<strong>Interior Color</strong>
		</p>
		<label class="screen-reader-text" for="vehicle_interiorcolor">
			Vehicle Interior Color
		</label>
		<input type="text" id="vehicle_interiorcolor" name="vehicle_interiorcolor" value="<?php echo $interiorcolor; ?>" size="38" />
	

		<p>
			<strong>Any Known Accidents?</strong>
		</p>
		<label class="screen-reader-text" for="vehicle_accidents">
			Vehicle Any Known Accidents?
		</label>
		<input type="text" id="vehicle_accidents" name="vehicle_accidents" value="<?php echo $accidents; ?>" size="38" />
	

		<p>
			<strong>Accidents - Please Explain</strong>
		</p>
		<label class="screen-reader-text" for="vehicle_accidents_explain">
			Vehicle Accidents - Please Explain
		</label>
		<input type="text" id="vehicle_accidents_explain" name="vehicle_accidents_explain" value="<?php echo $accidents_explain; ?>" size="38" />
	

		<p>
			<strong>Tires Better or Worse than 60%</strong>
		</p>
		<label class="screen-reader-text" for="vehicle_tires">
			Vehicle Tires Better or Worse than 60%
		</label>
		<input type="text" id="vehicle_tires" name="vehicle_tires" value="<?php echo $tires; ?>" size="38" />
	

		<p>
			<strong>Any Paintwork Performed?</strong>
		</p>
		<label class="screen-reader-text" for="vehicle_paintworkperformed">
			Vehicle Any Paintwork Performed?
		</label>
		<input type="text" id="vehicle_paintworkperformed" name="vehicle_paintworkperformed" value="<?php echo $paintworkperformed; ?>" size="38" />
	

		<p>
			<strong>Paintwork Performed - Please Explain</strong>
		</p>
		<label class="screen-reader-text" for="vehicle_paintworkperformed_explain">
			Vehicle Paintwork Performed - Please Explain
		</label>
		<input type="text" id="vehicle_paintworkperformed_explain" name="vehicle_paintworkperformed_explain" value="<?php echo $paintworkperformed_explain; ?>" size="38" />
	

		<p>
			<strong>Any Paintwork Needed?</strong>
		</p>
		<label class="screen-reader-text" for="vehicle_paintworkneeded">
			Vehicle Any Paintwork Needed?
		</label>
		<input type="text" id="vehicle_paintworkneeded" name="vehicle_paintworkneeded" value="<?php echo $paintworkneeded; ?>" size="38" />
	

		<p>
			<strong>Paintwork Needed - Please Explain</strong>
		</p>
		<label class="screen-reader-text" for="vehicle_paintworkneeded_explain">
			Vehicle Paintwork Needed - Please Explain
		</label>
		<input type="text" id="vehicle_paintworkneeded_explain" name="vehicle_paintworkneeded_explain" value="<?php echo $paintworkneeded_explain; ?>" size="38" />
	

		<p>
			<strong>Smoker</strong>
		</p>
		<label class="screen-reader-text" for="vehicle_smoker">
			Vehicle Smoker
		</label>
		<input type="text" id="vehicle_smoker" name="vehicle_smoker" value="<?php echo $smoker; ?>" size="38" />
	

		<p>
			<strong>Interior Condition</strong>
		</p>
		<label class="screen-reader-text" for="vehicle_interiorcondition">
			Vehicle Interior Condition
		</label>
		<input type="text" id="vehicle_interiorcondition" name="vehicle_interiorcondition" value="<?php echo $interiorcondition; ?>" size="38" />
	

		<p>
			<strong>Description of Overall Condition</strong>
		</p>
		<label class="screen-reader-text" for="vehicle_overalldesc">
			Vehicle Description of Overall Condition
		</label>
		<input type="text" id="vehicle_overalldesc" name="vehicle_overalldesc" value="<?php echo $overalldesc; ?>" size="38" />
	

		<p>
			<strong>Who Owns the Title?</strong>
		</p>
		<label class="screen-reader-text" for="vehicle_titleowner">
			Vehicle Who Owns the Title?
		</label>
		<input type="text" id="vehicle_titleowner" name="vehicle_titleowner" value="<?php echo $titleowner; ?>" size="38" />
	

		<p>
			<strong>Are you Replacing this Vehicle if Sold?</strong>
		</p>
		<label class="screen-reader-text" for="vehicle_replacingifsold">
			Vehicle Are you Replacing this Vehicle if Sold?
		</label>
		<input type="text" id="vehicle_replacingifsold" name="vehicle_replacingifsold" value="<?php echo $replacingifsold; ?>" size="38" />

<?php	
}

add_action( 'save_post', function($post_id) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
	    return;
	
	if ( !wp_verify_nonce( $_POST['vehicle_info_noncename'], plugin_basename( __FILE__ ) ) )
	    return;
	
	if ( !current_user_can( 'edit_page', $post_id ) )
	        return;


		if ( isset($_POST['vehicle_vin']) ) {
			$vin = $_POST['vehicle_vin'];
			update_post_meta($post_id,'vehicle_vin',$vin);
		}
		

		if ( isset($_POST['vehicle_year']) ) {
			$year = $_POST['vehicle_year'];
			update_post_meta($post_id,'vehicle_year',$year);
		}
		

		if ( isset($_POST['vehicle_make']) ) {
			$make = $_POST['vehicle_make'];
			update_post_meta($post_id,'vehicle_make',$make);
		}
		

		if ( isset($_POST['vehicle_model']) ) {
			$model = $_POST['vehicle_model'];
			update_post_meta($post_id,'vehicle_model',$model);
		}
		

		if ( isset($_POST['vehicle_trim']) ) {
			$trim = $_POST['vehicle_trim'];
			update_post_meta($post_id,'vehicle_trim',$trim);
		}
		

		if ( isset($_POST['vehicle_transmission']) ) {
			$transmission = $_POST['vehicle_transmission'];
			update_post_meta($post_id,'vehicle_transmission',$transmission);
		}
		

		if ( isset($_POST['vehicle_mileage']) ) {
			$mileage = $_POST['vehicle_mileage'];
			update_post_meta($post_id,'vehicle_mileage',$mileage);
		}
		

		if ( isset($_POST['vehicle_exteriorcolor']) ) {
			$exteriorcolor = $_POST['vehicle_exteriorcolor'];
			update_post_meta($post_id,'vehicle_exteriorcolor',$exteriorcolor);
		}
		

		if ( isset($_POST['vehicle_interiorcolor']) ) {
			$interiorcolor = $_POST['vehicle_interiorcolor'];
			update_post_meta($post_id,'vehicle_interiorcolor',$interiorcolor);
		}
		

		if ( isset($_POST['vehicle_accidents']) ) {
			$accidents = $_POST['vehicle_accidents'];
			update_post_meta($post_id,'vehicle_accidents',$accidents);
		}
		

		if ( isset($_POST['vehicle_accidents_explain']) ) {
			$accidents_explain = $_POST['vehicle_accidents_explain'];
			update_post_meta($post_id,'vehicle_accidents_explain',$accidents_explain);
		}
		

		if ( isset($_POST['vehicle_tires']) ) {
			$tires = $_POST['vehicle_tires'];
			update_post_meta($post_id,'vehicle_tires',$tires);
		}
		

		if ( isset($_POST['vehicle_paintworkperformed']) ) {
			$paintworkperformed = $_POST['vehicle_paintworkperformed'];
			update_post_meta($post_id,'vehicle_paintworkperformed',$paintworkperformed);
		}
		

		if ( isset($_POST['vehicle_paintworkperformed_explain']) ) {
			$paintworkperformed_explain = $_POST['vehicle_paintworkperformed_explain'];
			update_post_meta($post_id,'vehicle_paintworkperformed_explain',$paintworkperformed_explain);
		}
		

		if ( isset($_POST['vehicle_paintworkneeded']) ) {
			$paintworkneeded = $_POST['vehicle_paintworkneeded'];
			update_post_meta($post_id,'vehicle_paintworkneeded',$paintworkneeded);
		}
		

		if ( isset($_POST['vehicle_paintworkneeded_explain']) ) {
			$paintworkneeded_explain = $_POST['vehicle_paintworkneeded_explain'];
			update_post_meta($post_id,'vehicle_paintworkneeded_explain',$paintworkneeded_explain);
		}
		

		if ( isset($_POST['vehicle_smoker']) ) {
			$smoker = $_POST['vehicle_smoker'];
			update_post_meta($post_id,'vehicle_smoker',$smoker);
		}
		

		if ( isset($_POST['vehicle_interiorcondition']) ) {
			$interiorcondition = $_POST['vehicle_interiorcondition'];
			update_post_meta($post_id,'vehicle_interiorcondition',$interiorcondition);
		}
		

		if ( isset($_POST['vehicle_overalldesc']) ) {
			$overalldesc = $_POST['vehicle_overalldesc'];
			update_post_meta($post_id,'vehicle_overalldesc',$overalldesc);
		}
		

		if ( isset($_POST['vehicle_titleowner']) ) {
			$titleowner = $_POST['vehicle_titleowner'];
			update_post_meta($post_id,'vehicle_titleowner',$titleowner);
		}
		

		if ( isset($_POST['vehicle_replacingifsold']) ) {
			$replacingifsold = $_POST['vehicle_replacingifsold'];
			update_post_meta($post_id,'vehicle_replacingifsold',$replacingifsold);
		}
    	
});
/* END: CPT-VEHICLE INFORMATION */

/* CPT-VEHICLE SELLER */
add_action( 'add_meta_boxes', function() {
    add_meta_box(
        'vehicle_seller',
        'Vehicle Seller', 
        'vehicle_seller',
        'cpt-vehicle',
        'normal'
    );	
});

function vehicle_seller( $post ) {
	// Use nonce for verification
	wp_nonce_field( plugin_basename( __FILE__ ), 'vehicle_seller_noncename' );
	
	// Get Post Meta

	$firstname	= get_post_meta($post->ID, 'seller_firstname', true);
	$lastname	= get_post_meta($post->ID, 'seller_lastname', true);
	$email		= get_post_meta($post->ID, 'seller_email', true);
	$phone		= get_post_meta($post->ID, 'seller_phone', true);
	$address1	= get_post_meta($post->ID, 'seller_address1', true);
	$address2	= get_post_meta($post->ID, 'seller_address2', true);
	$city		= get_post_meta($post->ID, 'seller_city', true);
	$state		= get_post_meta($post->ID, 'seller_state', true);
	$zipcode	= get_post_meta($post->ID, 'seller_zipcode', true);	
	
	// The actual fields for data entry
?>


			<p>
				<strong>First Name</strong>
			</p>
			<label class="screen-reader-text" for="seller_firstname">
				Vehicle First Name
			</label>
			<input type="text" id="seller_firstname" name="seller_firstname" value="<?php echo $firstname; ?>" size="38" />
		

			<p>
				<strong>Last Name</strong>
			</p>
			<label class="screen-reader-text" for="seller_lastname">
				Vehicle Last Name
			</label>
			<input type="text" id="seller_lastname" name="seller_lastname" value="<?php echo $lastname; ?>" size="38" />
		

			<p>
				<strong>Email Address</strong>
			</p>
			<label class="screen-reader-text" for="seller_email">
				Vehicle Email Address
			</label>
			<input type="text" id="seller_email" name="seller_email" value="<?php echo $email; ?>" size="38" />
		

			<p>
				<strong>Phone</strong>
			</p>
			<label class="screen-reader-text" for="seller_phone">
				Vehicle Phone
			</label>
			<input type="text" id="seller_phone" name="seller_phone" value="<?php echo $phone; ?>" size="38" />
		

			<p>
				<strong>Address 1</strong>
			</p>
			<label class="screen-reader-text" for="seller_address1">
				Vehicle Address 1
			</label>
			<input type="text" id="seller_address1" name="seller_address1" value="<?php echo $address1; ?>" size="38" />
		

			<p>
				<strong>Address 2</strong>
			</p>
			<label class="screen-reader-text" for="seller_address2">
				Vehicle Address 2
			</label>
			<input type="text" id="seller_address2" name="seller_address2" value="<?php echo $address2; ?>" size="38" />
		

			<p>
				<strong>City</strong>
			</p>
			<label class="screen-reader-text" for="seller_city">
				Vehicle City
			</label>
			<input type="text" id="seller_city" name="seller_city" value="<?php echo $city; ?>" size="38" />
		

			<p>
				<strong>State</strong>
			</p>
			<label class="screen-reader-text" for="seller_state">
				Vehicle State
			</label>
			<input type="text" id="seller_state" name="seller_state" value="<?php echo $state; ?>" size="38" />
		

			<p>
				<strong>Zip Code</strong>
			</p>
			<label class="screen-reader-text" for="seller_zipcode">
				Vehicle Zip Code
			</label>
			<input type="text" id="seller_zipcode" name="seller_zipcode" value="<?php echo $zipcode; ?>" size="38" />

<?php	
}

add_action( 'save_post', function($post_id) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
	    return;
	
	if ( !wp_verify_nonce( $_POST['vehicle_seller_noncename'], plugin_basename( __FILE__ ) ) )
	    return;
	
	if ( !current_user_can( 'edit_page', $post_id ) )
	        return;


		if ( isset($_POST['seller_firstname']) ) {
			$firstname = $_POST['seller_firstname'];
			update_post_meta($post_id,'seller_firstname',$firstname);
		}
		

		if ( isset($_POST['seller_lastname']) ) {
			$lastname = $_POST['seller_lastname'];
			update_post_meta($post_id,'seller_lastname',$lastname);
		}
		

		if ( isset($_POST['seller_email']) ) {
			$email = $_POST['seller_email'];
			update_post_meta($post_id,'seller_email',$email);
		}
		

		if ( isset($_POST['seller_phone']) ) {
			$phone = $_POST['seller_phone'];
			update_post_meta($post_id,'seller_phone',$phone);
		}
		

		if ( isset($_POST['seller_address1']) ) {
			$address1 = $_POST['seller_address1'];
			update_post_meta($post_id,'seller_address1',$address1);
		}
		

		if ( isset($_POST['seller_address2']) ) {
			$address2 = $_POST['seller_address2'];
			update_post_meta($post_id,'seller_address2',$address2);
		}
		

		if ( isset($_POST['seller_city']) ) {
			$city = $_POST['seller_city'];
			update_post_meta($post_id,'seller_city',$city);
		}
		

		if ( isset($_POST['seller_state']) ) {
			$state = $_POST['seller_state'];
			update_post_meta($post_id,'seller_state',$state);
		}
		

		if ( isset($_POST['seller_zipcode']) ) {
			$zipcode = $_POST['seller_zipcode'];
			update_post_meta($post_id,'seller_zipcode',$zipcode);
		}
    	
});
/* END: CPT-VEHICLE SELLER */


/* CPT-BID INFO */
add_action( 'add_meta_boxes', function() {
    add_meta_box(
        'bid_info',
        'Details', 
        'bid_info',
        'cpt-bid',
        'normal'
    );	
});

function bid_info( $post ) {
	// Use nonce for verification
	wp_nonce_field( plugin_basename( __FILE__ ), 'bid_info_noncename' );
	
	// Get Post Meta

	$amount	= get_post_meta($post->ID, 'amount', true);
	$user_login	= get_post_meta($post->ID, 'user_login', true);
	$lead_name	= get_post_meta($post->ID, 'lead_name', true);
	
	// The actual fields for data entry
?>

			<p>
				<strong>Amount</strong>
			</p>
			<label class="screen-reader-text" for="amount">
				Vehicle Amount
			</label>
			<input type="text" id="amount" name="amount" value="<?php echo $amount; ?>" size="38" />
		

			<p>
				<strong>Username</strong>
			</p>
			<label class="screen-reader-text" for="user_login">
				Vehicle Username
			</label>
			<input type="text" id="user_login" name="user_login" value="<?php echo $user_login; ?>" size="38" />
		

			<p>
				<strong>Lead</strong>
			</p>
			<label class="screen-reader-text" for="lead_name">
				Vehicle Lead
			</label>
			<input type="text" id="lead_name" name="lead_name" value="<?php echo $lead_name; ?>" size="38" />

<?php	
}

add_action( 'save_post', function($post_id) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
	    return;
	
	if ( !wp_verify_nonce( $_POST['bid_info_noncename'], plugin_basename( __FILE__ ) ) )
	    return;
	
	if ( !current_user_can( 'edit_page', $post_id ) )
	        return;

		if ( isset($_POST['amount']) ) {
			$amount = $_POST['amount'];
			update_post_meta($post_id,'amount',$amount);
		}
		

		if ( isset($_POST['user_login']) ) {
			$user_login = $_POST['user_login'];
			update_post_meta($post_id,'user_login',$user_login);
		}
		

		if ( isset($_POST['lead_name']) ) {
			$lead_name = $_POST['lead_name'];
			update_post_meta($post_id,'lead_name',$lead_name);
		}

    	
});
/* END: CPT-BID INFO */