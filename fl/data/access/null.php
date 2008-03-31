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
class data_null implements data_access {
	private $true_value = true;
	private $false_value = false;

	public function __construct() {
	}

	public function create($table, array $data) {
		return true;
	}

	public function retrieve($table, $field='*', $condition='', $order='', $limit='') {
		return true;
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

	public function query($sql) {
		return true;
	}
}
?>
