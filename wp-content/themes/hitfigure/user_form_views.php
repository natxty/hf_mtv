<?php

namespace hitfigure\views;
use hitfigure\models\HitFigure;

function how_it_works() {

	global $wpdb;
	$tbl = $wpdb->prefix . "wtmod_cardb";
	
	$states = array('Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut', 'Delaware', 'District of Columbia', 'Florida', 'Georgia', 'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa', 'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland', 'Massachusetts', 'Michigan', 'Minnesota', 'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire', 'New Jersey', 'New Mexico', 'New York', 'North Carolina', 'North Dakota', 'Ohio', 'Oklahoma', 'Oregon', 'Pennsylvania', 'Rhode Island', 'South Carolina', 'South Dakota', 'Tennessee', 'Texas', 'Utah', 'Vermont', 'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming', 'Armed Forces Americas', 'Armed Forces Europe', 'Armed Forces Pacific');
	
	
	$f = new \FormHelper('submit_vehicle');
	$f->method = 'POST';
	
	/* Vehicle Year */
	$s = new \Select('vehicle_year');
	$s->setProperties(array(
		'id' => 'id_vehicle_year',
        'name' =>'vehicle_year',
		'text' =>'Year',			
		'required'=>True
	));

    $s->add_option('opt', array('text'=> '-- Select A Year --'));
	
	$order = 'DESC';
	$years = $wpdb->get_results("select distinct car_year from $tbl order by car_year $order");
	foreach($years as $year) {
		$s->add_option('opt',array('text'=>$year->car_year,'value'=> $year->car_year));
	}
	
	$f->add($s);
	
	
	
	/* Vehicle Make */
	$s = new \Select('vehicle_make');
	$s->setProperties(array(
		'id' => 'id_vehicle_make',
		'name' =>'vehicle_make',
		'text' =>'Make',			
		'required'=>True
	));

    $s->add_option('opt',array('text'=> '-- Select A Make --'));

	$order = 'ASC';
	$makes = $wpdb->get_results("select distinct car_make from $tbl order by car_make $order");
	foreach($makes as $make) {
		$s->add_option('opt',array('text'=>$make->car_make,'value'=> $make->car_make));
	}
	
	$f->add($s);
	
	/* Vehicle Model */
	$s = new \Select('vehicle_model');
	$s->setProperties(array(
		'id' => 'id_vehicle_model',
		'name' =>'vehicle_model',
		'text' =>'Model',			
		'required'=>True
	));
	

	$order = 'ASC';
	$models = $wpdb->get_results("select distinct car_model from $tbl order by car_model $order limit 100");
	$s->add_option('opt',array('text'=>'-- Select A Model --'));
    foreach($models as $model) {
		$s->add_option('opt',array('text'=>$model->car_model,'value'=> $model->car_model));
	}
	$f->add($s);
	
	
	/* Vehicle Trim */
	$s = new \Select('vehicle_trim');
	$s->setProperties(array(
		'id' => 'id_vehicle_trim',
		'name' =>'vehicle_trim',
		'text' =>'Trim',
		'required'=>True
	));

	$s->add_option('opt',array('text'=>'-- Select A Trim --'));
	$f->add($s);
	
	/* Vehicle Transmission */
	$s = new \Select('vehicle_transmission');
	$s->setProperties(array(
		'id' => 'id_vehicle_transmission',
		'name' =>'vehicle_transmission',
		'text' =>'Transmission',
		'required'=>True
	));

	$s->add_option('opt',array('text'=>'-- Select a Transmission'));
    $s->add_option('opt',array('text'=>'Automatic', 'value' => 'Automatic'));
    $s->add_option('opt',array('text'=>'Manual', 'value' => 'Manual'));
	$f->add($s);

    /* Mileage */
    $s = new \Input('vehicle_mileage');
    $s->setProperties(array(
		'id' => 'id_vehicle_mileage',
		'name' =>'vehicle_mileage',
		'text' =>'Mileage',
		'required'=>True
	));
    $f->add($s);

     /* Exterior Color */
    $s = new \Input('vehicle_exterior_color');
    $s->setProperties(array(
		'id' => 'id_vehicle_exterior_color',
		'name' =>'vehicle_exterior_color',
		'text' =>'Exterior Color',
		'required'=>True
	));
    $f->add($s);

    /* Interior Color */
    $s = new \Input('vehicle_interior_color');
    $s->setProperties(array(
		'id' => 'id_vehicle_interior_color',
		'name' =>'vehicle_interior_color',
		'text' =>'Interior Color',
		'required'=>True
	));
    $f->add($s);

    /* Known Accidents Label */
    $l = new \Label('vehicle_known_accidents_label');
    $l->setProperties(array(
                          'id' => 'id_vehicle_known_accidents_label',
						  'text' => 'Any Known Accidents?',
                          'class' => 'radio known_accidents'
                      ));
    $f->add($l);

    /* Known Accidents */
    $r = new \RadioGroup('vehicle_known_accidents');
	$r->setProperties(array(
		'id' => 'id_vehicle_known_accidents',
		'name'=>'vehicle_known_accidents',
		'required'=>True
	));

	$r->add_radio_button('rb_known_accidents_1', array('text'=>'Yes', 'value'=> 'Yes'));
	$r->add_radio_button('rb_known_accidents_2', array('text'=>'No', 'value'=> 'No'));
    $f->add($r);

    /* Accidents Please Explain */
    $s = new \Input('vehicle_accidents_explain');
    $s->setProperties(array(
		'id' => 'id_vehicle_accidents_explain',
		'name' =>'vehicle_accidents_explain',
		'text' =>'Accidents? Please Explain',
		'required'=>False
	));
    $f->add($s);

    /* Tires Sixty Percent Label */
    $l = new \Label('vehicle_tires_sixty_percent_label');
    $l->setProperties(array(
                          'id' => 'id_vehicle_tires_sixty_percent_label',
						  'text' => 'Tires Better or Worse than 60%',
                          'class' => 'radio tires_sixty_percent'
                      ));
    $f->add($l);

    /* Tires Better/Worse 60% */
    $r = new \RadioGroup('vehicle_tires_sixty_percent');
	$r->setProperties(array(
		'id'=>'id_vehicle_tires_sixty_percent',
		'name'=>'vehicle_tires_sixty_percent',
		'required'=>True
	));

	$r->add_radio_button('rb_tires_sixty_percent_1', array('text'=>'Yes', 'value'=> 'Yes'));
	$r->add_radio_button('rb_tires_sixty_percent_2', array('text'=>'No', 'value'=> 'No'));
    $f->add($r);


    /* Paintwork Performed? Label */
    $l = new \Label('vehicle_paintwork_performed_label');
    $l->setProperties(array(
                           'id' => 'id_vehicle_paintwork_performed_label',
						   'text' => 'Any Paintwork Performed?',
                          'class' => 'radio paintwork_performed'
                      ));
    $f->add($l);

    /* Paintwork Performed? */
    $r = new \RadioGroup('vehicle_paintwork_performed');
	$r->setProperties(array(
		'id' => 'id_vehicle_paintwork_performed',
		'name'=>'vehicle_paintwork_performed',
		'required'=>True
	));

	$r->add_radio_button('rb_paintwork_performed_1', array('text'=>'Yes', 'value'=> 'Yes'));
	$r->add_radio_button('rb_paintwork_performed_2', array('text'=>'No', 'value'=> 'No'));
    $f->add($r);

    /* Paintwork? Please Explain */
    $s = new \Input('vehicle_paintwork_performed_explain');
    $s->setProperties(array(
		'id' => 'id_vehicle_paintwork_performed_explain',
		'name' =>'vehicle_paintwork_performed_explain',
		'text' =>'Paintwork Performed? Please Explain',
		'required'=>False
	));
    $f->add($s);

    /* Smoker Label */
    $l = new \Label('vehicle_smoker_label');
    $l->setProperties(array(
                          'id' => 'id_vehicle_smoker_label',
						  'text' => 'Smoker?',
                          'class' => 'radio smoker'
                      ));
    $f->add($l);

    /* Smoker? */
    $r = new \RadioGroup('vehicle_smoker');
	$r->setProperties(array(
		'id' => 'id_vehicle_smoker',
		'name'=>'vehicle_smoker',
		'required'=>True
	));

	$r->add_radio_button('rb_smoker_1', array('text'=>'Yes', 'value'=> 'Yes'));
	$r->add_radio_button('rb_smoker_2', array('text'=>'No', 'value'=> 'No'));
    $f->add($r);

    /* Paintwork Needed? Label */
    $l = new \Label('vehicle_paintwork_needed_label');
    $l->setProperties(array(
                          'id' => 'id_vehicle_paintwork_needed_label',
						  'text' => 'Any Paintwork Needed?',
                          'class' => 'radio paintwork_needed'
                      ));
    $f->add($l);

    /* Paintwork Needed? */
    $r = new \RadioGroup('vehicle_paintwork_needed');
	$r->setProperties(array(
		'id' => 'id_vehicle_paintwork_needed',
		'name'=>'vehicle_paintwork_needed',
		'required'=>True
	));

	$r->add_radio_button('rb_paintwork_needed_1', array('text'=>'Yes', 'value'=> 'Yes'));
	$r->add_radio_button('rb_paintwork_needed_2', array('text'=>'No', 'value'=> 'No'));
    $f->add($r);

    /* Paintwork Needed? Please Explain */
    $s = new \Input('vehicle_paintwork_needed_explain');
    $s->setProperties(array(
		'id' =>'id_vehicle_paintwork_needed_explain',
		'name' =>'vehicle_paintwork_needed_explain',
		'text' =>'Paintwork Needed? Please Explain',
		'required'=>False
	));
    $f->add($s);

    /* Interior Condition */
	$s = new \Select('vehicle_interior_condition');
	$s->setProperties(array(
		'id' =>'id_vehicle_interior_condition',
		'name' =>'vehicle_interior_condition',
		'text' =>'Transmission',
		'required'=>True
	));

	$s->add_option('opt',array('text'=>'-- Select a Condition --'));
    $s->add_option('opt',array('text'=>'Excellent', 'value' => 'Excellent'));
    $s->add_option('opt',array('text'=>'Good', 'value' => 'Good'));
    $s->add_option('opt',array('text'=>'Fair', 'value' => 'Fair'));
    $s->add_option('opt',array('text'=>'Poor', 'value' => 'Poor'));
	$f->add($s);

    /* Description of Overall Condition */
    $s = new \Textarea('vehicle_overall_condition');
    $s->setProperties(array(
		'id' =>'id_vehicle_overall_condition',
		'name' =>'vehicle_overall_condition',
		'text' =>'Description of Overall Condition',
		'required'=>True
	));
    $f->add($s);

    /* Who Owns the Title? Label */
    $l = new \Label('vehicle_title_owner_label');
    $l->setProperties(array(
                          'id' =>'id_vehicle_title_owner_label',
						  'text' => 'Who Owns The Title?',
                          'class' => 'radio title_owner'
                      ));
    $f->add($l);

    /* Who Owns the Title? */
    $r = new \RadioGroup('vehicle_title_owner');
	$r->setProperties(array(
		'id' =>'id_vehicle_title_owner',
		'name'=>'vehicle_title_owner',
		'required'=>True
	));

	$r->add_radio_button('rb_title_owner_1', array('text'=>'Payoff Required', 'value'=> 'Payoff Required'));
	$r->add_radio_button('rb_title_owner_2', array('text'=>'I Have The Title', 'value'=> 'I Have The Title'));
    $f->add($r);

    /* Replacing If Sold Label */
    $l = new \Label('vehicle_replacing_if_sold_label');
    $l->setProperties(array(
                          'id' =>'id_vehicle_replacing_if_sold_label',
						  'text' => 'Are you Replacing this Vehicle if Sold?',
                          'class' => 'radio replacing_if_sold'
                      ));
    $f->add($l);

    /* Replacing If Sold */
    $r = new \RadioGroup('vehicle_replacing_if_sold');
	$r->setProperties(array(
		'id' =>'id_vehicle_replacing_if_sold',
		'name'=>'vehicle_replacing_if_sold',
		'required'=>True
	));

	$r->add_radio_button('rb_replacing_if_sold_1', array('text'=>'Yes', 'value'=> 'Yes'));
	$r->add_radio_button('rb_replacing_if_sold_2', array('text'=>'No', 'value'=> 'No'));
    $f->add($r);
	
	/*** CONTINUE TO PAGE TWO ***/
	/*
	$b = new \Button('vehicle_next_page_1');
	$b->setProperties(array(
		'id' => 'id_vehicle_next_page_1',
		'name'	=> 'vehicle_next_page_1',
		'text'	=> 'Continue'
	));
	$f->add($b);
	*/

    /*** FILE UPLOADS **/
	
	for($x=1;$x<=10;$x++) {
		$i = new \FileInput('fileinput');
		$i->setProperties(array(
			'id' =>'id_vehicle_image_'.$x,
			'name' =>'vehicle_image_'.$x,
			'text' =>'Photo '.$x,
			'required'=>False
		));
		$f->add($i);
	}
	
	
	
	/*** END FILE UPLOADS **/

    /* First Name */
    $s = new \Input('vehicle_first_name');
    $s->setProperties(array(
		'id' =>'id_vehicle_first_name',
		'name' =>'vehicle_first_name',
		'text' =>'First Name',
		'required'=>True
	));
    $f->add($s);

    /* Last Name */
    $s = new \Input('vehicle_last_name');
    $s->setProperties(array(
		'id' =>'id_vehicle_last_name',
		'name' =>'vehicle_last_name',
		'text' =>'Last Name',
		'required'=>True
	));
    $f->add($s);

    /* Email */
    $s = new \Input('vehicle_email');
    $s->setProperties(array(
		'id' => 'id_vehicle_email',
		'name' =>'vehicle_email',
		'text' =>'Email Address',
		'required'=>True
	));
    $f->add($s);

    /* Phone */
    $s = new \Input('vehicle_phone');
    $s->setProperties(array(
		'id' => 'id_vehicle_phone',
		'name' =>'vehicle_phone',
		'text' =>'Phone Number',
		'required'=>True
	));
    $f->add($s);


    /* Address 1 */
    $s = new \Input('vehicle_address_1');
    $s->setProperties(array(
		'id' =>'id_vehicle_address_1',
		'name' =>'vehicle_address_1',
		'text' =>'Address 1',
		'required'=>True
	));
    $f->add($s);

    /* Address 2 */
    $s = new \Input('vehicle_address_2');
    $s->setProperties(array(
		'id' =>'id_vehicle_address_2',
		'name' =>'vehicle_address_2',
		'text' =>'Address 2',
		'required'=>False
	));
    $f->add($s);

    /* City */
    $s = new \Input('vehicle_city');
    $s->setProperties(array(
		'id' =>'id_vehicle_city',
		'name' =>'vehicle_city',
		'text' =>'City',
		'required'=>True
	));
    $f->add($s);

    /* State */
    $s = new \Select('vehicle_state');
    $s->setProperties(array(
		'id' =>'id_vehicle_state',
		'name' =>'vehicle_state',
		'text' =>'State',
		'required'=>True
	));
	
	$s->add_option('opt',array('text'=>'-- Select a State --'));
    foreach($states as $state) {
		$s->add_option('opt',array('text'=> $state, 'value' => $state ));	
	}
	$f->add($s);
	
    $f->add($s);

    /* Zip Code */
    $s = new \Input('vehicle_zipcode');
    $s->setProperties(array(
		'id' =>'id_vehicle_zipcode',
		'name' =>'vehicle_zipcode',
		'text' =>'Zip Code',
		'required'=>True
	));
    $f->add($s);

    /* Button */
	$b = new \Button('confirm');
	$b->setProperties(array(
		'id' => 'id_confirm',
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

		
	$formvars = array('form' => $f->get_data());


	// HitFigure is the main 'app' class, your code is its bitch
	$hitfigure = HitFigure::getInstance();	
	$vars = $hitfigure->template_vars($formvars);

	/*
	print "<pre>\n";
    print_r($vars);
    print "</pre>";
	*/
	
	display_mustache_template('sellerform', $vars);
}

function ajax_form_data($request) {

    $post_data = json_decode(str_replace("\\", "", $_POST['data']));
    /*
     foreach($posts_data as $key => $value) {
        $txt .= $key . "= " . $value . "\n";
    }
    */
	/*
    $time = date("Y-d-m",time());
    $fp = fopen('/Users/natxty/Documents/production_sites/hitfigure/_log/' . $time . '_log.txt', 'a');
    fwrite($fp, $post_data."\n");
    fclose($fp);
	*/
    // do something with my data
    //$return_data = json_encode($posts_data);
    
    $success = true;

    if ($success)
      \mtv\shortcuts\display_json($post_data);
        //echo '{ "response" : "here" }';
    else
        throw new \mtv\http\AjaxHttp500("Something bad happened.");

}



