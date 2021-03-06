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
	
	$hitfigure->admin->dashboard();	
	$hitfigure->vars->merge('title', 'Dashboard');
	$hitfigure->admin->set_current_nav_link('dashboard');
		
	display_mustache_template('dashboard', $hitfigure->vars->get());
}



function view_leads( $request ) {
	// View Leads
	$hitfigure = HitFigure::getInstance();
	$hitfigure->is_logged_in();
	$hitfigure->admin->set_current_nav_link('view_leads');
	
	$hitfigure->admin->view_leads('all');
		
	display_mustache_template('viewleads', $hitfigure->vars->get());
}



function view_won_leads( $request ) {
	// View Leads
	$hitfigure = HitFigure::getInstance();
	$hitfigure->is_logged_in();
	$hitfigure->admin->set_current_nav_link('view_won_leads');
		
	$hitfigure->admin->view_leads('won');
		
	display_mustache_template('viewleads', $hitfigure->vars->get());	
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
	
	$hitfigure->admin->view_lead($id);

	display_mustache_template('lead', $hitfigure->vars->get());
}



function edit_client( $request ) {
	// Edit Client
	$id 	= $request['id']; // Client ID
	
	$hitfigure = HitFigure::getInstance();
	$hitfigure->is_logged_in();
	
	$hitfigure->admin->edit_client($id);	

	display_mustache_template('editclient', $hitfigure->vars->get());
}



function new_client( $request ) {
	// New Client
	$type		= $request['type']; // manufacturer / dealer / salesperson | accountant
	
	$hitfigure = HitFigure::getInstance();
	$hitfigure->is_logged_in();
	
	extract(client_type_to_name($type));
	
	$hitfigure->vars->merge(array(
		'title'					=>'New ' . $name,
		'pgheader'				=>'New ' . $name,
		'client_type'			=>$type,
		'client_name'			=>$name
	));		

	$hitfigure->admin->register_client($type);	

	display_mustache_template('newclient', $hitfigure->vars->get());
}



function view_clients( $request ) {
	// View Clients
	$type	= $request['type']; // manufacturer / dealer / salesperson | accountant
		
	$hitfigure = HitFigure::getInstance();
	$hitfigure->is_logged_in();
	$hitfigure->admin->set_current_nav_link('edit_client_'.$type);
		
	$hitfigure->admin->view_clients($type);
	
	display_mustache_template('viewclients', $hitfigure->vars->get());
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

	$hitfigure->admin->view_alert($id);
	

	display_mustache_template('alert',  $hitfigure->vars->get());
} 



function view_alerts( $request ) {
	// View Alerts
	$hitfigure = HitFigure::getInstance();
	$hitfigure->is_logged_in();
	$hitfigure->admin->set_current_nav_link('view_alerts');
	
	$hitfigure->admin->view_alerts();
	
	display_mustache_template('viewalerts', $hitfigure->vars->get());	
}



function ajax_alert_data( $request ) {	
	$hitfigure = HitFigure::getInstance();
	$hitfigure->is_logged_in();
	
	$alertdata = $hitfigure->admin->get_alert_data();

 	echo json_encode($alertdata);
}



function dismiss_alert( $request ) {
	$alert_id = $_REQUEST['id'];
	$redirect = null;
	if (isset($_REQUEST['redirect'])) {
		$redirect = $_REQUEST['redirect'];
	}

	$hitfigure = HitFigure::getInstance();
	$hitfigure->is_logged_in();
	
	$hitfigure->admin->dismiss_alert($alert_id, $redirect);
}



function bid( $request ) {
	// Bid
	$id 	= $request['id']; // Lead (vehicle) ID

	$hitfigure = HitFigure::getInstance();
	$hitfigure->is_logged_in();

	$hitfigure->admin->bid($id);	

	display_mustache_template('bid', $hitfigure->vars->get());
}



function email_seller( $request ) {
	// Email Seller
	$id 	= $request['id']; // Lead (vehicle) ID

	$hitfigure = HitFigure::getInstance();
	$hitfigure->is_logged_in();
	
	$hitfigure->admin->email_seller_form($id);
	
	display_mustache_template('email_seller', $hitfigure->vars->get());
}



function zamboni( $request ) {
	// =^_^= //
	$hitfigure = HitFigure::getInstance();
	$hitfigure->cron();	
}