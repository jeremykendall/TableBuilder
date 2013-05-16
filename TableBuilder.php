<?php

namespace TableBuilder;

/**
 * TableBuilder
 */
class TableBuilder {

	/**
	 * A unique ID for this instance of TableBuilder.
	 */
	private $id;

	public function __construct($id) {
		$id = trim((string)$id);
		if ( $id == null ) {
			throw new Exception('A unique id ($id) must be specified for each instance of TableBuilder.');
		}
		$this->id = $id;
	}

}
