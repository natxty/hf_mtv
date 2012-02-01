<?php

/**
 * View filtering iterator
 *
 * Iterates over non-anonymous views.
 *
 * @version DR1.5
 */
class ViewFilterIterator extends FilterIterator {



	/**
	 * Accepts item for iteration
	 *
	 * Item is accepted when is instance of View and has identifier
	 * @return boolean
	 */
	public function accept() {
        $v = $this->current();
		return $v instanceof View and $v->getId();
	}



} // class ViewFilterIterator

?>