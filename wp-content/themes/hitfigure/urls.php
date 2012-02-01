<?php

$url_patterns = array(
	'/admin\/dashboard/' 								=> 'hitfigure\views\dashboard',
	'/admin\/leads/'									=> 'hitfigure\views\view_leads',
	'/admin\/lead\/(?P<id>[\d]+)/'						=> 'hitfigure\views\lead',
	'/admin\/view\/(?P<type>manufacturer|dealer)/'		=> 'hitfigure\views\view_clients',
	'/admin\/new\/(?P<type>manufacturer|dealer)/'		=> 'hitfigure\views\new_client',
	'/admin\/edit\/(?P<id>[\d]+)/'						=> 'hitfigure\views\edit_client',
	'/admin\/alerts/'									=> 'hitfigure\views\view_alerts',
	'/admin\/bid\/(?P<id>[\d]+)/'						=> 'hitfigure\views\bid',
	'/admin\/email\/(?P<id>[\d]+)/'						=> 'hitfigure\views\email_seller',
	'/how-it-works/'									=> 'hitfigure\views\how_it_works',
	'/(?P<slug>[\w-\/]+)\/?$/' 							=> 'hitfigure\views\page'
);

$ajax_url_patterns = array(
	'/ajax_app_setup/'	=> 'hitfigure\views\ajax_app_setup',
);