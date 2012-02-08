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
	$hitfigure = HitFigure::getInstance();
	$hitfigure->is_logged_in();
	
	//$hitfigure->vars->set_logging(true);
	
	$pagevars = array(
		'title'		=>'Dashboard'
	);
	

	
	$hitfigure->admin->dashboard();	
	$hitfigure->vars->merge('title', 'Dashboard');
		
	display_mustache_template('dashboard', $hitfigure->vars->get());
}



function view_leads( $request ) {
	// View Leads
	$hitfigure = HitFigure::getInstance();
	$hitfigure->is_logged_in();

	$pagevars = array(
		'title'		=>'View Leads',
		'pgheader'	=>'View Leads',
		'is_all'	=>True
	);
		
	$vars = $hitfigure->page_vars($pagevars);
	display_mustache_template('viewleads', $vars);
	
	
}



function view_won_leads( $request ) {
	// View Leads
	$hitfigure = HitFigure::getInstance();
	$hitfigure->is_logged_in();
		
	$pagevars = array(
		'title'		=>'View Won Leads',
		'pgheader'	=>'View Won Leads',
		'is_all'	=>False
	);
		
	$vars = $hitfigure->page_vars($pagevars);
	display_mustache_template('viewleads', $vars);
	
	
}



function ajax_lead_data( $request ) {
	$type = $request['type'];

	// Return Ajax data to datatables
	$hitfigure = HitFigure::getInstance();
	$hitfigure->is_logged_in();
	
	if ($type=='won') {
		$leaddata = $hitfigure->admin->get_won_lead_data();	
	} else {
		$leaddata = $hitfigure->admin->get_lead_data();		
	}
 	echo json_encode($leaddata);
}



function lead( $request ) {
	// View Single Lead
	$id = $request['id']; // Lead (vehicle) id
	
	$hitfigure = HitFigure::getInstance();
	$hitfigure->is_logged_in();
	
	$adminvars = $hitfigure->admin->view_lead($id);	
	
	$vars = $hitfigure->template_vars($adminvars);
	display_mustache_template('lead', $vars);
}



function edit_client( $request ) {
	// Edit Client
	$id 	= $request['id']; // Client ID
	
	$hitfigure = HitFigure::getInstance();
	$hitfigure->is_logged_in();
	
	$adminvars = $hitfigure->admin->edit_client($id);	
	
	$vars = $hitfigure->template_vars($adminvars);  
	display_mustache_template('editclient', $vars);
}



function new_client( $request ) {
	// New Client
	$type		= $request['type']; // manufacturer / dealer / salesperson | accountant
	
	$hitfigure = HitFigure::getInstance();
	$hitfigure->is_logged_in();

	$adminvars = $hitfigure->admin->register_client($type);	

	$vars = $hitfigure->template_vars($adminvars);
	display_mustache_template('newclient', $vars);
}



function view_clients( $request ) {
	// View Clients
	$type	= $request['type']; // manufacturer / dealer / salesperson | accountant
	
	$hitfigure = HitFigure::getInstance();
	$hitfigure->is_logged_in();
	
	$vars = $hitfigure->template_vars(array('type'=>$type)); 
	display_mustache_template('viewclients', $vars);
}



function ajax_client_data( $request ) {
	// Return Ajax data to datatables
	$type	= $request['type']; // manufacturer / dealer
	
	$hitfigure = HitFigure::getInstance();
	$hitfigure->is_logged_in();
	
	$clientdata = $hitfigure->admin->get_client_data($type);

 	echo json_encode($clientdata);
}



function alert( $request ) {
	$id = $request['id']; // Alert id

	$hitfigure = HitFigure::getInstance();
	$hitfigure->is_logged_in();
	
	$adminvars = $hitfigure->admin->view_alert($id);	

	$vars = $hitfigure->template_vars($adminvars);
	display_mustache_template('alert', $vars);
} 



function view_alerts( $request ) {
	// View Alerts
	$hitfigure = HitFigure::getInstance();
	$hitfigure->is_logged_in();
	
	$vars = $hitfigure->template_vars(); 
	display_mustache_template('viewalerts', $vars);	
}



function ajax_alert_data( $request ) {	
	$hitfigure = HitFigure::getInstance();
	$hitfigure->is_logged_in();
	
	$alertdata = $hitfigure->admin->get_alert_data();

 	echo json_encode($alertdata);
}



function bid( $request ) {
	// Bid
	$id 	= $request['id']; // Lead (vehicle) ID

	$hitfigure = HitFigure::getInstance();
	$hitfigure->is_logged_in();

	$adminvars = $hitfigure->admin->bid($id);	

	$vars = $hitfigure->template_vars($adminvars);
	display_mustache_template('bid', $vars);
}



function email_seller( $request ) {
	// Email Seller
	$id 	= $request['id']; // Lead (vehicle) ID
	echo $id;
}



function zamboni( $request ) {
	// =^_^= //
	$hitfigure = HitFigure::getInstance();
	$hitfigure->cron();	
}