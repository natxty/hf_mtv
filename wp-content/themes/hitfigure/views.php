<?php
namespace hitfigure\views;
use hitfigure\models\HitFigure;
	
require_once( dirname(__FILE__) . '/user_form_views.php' );



function page( $request ) { // Generic Pages
	$page = get_page_by_path( $request['slug'] );
	
	if ( !$page) {
		display_mustache_template('404',array());
		return;
	}
	
	$hitfigure = HitFigure::getInstance();	
	$vars = $hitfigure->template_vars(array('pages'=>get_page_queue($page)));
	display_mustache_template('index', $vars);	
}



function homepage( $request ) {
	$hitfigure = HitFigure::getInstance();	
	$vars = $hitfigure->template_vars();
	display_mustache_template('homepage', $vars);
}



function faqs( $request ) {
	$hitfigure = HitFigure::getInstance();	
	$vars = $hitfigure->template_vars();
	display_mustache_template('faqs', $vars);
}



function dashboard( $request ) {

}



function view_leads( $request ) {
	// View Leads
	$hitfigure = HitFigure::getInstance();	
	$vars = $hitfigure->template_vars();
	display_mustache_template('viewleads', $vars);
	
	
}

function ajax_lead_data( $request ) {
	// Return Ajax data to datatables
	$hitfigure = HitFigure::getInstance();
	$leaddata = $hitfigure->admin->get_lead_data();	
 	echo json_encode($leaddata);
}



function lead( $request ) {
	// View Single Lead
	$id = $request['id']; // Lead (vehicle) id
	
	$hitfigure = HitFigure::getInstance();
	
	$adminvars = $hitfigure->admin->view_lead($id);	
	
	$vars = $hitfigure->template_vars($adminvars);
	display_mustache_template('lead', $vars);
}



function edit_client( $request ) {
	// Edit Client
	$id 	= $request['id']; // Client ID
	
	$hitfigure = HitFigure::getInstance();
	
	$adminvars = $hitfigure->admin->edit_client($id);	
	
	$vars = $hitfigure->template_vars($adminvars);  
	display_mustache_template('editclient', $vars);
}



function new_client( $request ) {
	// New Client
	$type		= $request['type']; // manufacturer / dealer
	
	$hitfigure = HitFigure::getInstance();

	$adminvars = $hitfigure->admin->register_client($id);	

	$vars = $hitfigure->template_vars($adminvars);
	display_mustache_template('newclient', $vars);
}



function view_clients( $request ) {
	// View Clients
	$type	= $request['type']; // manufacturer / dealer
	
	$hitfigure = HitFigure::getInstance();
	
	$vars = $hitfigure->template_vars(array('type'=>$type)); 
	display_mustache_template('viewclients', $vars);
}



function ajax_client_data( $request ) {
	// Return Ajax data to datatables
	$type	= $request['type']; // manufacturer / dealer
	
	$hitfigure = HitFigure::getInstance();
	
	$clientdata = $hitfigure->admin->get_client_data($type);

 	echo json_encode($clientdata);
}



function view_alerts( $request ) {
	// View Alerts
}



function bid( $request ) {
	// Bid
	$id 	= $request['id']; // Lead (vehicle) ID

	$hitfigure = HitFigure::getInstance();

	$adminvars = $hitfigure->admin->bid($id);	

	$vars = $hitfigure->template_vars($adminvars);
	display_mustache_template('bid', $vars);
}



function email_seller( $request ) {
	// Email Seller
	$id 	= $request['id']; // Lead (vehicle) ID
	echo $id;
}