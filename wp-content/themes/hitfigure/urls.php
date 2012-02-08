<?php

$url_patterns = array(
	'/admin\/dashboard/' 									=> 'hitfigure\views\dashboard',
	'/admin\/leads/'										=> 'hitfigure\views\view_leads',
	'/admin\/won/'											=> 'hitfigure\views\view_won_leads',
	'/admin\/lead\/(?P<id>[\d]+)/'							=> 'hitfigure\views\lead',
	'/admin\/view\/(?P<type>manufacturer|dealer|salesperson|accountant)/'			=> 'hitfigure\views\view_clients',
	'/admin\/new\/(?P<type>manufacturer|dealer|salesperson|accountant)/'			=> 'hitfigure\views\new_client',
	'/admin\/edit\/(?P<id>[\d]+)/'							=> 'hitfigure\views\edit_client',
	'/admin\/alerts/'										=> 'hitfigure\views\view_alerts',
	'/admin\/alert\/(?P<id>[\d]+)/'							=> 'hitfigure\views\alert',
	'/admin\/bid\/(?P<id>[\d]+)/'							=> 'hitfigure\views\bid',
	'/admin\/email\/(?P<id>[\d]+)/'							=> 'hitfigure\views\email_seller',
	'/how-it-works/'										=> 'hitfigure\views\how_it_works',
	'/thank-you\/(?P<id>[\d]+)/'							=> 'hitfigure\views\thank_you',
	'/faqs/'												=> 'hitfigure\views\faqs',
	'/contact/'												=> 'hitfigure\views\contact',
	'/homepage/'											=> 'hitfigure\views\homepage',
	'/colin/'												=> 'hitfigure\views\colin',
	'/ajax_view_leads\/(?P<type>all|won)/' 					=> 'hitfigure\views\ajax_lead_data',
	'/ajax_view_clients\/(?P<type>manufacturer|dealer|salesperson|accountant)/'	=> 'hitfigure\views\ajax_client_data',
	'/ajax_view_alerts/'									=> 'hitfigure\views\ajax_alert_data',
	'/zamboni/'												=> 'hitfigure\views\zamboni',
	'/tests/'												=> 'hitfigure\views\tests',
	'/(?P<slug>[\w-\/]+)\/?$/' 								=> 'hitfigure\views\page'
);

$ajax_url_patterns = array(
	'/ajax_app_setup/'	=> 'hitfigure\views\ajax_app_setup',
	'/ajax_form_data/'  => 'hitfigure\views\ajax_form_data'
);