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
class fl_functions {
	/**
	 * Referenzen auf Objekte
	 */
	public $flash = null;
	public $factory = null;

	/**
	 * Konstruktor
	 */
	public function __construct() {
		$this->factory = new fl_factory();
	}

	/**
	 * Datenzugriff setzen
	 *
	 * @param data_access $data_access
	 */
	public function set_data_access(data_access $data_access) {
		$this->factory->set_data_access($data_access);
	}

	/**
	 * Flashnachrichten starten
	 */
	public function start_flash() {
		$this->flash = new fl_flash();
	}

	/**
	 * Federleicht anhalten
	 */
	public function stop($message='') {
		echo $message;
		exit();
	}
}
?>
