<?php

namespace hitfigure\views;

function how_it_works() {
	
	
	$f = new \FormHelper('submit_vehicle');
	$f->method = 'POST';
	
	/* Vehicle Year */
	$s = new \Select('vehicle_year');
	$s->setProperties(array(
		'name' =>'vehicle_year',
		'text' =>'Year',			
		'required'=>True
	));
	
	$s->add_option('opt',array('text'=>'2011','value'=>'2011'));

	
	//set the efault value... 
	//$s->value = '2011';
	
	$f->add($s);
	
	/* Vehicle Make */
	$s = new \Select('vehicle_make');
	$s->setProperties(array(
		'name' =>'vehicle_make',
		'text' =>'Make',			
		'required'=>True
	));
	
	$s->add_option('opt',array('text'=>'Select One'));
	$s->add_option('opt',array('text'=>'opt1','value'=>'myval1'));
	$s->add_option('opt',array('text'=>'opt2','value'=>'myval2'));
	
	$f->add($s);
	
	/* Vehicle Model */
	
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