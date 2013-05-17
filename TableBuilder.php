<?php

namespace TableBuilder;

use ORM;
use Exception;
use Audit;

/**
 * TableBuilder
 */
class TableBuilder {

	/**
	 * A unique ID for this instance of TableBuilder.
	 */
	private $id;

	/**
	 * The data store behind this instance of TableBuilder.
	 */
	private $backend = 'mysql';

	/**
	 * DB abstraction layer.
	 */
	private $dba;

	/**
	 * The various backend data stores that TableBuilder supports.
	 */
	private $supported_backends = array('mysql', 'mongodb');

	private $table;

	private $recorder;

	private $results;

	private $parsed_results;

	private $columns = array();

	public function __construct($id) {
		$id = trim((string)$id);
		if ( $id == null ) {
			throw new Exception('A unique id ($id) must be specified for each instance of TableBuilder.');
		}
		$this->id = $id;
	}

	public function setBackend($backend) {
		if ( !in_array($backend, $this->supported_backends) ) {
			throw new Exception("The specified backend is not supported: {$backend}");
		}
		$this->backend = $backend;
	}

	public function setTable($table) {
		$this->table = $table;
	}

	public function getQuery() {
		$this->recorder = new Recorder();
		return $this->recorder;
	}

	public function run() {
		// $this->getResults();
		$x = $this->getParsedResults();
		Audit::Log($x, 'parsed results');
	}

	public function createColumn(array $options = null) {
		$column = new Column($this, $options);
		$this->columns[] = $column;
		return $column;
	}

	private function getResults() {
		if ( is_array($this->results) ) {
			return $this->results;
		}
		$dba = $this->getDBA();
		$properties = $this->recorder->getProperties();
		$calls = $this->recorder->getCalls();
		foreach ( $properties as $k => $v ) {
			$dba->$k = $v;
		}
		foreach ( $calls as $call ) {
			call_user_func_array(array($dba, $call['name']), $call['arguments']);
		}
		switch ( $this->backend ) {
			case 'mysql' :
				$query = $dba->find_many();
				$results = array();
				foreach( $query as $q ) {
					$results[] = $q->as_array();
				}
			break;
			case 'mongodb' :
			break;
			default :
				throw new Exception("Invalid backend data store specified.");
			break;
		}
		$this->results = $results;
		return $this->results;
	}

	private function getParsedResults() {
		if ( is_array($this->parsed_results) ) {
			return $this->parsed_results;
		}
		$results = $this->getResults();
		$keys = array();
		foreach ( $this->columns as $column ) {
			$field = $column->getOption('field');
			$alias = $column->getOption('alias');
			if ( $field != null ) {
				$keys[] = $field;
			}
			if ( $alias != null ) {
				$keys[] = $alias;
			}
		}
		foreach ( $results as $k => $row ) {
			foreach ( $row as $k2 => $v ) {
				if ( !in_array($k2, $keys) ) {
					unset($row[$k2]);
				}
				$results[$k] = $row;
			}
		}
		$this->parsed_results = $results;
		return $this->parsed_results;
	}

	/**
	 * Returns an instance of the appropriate database abstraction layer, based on the
	 * backend data store that has been selected.
	 */
	private function getDBA() {
		if ( $this->dba != null ) {
			return $this->dba;
		}
		switch ( $this->backend ) {
			case 'mysql' :
				$this->dba = ORM::for_table($this->table);
			break;
			case 'mongodb' :
			break;
			default :
				throw new Exception("Invalid backend data store specified.");
			break;
		}
		return $this->dba;
	}

}
