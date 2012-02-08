<?php

namespace hitfigure\models;


/*
 * A class to store variables we'll add to our template
 */
class TemplateVariablesContainer {
	protected $_vars 			= array();
	protected $_protected		= array();
	protected $_log 			= array();
	protected $_keep_log		= false;
	
	public function __construct($log=false) {
		$this->set_logging($log);
	}
	 	 
	public function add() {
		$vars = $this->outputArray(func_get_args());
		$this->_vars = $this->_vars + $vars;
	}

	public function merge() {
		$vars = $this->outputArray(func_get_args());
		$this->_vars = array_merge($this->_vars, $vars);
	}
	
	public function add_protected() {
		$vars = $this->outputArray(func_get_args());
		$this->_protected = $this->_protected + $vars;
	}

	public function get() {
		// Combine our vars and protected
		return array_merge($this->_vars, $this->_protected);
	}
	
	public function set_logging($log = false) {
		$this->_keep_log = $log;
	}
	
	protected function log($vars) {
		$time = date("Y-m-d H:i:s");
		$this->_log[$time] = array(
			'var_array' => $vars,
			'backtrace' => debug_backtrace()
		);
	}
	
	public function print_log($exit=true) {
		print_r($this->_log);
		if ($exit) exit;
	}


	/*
	 * Add, merge and add_protected can either be key=>values or an array
	 */	
	protected function outputArray($args) {
		$a = array();
		
		if (count($args) > 1 && is_string($args[0])) { // Key + Value
			$a[$args[0]] = $args[1];
		
		} elseif (count($args) == 1 && is_array($args[0])) {
			$a = $args[0];
		
		}
		
		if ($this->_keep_log) $this->log($a);
		return $a;
	}	

}
