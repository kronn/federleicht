<?php
/**
 * Datenstrukturen des Federleicht-Frameworks verwalten
 *
 * @version 0.2
 * @author Matthias Viehweger <kronn@kronn.de>
 * @package federleicht
 * @subpackage base
 */
class structures {
	var $libpath;
	var $modulepath;
	var $built_in;

	/**
	 * Konstruktor
	 */
	function structures() {
		$registry =& registry::getInstance();
		$this->libpath = $registry->get('path', 'lib');
		$this->modulepath = $registry->get('path', 'module');

		$this->built_in = '%%builtin';
	}

	/**
	 * Datenstrukturdatei zurueckgeben
	 *
	 * @param string $wanted_structure
	 * @param array  $initial_data
	 * @return data_structure
	 */
	function get($wanted_structure, $initial_data = null) {
		if ( strpos($wanted_structure, '/') === false) {
			$modul = $this->built_in;
			$name = $wanted_structure;
			$structure_name = $name;

		} else {
			list($modul, $name) = explode('/', $wanted_structure, 2);
			$structure_name = $name . '_data';
		}

		$this->load_structure($modul, $name);

		return new $structure_name($initial_data);
	}

	/**
	 * Datenstrukturdatei einlesen
	 *
	 * @param string $modul
	 * @param string $name
	 */
	function load_structure($modul, $name) {
		if ( $modul === $this->built_in ) {
			$file = $this->libpath . 'data/structures/'.$name.'.php';
		} else {
			$file = $this->modulepath . '/'.$modul.'/data/'.$name.'.php';
		}

		require_once $file;
	}

	/**
	 * Datenstrukturdatei einlesen
	 *
	 * @param string $wanted_structure
	 */
	function load($wanted_structure) {
		trigger_error(
			'deprecated, use load_structure($modul, $name) instead',
			E_USER_WARNING
		);

		return $this->load_structure($this->built_in, $wanted_structure);
	}
}
