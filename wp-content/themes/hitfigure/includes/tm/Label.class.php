<?php

class Label extends View {



	protected $package = 'core-views';
	
	public function errorMessage($message) { 	}

	public function validated() {
		// Always return True for buttons...
		return True;
	}

	public function clearError() { }



} // class Label

?>