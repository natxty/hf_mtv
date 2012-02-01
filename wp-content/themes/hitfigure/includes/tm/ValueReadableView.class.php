<?php

abstract class ValueReadableView extends View implements IReadableView {



	protected $idRequired = true;

	const NORMAL_COLOR = 'white'; // this is bad! palette.txt values should be used instead!
	const ERROR_COLOR = '#FFB0B0'; //



    public function valueReader() {
        return "ValueReadableView.getValue('{$this->id}')";
    }



	public function errorMessage($message) {
		$this->title = $message;
		$this->style = $this->style . '; background-color: '.self::ERROR_COLOR.';';
		$response = ResponseContext::getInstance();
		$response->eval("ValueReadableView.errorMessage('{$this->id}', '$message')");
	}



	public function clearError() {
		$this->title = '';
		$this->style = $this->style . '; background-color: '.self::NORMAL_COLOR.';';
		$response = ResponseContext::getInstance();
		$response->eval("ValueReadableView.clearError('{$this->id}')");
	}



} // class ValueReadableView

?>