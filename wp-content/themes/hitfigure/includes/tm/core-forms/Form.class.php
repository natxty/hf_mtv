<?php

class Form extends View implements IReadableView {



    protected $package = 'core-forms';



	public function valueReader() {
		$out = array();
		$family = $this->getFamily();
		foreach ($family as $id => $child) {
			if ($child instanceof IReadableView) {
				$out[] = "&quot;" . $id . "&quot;: &quot;' + " . $child->valueReader() . " + '&quot;'";
			}
		}
		return "'{" . implode(" + ', ", $out) . " + '}'";
	}



	public function errorMessage($message) {
		throw new Exception('cannot set Form error message');
	}



	public function clearError() {
		foreach ($this->getFamily() as $child) {
			if ($child instanceof IReadableView) {
				 $child->clearError();
			}
		}
	}



} // class Form

?>