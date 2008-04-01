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
	var $flash = null;
	var $data_access = null;
	var $registry = null;
	var $factory = null;

	/**
	 * Konstruktor
	 */
	function __construct() {
		$this->registry = fl_registry::getInstance();
		$this->factory = new fl_factory();
		$this->structures = $this->factory->structures;
	}

	/**
	 * Datenzugriff setzen
	 *
	 * @param data_access $data_access
	 */
	function set_data_access(data_access $data_access) {
		$this->data_access = $data_access;
		$this->factory->set_data_access($data_access);
	}

	/**
	 * Factory zurueckgeben
	 *
	 * @param data_access $data_access
	 * @return factory
	 * @todo intern gespeicherte Factory aufgeben, nur noch neue factory zurueckgeben.
	 */
	function get_factory() {
		if ( is_a($this->factory, 'factory') ) {
			$factory = $this->factory;
		} else {
			$factory = new factory();
		}

		$factory->set_data_access($this->data_access);

		return $factory;
	}

	/**
	 * Aufruf eines Helfermodul
	 *
	 * @param string $wanted Name des gewünschten Helfermoduls
	 * @return boolean
	 * @todo entfernen, wird von factory uebernommen
	 */
	function needs($wanted) {
		trigger_error('veraltet. neu: factory->load_helper($arg)');
		return $this->factory->load_helper($wanted);
	}

	/**
	 * Helfermodul holen
	 *
	 * @param string $wanted_helper
	 * Weitere Parameter werden übernommen und an den Konstruktor weitergegeben.
	 *
	 * @return mixed
	 * @todo entfernen, wird von factory uebernommen
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
	 * @todo entfernen, wird von factory uebernommen
	 */
	function get_model($modul) {
		trigger_error('veraltet. neu: factory->get_model($arg)');
		return $this->factory->get_model($modul);
	}

	/**
	 * Moduldatei einlesen
	 *
	 * @param string $modul
	 * @todo entfernen, wird von factory uebernommen
	 */
	function load_module($modul) {
		trigger_error('veraltet. neu: factory->load_module($arg)');
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
		echo $message;
		exit();
	}
}
?>
