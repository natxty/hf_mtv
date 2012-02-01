<?php

add_action( 'show_user_profile', 'additional_user_profile_fields' );
add_action( 'edit_user_profile', 'additional_user_profile_fields' );
 
function additional_user_profile_fields( $user ) {
	
	$business_name 		= esc_attr( get_the_author_meta( 'business_name', $user->ID ) );
	$phone				= esc_attr( get_the_author_meta( 'phone', $user->ID ) );
	$address			= esc_attr( get_the_author_meta( 'address', $user->ID ) );
	$address2			= esc_attr( get_the_author_meta( 'address2', $user->ID ) ); 
	$city				= esc_attr( get_the_author_meta( 'city', $user->ID ) );
	$state				= esc_attr( get_the_author_meta( 'state', $user->ID ) );
	$zipcode			= esc_attr( get_the_author_meta( 'zipcode', $user->ID ) );
	 
?>
	<h3>Extra profile information</h3>

	<table class="form-table">

		<tr>
			<th><label for="business_name">Business Name</label></th>

			<td>
				<input type="text" name="business_name" id="business_name" value="<?php echo $business_name; ?>" class="regular-text" /><br />
				<span class="description">Please enter your business name.</span>
			</td>
		</tr>

		<tr>
			<th><label for="phone">Phone</label></th>

			<td>
				<input type="text" name="phone" id="phone" value="<?php echo $phone; ?>" class="regular-text" /><br />
				<span class="description">Please enter your phone number.</span>
			</td>
		</tr>

		<tr>
			<th><label for="address">Address</label></th>

			<td>
				<input type="text" name="address" id="address" value="<?php echo $address; ?>" class="regular-text" /><br />
				<span class="description">Please enter your address.</span>
			</td>
		</tr>

		<tr>
			<th><label for="address2">Address 2</label></th>

			<td>
				<input type="text" name="address2" id="address2" value="<?php echo $address2; ?>" class="regular-text" /><br />
				<span class="description">Please enter additional address details.</span>
			</td>
		</tr>

		<tr>
			<th><label for="city">City</label></th>

			<td>
				<input type="text" name="city" id="city" value="<?php echo $city; ?>" class="regular-text" /><br />
				<span class="description">Please enter your city.</span>
			</td>
		</tr>


		<tr>
			<th><label for="state">State</label></th>

			<td>
				<input type="text" name="state" id="state" value="<?php echo $state; ?>" class="regular-text" /><br />
				<span class="description">Please enter your state.</span>
			</td>
		</tr>


		<tr>
			<th><label for="zipcode">Zip Code</label></th>

			<td>
				<input type="text" name="zipcode" id="zipcode" value="<?php echo $zipcode; ?>" class="regular-text" /><br />
				<span class="description">Please enter your zip code.</span>
			</td>
		</tr>


		<tr>
			<th><label for="manufacturer">Manufacturer</label></th>

			<td>
				<!-- Pull in Manufactuers Table here -->
				<span class="description">Please enter your manufacturer.</span>
			</td>
		</tr>

	</table>
<?php 
}
 
add_action( 'personal_options_update', 'save_additional_user_profile_fields' );
add_action( 'edit_user_profile_update', 'save_additional_user_profile_fields' );
 
function save_additional_user_profile_fields( $user_id ) {
	if ( !current_user_can( 'edit_user', $user_id ) )
		return false;
	
	$fieldnames = array(
		'business_name',
		'phone',
		'address',
		'address2',
		'city',
		'state',
		'zipcode'
	);
	
	foreach ($fieldnames as $name) {
		if ( isset($_POST[$name]) )
			update_user_meta( $user_id, $name, $_POST[$name] );
	}
}