<?php

namespace TableBuilder;

use Exception;

class Column {

	protected $table_builder;
	protected $options = array();
	protected $valid_options = array('field', 'label', 'searchable', 'sortable');

	public function __construct(TableBuilder $table, array $options = null) {
		$this->table_builder = $table;
		$this->setOptions($options);
	}

	public function setOptions(array $options = null) {
		$options = (array) $options;
		$this->options = array();
		foreach ( $options as $k => $v ) {
			if ( !in_array($k, $this->valid_options) ) {
				throw new Exception('Invalid option specified: ' . $k);
			}
			$this->options[$k] = $v;
		}
	}

	public function setOption($key, $value) {
		if ( !in_array($key, $this->valid_options) ) {
			throw new Exception('Invalid option specified: ' . $key);
		}
		$this->options[$key] = $value;
	}

	public function getOption($key) {
		return @$this->options[$key];
	}

}
