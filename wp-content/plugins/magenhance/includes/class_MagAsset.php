<?php

interface iMagAsset {
	public function get_obj_string();
}

class MagAsset implements iMagAsset {
	public function __construct($post_id,$data=null) {
		if (!$data)
			$data = get_post($post_id);
		$this->data = $data;
		$this->type = $this->data->post_type == 'cpt-script' ? 'script' : 'style';
		$this->source_path = 	get_post_meta($post_id,'enh-source-path',true);
		$this->source_root = 	get_post_meta($post_id,'enh-source-root',true);
		$this->media = 			get_post_meta($post_id,'enh-media',true);
		$this->excludemedia = 	get_post_meta($post_id,'enh-excludemedia',true);
		$this->iecondition = 	get_post_meta($post_id,'enh-iecondition',true);	
	}
	
	public function get_obj_string() {
		$file_path = $this->build_file_path();
		$path_name = $this->type == 'style' ? 'href' : 'src';
		
		$obj = array(
			$path_name 		=> $file_path,
			'media'			=> $this->media,
			'excludemedia'	=> $this->excludemedia,
			'iecondition'	=> $this->iecondition
		);

		return str_replace( '\/','/', json_encode($obj) );
	}
	
	public function enqueue($priority=10) {
		/*
		 * This function is only partially finished
		 * it needs to support all of the other options available
		 * such as IE conditions and exclude media 
		*/
		$file_path 	= $this->build_file_path();
		$handle		= $this->data->post_title;
		$media		= $this->media;
	
		if ( $this->type == 'script' ) {
			add_action ('wp_enqueue_scripts', function() use ($handle, $file_path) {
				wp_enqueue_script( $handle, $file_path );
			},$priority);
		} else {
			add_action ('wp_enqueue_scripts', function() use ($handle, $file_path, $media) {
				wp_enqueue_style( $handle, $file_path, '', '',  $media );
			},$priority);
		}
	}
	
	private function build_file_path() {
		$path = '';
		switch($this->source_root) {
			case 'theme':
				$path = get_bloginfo('template_url') . '/' . $this->source_path;
			break;
			case 'plugins':
				$path = plugins_url() . '/' . $this->source_path;
			break;
			default: /* None */
				$path = 'http://' . $this->source_path;
		}
		return $this->append_query_vars($path);
	}
	
	private function append_query_vars($path) {
		if ( pathinfo($path, PATHINFO_EXTENSION) != 'php' )
			return $path;
		
		
		$post_id 	= $this->data->ID;
		$fields		= array();
		
		foreach(get_post_custom($post_id) as $key=>$value) {
			if ( $this->startsWith($key,'_') || $this->startsWith($key,'enh-') ) {
				continue;
			} else {
				$fields[$key] = $value;
			}
		}
		
		return add_query_arg( $fields, $path.'?wpurl='. get_bloginfo('wpurl') );
	}
	
	private function startsWith($haystack, $needle) {
		$length = strlen($needle);
		return (substr($haystack, 0, $length) === $needle);
	}
	
}


?>