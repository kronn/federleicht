<?php
/**
 * Datenstrukturen des Federleicht-Frameworks verwalten
 *
 * @version 0.1
 * @author Matthias Viehweger <kronn@kronn.de>
 * @package federleicht
 * @subpackage base
 */
class structures {
	var $libpath;

	/**
	 * Konstruktor
	 */
	function structures() {
		$registry =& registry::getInstance();
		$this->libpath = $registry->get('path', 'lib');
	}

	/**
	 * Datenstrukturdatei zurückgeben
	 *
	 * @param string $wanted_structure
	 * @param array  $initial_data
	 * @return data_structure
	 */
	function get($wanted_structure, $initial_data = null) {
		$this->load($wanted_structure);

		return new $wanted_structure($initial_data);
	}

	/**
	 * Datenstrukturdatei einlesen
	 *
	 * @param string $wanted_structure
	 */
	function load($wanted_structure) {
		$file = $this->libpath . 'data/structures/'.$wanted_structure . '.php';
		require_once $file;
	}
}
