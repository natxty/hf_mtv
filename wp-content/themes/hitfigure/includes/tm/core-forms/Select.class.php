<?php

class Select extends FormElement {



	protected $package = 'core-forms';



	public function __construct($id) {
		parent::__construct($id);
	}
	
	public function add_option( $id, $properties ) {
		$o = new Option($id);
		$o->setProperties($properties);
		$this->add($o);
		return $o;
	}
	
	public function set_value( $val ) {
		foreach( $this->children as $opt ) {
			if ( $opt->value == $val ) {
				$opt->selected = True;
			} else {
				$opt->selected = False;
			}
		}
	}
	
	public function get_value() {
		foreach ( $this->children as $opt ) {
			if ( $opt->selected ) {
				return $opt->value;
			}
		}
	}
	
	public function __get($property) {
		if ($property == 'value') {
			return $this->get_value();
		}
		return parent::__get($property);
	}
	
	public function __set($property,$value) {
		if ($property == 'value') {
			$this->set_value($value);
		} else {
			parent::__set($property,$value);
		}
	}
	
	public function options() {
		return $this->children;
	}



} // class Select

?>