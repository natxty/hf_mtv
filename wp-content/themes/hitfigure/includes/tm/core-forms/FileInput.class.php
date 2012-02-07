<?php

class FileInput extends FormElement {


    protected $package = 'core-forms';


	public function save() {
		if (!$this->validated() || !$this->file_tmp_name) { // Don't even think about it...
			return false;			
		}
		
		$upload_dir = wp_upload_dir();	
		$path = $upload_dir['path'];
		
		$wp_upload_path = $path . '/' . $this->file_name;
		$result = move_uploaded_file($this->file_tmp_name, $wp_upload_path);
		
		if ($result) {
			return $wp_upload_path;
		} else {
			// Failed...
			return false;
		}
	}
	
	



} // class File Input

?>