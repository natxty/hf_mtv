<?php
namespace hitfigure\views;
use hitfigure\models\VehicleCollection,
	hitfigure\models\Vehicle,
	hitfigure\models\AlertCollection,
	hitfigure\models\BidCollection,
	hitfigure\models\Client,
	hitfigure\models\Dealer,
	hitfigure\models\Manufacturer,
	hitfigure\models\AttachmentCollection,
	hitfigure\models\ClientCollection,
	hitfigure\models\DealerCollection,
	hitfigure\models\ManufacturerCollection;
	
require_once( dirname(__FILE__) . '/user_form_views.php' );



function page( $request ) { // Generic Pages
	$page = get_page_by_path( $request['slug'] );
	
	if ( !$page) {
		echo '<h1>Page not found</h1>'; // 404
		return;
	}
	
	$vars = array('pages'=>get_page_queue($page)) + get_header_vars() + get_footer_vars();
	display_mustache_template('index', $vars);	
}



function homepage( $request ) {
	$vars = wp_data() + get_header_vars() + get_footer_vars();
	display_mustache_template('homepage', $vars);
}



function faqs( $request ) {
	$vars = wp_data() + get_header_vars() + get_footer_vars();
	display_mustache_template('faqs', $vars);
}



function dashboard( $request ) {
	//$v = new Vehicle(array('id'=>51));
	//$v->fetch();
	//$v->save();
	
	$admin = \hitfigure\models\AdminAppFactory();
	$admin->user()->save();
	print_r($admin->user());
}



function colin( $request ) {
	// Dashboard View
	echo ' Dashboard ';	
	$admin = \hitfigure\models\AdminAppFactory();
	//print_r($admin->user());
	
	echo 'form tests...';
	$f = new \FormHelper('test');
	$f->method = 'POST';
	$f->enctype = 'multipart/form-data';
	
	$r = new \RadioGroup('rgroup');
	$r->setProperties(array(
		'name'=>'rgroup',
		'required'=>True
	));
	
	$r->add_radio_button('rb', array('text'=>'1', 'value'=>'1'));
	$r->add_radio_button('rb', array('text'=>'2', 'value'=>'2'));
	$r->add_radio_button('rb', array('text'=>'3', 'value'=>'3'));
	$r->add_radio_button('rb', array('text'=>'4', 'value'=>'4'));
	$f->add($r);

	$t = new \TextArea('text-area');
	$t->setProperties(array(
		'name'=>'mytextarea',
		'required'=>True
	));
	$f->add($t);

	$i = new \FileInput('fileinput');
	$i->setProperties(array(
		'name'=>'fileinput',
		'required'=>True
	));
	$f->add($i);

	$b = new \Button('confirm');
	$b->setProperties(array(
		'name'	=> 'confirm',
		'text'	=> 'Confirm'
	));
	$f->add($b);
	


	if ( isset($_REQUEST['confirm']) ) { 
		$f->applyUserInput(True);
		
		if (!$f->validate()) {	
			// Unset anything private here, but our validation_func's should print errors etc.
		} else {
			// Do something on success...
		}
	}
	
	echo $f->render();
	
	$vehicle = new Vehicle(array('id'=>7));	
	$vehicle->fetch();
	//print_r($vehicle->get_attachments());
	print_r($vehicle->get_attachments());
}

function view_leads( $request ) {
	// View Leads
	// This might be simplified even further if 
	// the grid view is all ajax driven
	$vars = wp_data() + get_header_vars() + get_footer_vars();
	display_mustache_template('viewleads', $vars);
	
	
}

function ajax_lead_data( $request ) {
	// Return Ajax data to datatables
	// Eventually constrain by range...
	$json = VehicleCollection::get_json_vehicle_data();
 	//print_r($json);
 	echo json_encode(array('aaData'=>$json));
}

function bid_status_text($status) {
	if ($status === -1) {
		return 'No Bids';
	} elseif ($status === 0) {
		return 'Losing';
	} elseif ($status === 1) {
		return 'Winning!';
	}
}

function lead( $request ) {
	// View Single Lead
	$id = $request['id']; // Lead (vehicle) id
	$vehicle = new Vehicle(array('id'=>$id));	
	$vehicle->fetch();
	
	$min_amount 			= BidCollection::getMinAmount($vehicle->id);
	$highest_amount 		= BidCollection::getHighestBid($vehicle->id);
	$your_highest_amount 	= BidCollection::yourHighestBid($vehicle->id);
	$bid_status 			= bid_status_text(BidCollection::bidStatus($vehicle->id));
	
	$bidvars = array(
		'timeleft'				=>VehicleCollection::time_left($id),
		'min_amount'			=>money_format('%i', $min_amount),
		'highest_amount'		=>money_format('%i', $highest_amount),
		'your_highest_amount'	=>money_format('%i', $your_highest_amount),
		'bid_status'			=>$bid_status
	);
	
	$attachments = array('attachments'=>$vehicle->get_attachments());
	
	$vars = $vehicle->attributes + $vehicle->post_meta + $bidvars + get_header_vars() + get_footer_vars() + wp_data() + $attachments;
	display_mustache_template('lead', $vars);
}



function edit_client( $request ) {
	// Edit Client
	$id 	= $request['id']; // Client ID
	
	$adminapp = \hitfigure\models\AdminAppFactory();
	$form = $adminapp->edit_client( $id );
	
	$vars = array('form'=>$form) + get_header_vars() + get_footer_vars() + wp_data();
	
	if (isset($_GET['clientregistered'])) {
		$vars['clientregistered'] = True;
		$vars['clienttype'] = $_GET['clientregistered']; // -- This is the Type of client
	}
	
	display_mustache_template('editclient', $vars);
}



function new_client( $request ) {
	// New Client
	$type		= $request['type']; // manufacturer / dealer
	
	$adminapp = \hitfigure\models\AdminAppFactory();
	$form = $adminapp->register_client( $type );
	
	$vars = array('form'=>$form) + get_header_vars() + get_footer_vars() + wp_data();
	
	display_mustache_template('newclient', $vars);
}



function view_clients( $request ) {
	// View Clients
	$type	= $request['type']; // manufacturer / dealer
	
	$vars = array('type'=>$type) + get_header_vars() + get_footer_vars() + wp_data(); 
	display_mustache_template('viewclients', $vars);
}


function ajax_client_data( $request ) {
	// Return Ajax data to datatables
	$type	= $request['type']; // manufacturer / dealer
	
	if ($type == 'dealer') {
		$json = DealerCollection::get_json_client_data();
	} elseif ($type == 'manufacturer') {
		$json = ManufacturerCollection::get_json_client_data();
	}

 	echo json_encode(array('aaData'=>$json));
}

function view_alerts( $request ) {
	// View Alerts
	
	$posts = AlertCollection::filter();
	
?>
<pre>
	<?php print_r($posts); ?>
</pre>
<?php	
}


function bid( $request ) {
	// Bid
	$id 	= $request['id']; // Lead (vehicle) ID

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
	$vehicle = VehicleCollection::getVehicleByID($id);
	
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
				$f = place_bid_form($id, $min_amount);
				$bidvars['placing_bid'] = True;
				$bidvars['min_amount'] = $min_amount;
			}
			
			if ( isset($_REQUEST['submit'])	) {
				$f = place_bid_form($id, $min_amount);
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
					$f = confirm_bid_form($id);
				}
			} elseif ( isset($_REQUEST['confirm']) ) {
				$f = confirm_bid_form($id);
				$f->applyUserInput(True);
				// Bid confirmed... just display a confirmation message
				$yourbidamount = $f->amount->value;
				BidCollection::place($f->amount->value,$id, $vehicle->post_title);
				$bidvars['bid_placed'] = True;
				$bidvars['yourbidamount'] = $yourbidamount;
				$f = null;
			} elseif ( isset($_REQUEST['revise']) ) {
				// Back to the top...
				$bidvars['placing_bid'] = True;
				$bidvars['min_amount'] = $min_amount;
				$f = place_bid_form($id, $min_amount);
			}
		}
	}
	
	$form = $f ? $f->render() : '';
	$vars = array('form'=>$form) + $vehicle->attributes + $vehicle->post_meta + get_header_vars() + get_footer_vars() + wp_data() + $bidvars;
	display_mustache_template('bid', $vars);
}

function place_bid_form($id, $min_amount = 250) {
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


function confirm_bid_form($id) {
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



function email_seller( $request ) {
	// Email Seller
	$id 	= $request['id']; // Lead (vehicle) ID
	echo $id;
}