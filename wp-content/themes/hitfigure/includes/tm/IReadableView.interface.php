<?php

interface IReadableView {



	public function valueReader();



	public function errorMessage($message);



	public function clearError();



} // interface IReadableView

?>