<?php
/**
 * Federleicht-Funktionen
 *
 * Die Klasse functions enthält Methoden, die in den meisten
 * anderen Objekten verfügbar sind.
 *
 * @package federleicht
 * @subpackage base
 */
class functions {
	/**
	 * Referenzen auf Objekte
	 */
	var $flash = null;
	var $data_access = null;
	var $registry = null;
	var $factory = null;

	/**
	 * Konstruktor
	 */
	function functions() {
		$this->registry =& registry::getInstance();
		$this->factory = new factory();
		$this->structures =& $this->factory->structures;
	}

	/**
	 * Datenzugriff setzen
	 *
	 * @param data_access $data_access
	 */
	function set_data_access(&$data_access) {
		$this->data_access = &$data_access;
		$this->factory->set_data_access($data_access);
	}

	/**
	 * Aufruf eines Helfermodul
	 *
	 * @param string $wanted Name des gewünschten Helfermoduls
	 * @return boolean
	 */
	function needs($wanted) {
		return $this->factory->load_helper($wanted);
	}

	/**
	 * Helfermodul holen
	 *
	 * @param string $wanted_helper
	 * Weitere Parameter werden übernommen und an den Konstruktor weitergegeben.
	 *
	 * @return mixed
	 */
	function get_helper($wanted_helper) {
		if ( func_num_args() > 1 ) {
			trigger_error('function does not support multiple Parameters. Use factory->get_helper() instead', E_USER_ERROR);
		} else {
			trigger_error('deprecated, use factory->get_helper() instead', E_USER_NOTICE);
		}

		return $this->factory->get_helper($wanted_helper);
	}

	/**
	 * Weiteres Model holen
	 *
	 * Nach Möglichkeit wird das Model aus der Registry geholt.
	 *
	 * @param string $modul   Name des Moduls, aus dem das Model geholt wird.
	 * @return model
	 */
	function get_model($modul) {
		return $this->factory->get_model($modul);
	}

	/**
	 * Moduldatei einlesen
	 *
	 * @param string $modul
	 */
	function load_module($modul) {
		return $this->factory->load_module($modul);
	}

	/**
	 * Flashnachrichten starten
	 */
	function start_flash() {
		$this->flash = new flash();
	}

	/**
	 * Federleicht anhalten
	 */
	function stop($message='') {
		if ( isset($this->flash) ) {
			$this->flash->_flash();
		}

		echo $message;
		exit();
	}
}
?>