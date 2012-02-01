<?php

class RadioGroup extends FormElement  {

	protected $package = 'core-forms';
	
	public function add_radio_button($id, $properties) {
		$r = new RadioButton($id);
		$r->setProperties($properties);
		$r->name = $this->name;
		$this->add($r);
	}

	
	public function get_value() {
		foreach ($this->children as $r) {
			if ($r->checked) {
				return $r->value;
			}
		}
	}
	
	
	public function set_value($value) {
		foreach ($this->children as $r) {
			if ($r->value == $value) {
				$r->checked = True;
			} else {
				$r->checked = False;
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



} // class RadioGroup

?>