<?php
/**
 * Leer-Objekt
 *
 * Der Zugriff auf die Datenbank erfolgt über CRUD ZugriffsFunktionen
 * - create
 * - retrieve
 * - update
 * - del
 */
class fl_data_access_null implements data_access {
	public $true_value = true;
	public $false_value = false;

	public function __construct() {
	}

	public function create($table, array $data) {
		return 1;
	}

	public function retrieve($table, $field='*', $condition='', $order='', $limit='') {
		return array();
	}

	public function update($table, array $data, $id, $id_field='id', $all=FALSE) {
		return true;
	}

	public function del($table, $id) {
		return true;
	}

	public function count($table, $condition='') {
		return 0;
	}

	public function find_id($table, $condition) {
		return 0;
	}

	protected function _query_db($sql) {
		return true;
	}
}
?>
