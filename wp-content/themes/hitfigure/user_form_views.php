<?php

namespace hitfigure\views;

function how_it_works() {

	global $wpdb;
	$tbl = $wpdb->prefix . "wtmod_cardb";
	
	$f = new \FormHelper('submit_vehicle');
	$f->method = 'POST';
	
	/* Vehicle Year */
	$s = new \Select('vehicle_year');
	$s->setProperties(array(
		'name' =>'vehicle_year',
		'text' =>'Year',			
		'required'=>True
	));
	
	$order = 'DESC';
	$years = $wpdb->get_results("select distinct car_year from $tbl order by car_year $order");
	foreach($years as $year) {
		$s->add_option('opt',array('text'=>$year->car_year,'value'=> $year->car_year));
	}
	
	$f->add($s);
	
	
	
	/* Vehicle Make */
	$s = new \Select('vehicle_make');
	$s->setProperties(array(
		'name' =>'vehicle_make',
		'text' =>'Make',			
		'required'=>True
	));
	
	$order = 'ASC';
	$makes = $wpdb->get_results("select distinct car_make from $tbl order by car_make $order");
	foreach($makes as $make) {
		$s->add_option('opt',array('text'=>$make->car_make,'value'=> $make->car_make));
	}
	
	$f->add($s);
	
	/* Vehicle Model */
	$s = new \Select('vehicle_model');
	$s->setProperties(array(
		'name' =>'vehicle_model',
		'text' =>'Model',			
		'required'=>True
	));
	
	/*
	$order = 'ASC';
	$models = $wpdb->get_results("select distinct car_model from $tbl order by car_model $order limit 100");
	foreach($models as $model) {
		$s->add_option('opt',array('text'=>$model->car_model,'value'=> $model->car_model));
	}
	*/
	$s->add_option('opt',array('text'=>'Please Select'));
	$f->add($s);
	
	
	/* Vehicle Trim */
	
	/* Vehicle Transmission */
	
	

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
}



