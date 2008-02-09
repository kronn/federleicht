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
class data_null {
	function data_null() {
	}

	function create() {
		return true;
	}

	function retrieve($table, $field='*', $condition='', $order='', $limit='') {
		return true;
	}

	function update($table, $data, $id, $id_field='id', $all=FALSE) {
		return true;
	}

	function del($table, $id) {
		return true;
	}

	function count($table, $condition='') {
		return 0;
	}

	function find_id($table, $condition) {
		return 0;
	}

	function query($sql) {
		return true;
	}
}
?>
