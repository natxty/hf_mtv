<?php

class Button extends FormElement {



	protected $package = 'core-forms';
	protected $idRequired = false;



	public function errorMessage($message) { 	}

	public function validated() {
		// Always return True for buttons...
		return True;
	}

	public function clearError() { }



} // class Button

?>