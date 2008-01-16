<?php
/**
 * Allgemeines Datenstrukturobjekt
 *
 * @author Matthias Viehweger <kronn@kronn.de>
 * @version 0.2
 * @package federleicht
 * @subpackage base
 */
class data_structure {
	/**
	 * Daten direkt ausgeben
	 *
	 * @param string $key
	 */
	function say($key) {
		echo $this->_get_field($key);
	}

	/**
	 * Daten zurückgeben
	 * 
	 * @param string $key
	 * @return mixed
	 */
	function get($key) {
		return $this->_get_field($key);
	}

	/**
	 * Daten in Datenstruktur setzen
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	function set($key, $value) {
		$this->_set_field($key, $value);
	}

	/**
	 * Ein assoziatives Array als Daten übernehmen
	 *
	 * @param array $data
	 */
	function set_data($data) {
		foreach ($data as $key => $value ) {
			$this->set($key, $value);
		}
	}

	/**
	 * Ein assoziatives Array aller Daten holen
	 *
	 * @return array
	 */
	function get_data() {
		$data = (array) $this;

		return $data;
	}

	/**
	 * Konstruktor
	 *
	 * @param array $data
	 */
	function data_structure($data = null) {
		if ( $data === null ) {
			$data = array();
		}

		$this->set_data($data);
	}

	/** ====== Zugriffsfunktionen auf die internen Daten ====== */
	/**
	 * Daten aus Objekt holen
	 *
	 * @param string $key
	 * @return mixed
	 */
	function _get_field($key) {
		if ( !isset($this->key) ) {
			$value = '';
		} else {
			$value = $this->$key;
		}

		return $value;
	}

	/**
	 * Daten in Datenobjekt schreiben
	 *
	 * es wird eine Liste der hinzugefügten Schlüssel geführt.
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	function _set_field($key, $value) {
		$this->$key = $value;
	}
}
