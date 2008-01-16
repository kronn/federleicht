<?php
/**
 * ActiveRecord-Objekt
 *
 * @author Matthias Viehweger <kronn@kronn.de>
 * @version 0.2
 */
class active_record {
	/**
	 * Instanzvariablen
	 */
	var $db = null;
	var $table = '';
	var $data = null;
	var $id = null;

	/**
	 * Konstruktor
	 *
	 * @param datamodel $db
	 * @param string $table
	 * @param int $id
	 * @param data_structure $data
	 */
	function active_record($db, $table, $id, $data) {
		$this->db =& $db;
		$this->table = $table;
		$this->id = $id;

		if ( !(is_a($data, 'data_structure')) ) {
			$factory = new factory();
			$factory->set_data_access($this->db);
			$this->data = $factory->get_structure('data_structure');
		} else {
			$this->data = $data;
		}

		$this->load();
	}

	/**
	 * Daten setzen
	 */
	function set_data($data) {
		foreach ( $data as $key => $value ) {
			if ( empty($value) ) continue;
			
			$this->data->set($key, $value);
		}
	}

	/**
	 * Daten holen
	 */
	function get_data() {
		$data = array();

		foreach ( $this->data->get_data() as $key => $value ) {
			if ( empty($value) OR $value === null ) continue;

			$data[$key] = $value;
		}

		return $data;
	}

	/**
	 * Daten aus Datenbank laden
	 */
	function load() {
		if ( $this->id > 0 ) {
			$data = $this->db->retrieve($this->table, '*', 'id='.$this->id);
		} else {
			$data = array();
		}

		return $this->data->set_data($data);
	}

	/**
	 * Daten in Datenbank speichern
	 *
	 * @return boolean
	 */
	function save() {
		if ( $this->id > 0 ) {
			$result = $this->db->update($this->table, $this->get_data(), $this->id);
		} else {
			$result = $this->db->create($this->table, $this->get_data());
		}

		return $result;
	}
}
