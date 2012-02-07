<?php

abstract class FormElement extends ValueReadableView {

	public function validated() {
		if ( !is_bool($this->_validated) ) {
			$this->_validated = $this->validate();
		}
		return $this->_validated;
	}
	
	public function validate() {
		if (!$this->isPostBack) {
			return True;
		} else {
			if (!$this->_met_required()) {
				$this->errormsg = "This is a required field";
				return False;
			}
			$validate_func = $this->validate_func;
			if (is_callable($validate_func)) {
				$r = $validate_func($this);
				if ($r !== True) {
					$this->errormsg  = $r;
					return False;
				}
			}
		}
		return True;
	}
	
	private function _met_required() {
		if ($this->required && !$this->value) {
			var_dump($this);
			return False;
		}
		return True;
	}

}
