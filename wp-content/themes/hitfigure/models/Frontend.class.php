<?php

namespace hitfigure\models;


class Frontend {
	
	public function thank_you($id) {
		$vehicle = new Vehicle(array('id'=>$id));
		$vehicle->fetch();
		
		return $vehicle->get_vars();
	}


}


class Contact extends Post {
	public $defaults = array('post_type' => 'cpt-contact', 'post_status'=>'publish');
	
	public function save() {
		
		if ( isset($this->attributes['contact_full_name']) ) {
			$this->attributes['post_meta']['contact_full_name'] = $this->attributes['contact_full_name'];
			unset($this->attributes['contact_full_name']);
		}
		
		if ( isset($this->attributes['contact_email']) ) {
			$this->attributes['post_meta']['contact_email'] = $this->attributes['contact_email'];
			unset($this->attributes['contact_email']);
		}
		
		//this should be in there...
		//$this->attributes['post_content'] = $this->render_content();
				
		parent::save();
	}
}