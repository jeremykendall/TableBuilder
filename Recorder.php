<?php

namespace TableBuilder;

class Recorder {

	private $calls = array();
	private $properties = array();

	public function __construct() {
	}

	public function __set($key, $value) {
		$this->properties[$key] = $value;
	}

	public function __call($name, $arguments) {
		$this->calls[] = array(
			'name' => $name,
			'arguments' => $arguments
		);
	}

	public function getProperties() {
		return (array) $this->properties;
	}

	public function getCalls() {
		return (array) $this->calls;
	}

}
