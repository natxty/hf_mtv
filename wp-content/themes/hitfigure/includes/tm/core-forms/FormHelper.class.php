<?php

class FormHelper extends Form {
			
	public function applyUserInput($isPostBack=False) {
		// isPostBack must be set to true manually
		if (!$isPostBack) {
			return;
		}
		// Set the values of our forms child elements to the values in our Request
		// and let then know this isPostBack
		foreach ($this->children as $child) {
			$child->isPostBack = True;
			if (array_key_exists($child->name, $_REQUEST)) {
				$child->value = $_REQUEST[$child->name];
			}
			if (array_key_exists($child->name, $_FILES)) {
				// Hot damn, a file is being uploaded...
				foreach ($_FILES[$child->name] as $key=>$value) {
					$child->{"file_".$key} = $value;
				}
				$child->value = $child->file_name;
			}
		}
	}
	
	public function validate() {
		foreach ($this->children as $child) {
			if ( !$child->validated()) {
				return False;
			}
		}
		return True;		
	}
	
	public function get_data() {
		return $this->data;
	}	

}