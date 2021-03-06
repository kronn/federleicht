<?php
/**
 * Datenstrukturen des Federleicht-Frameworks verwalten
 *
 * @version 0.4
 * @author Matthias Viehweger <kronn@kronn.de>
 * @package federleicht
 * @subpackage base
 */
class fl_data_structures {
	protected $libpath;
	protected $modulepath;
	public $built_in;

	/**
	 * Konstruktor
	 */
	public function __construct() {
		$registry = fl_registry::getInstance();
		$this->libpath = $registry->get('path', 'lib');
		$this->modulepath = $registry->get('path', 'module');

		$this->built_in = '%%builtin';

		$this->load_structure($this->built_in, 'data');
		$this->load_structure($this->built_in, 'image');
	}

	/**
	 * Datenstrukturdatei zurueckgeben
	 *
	 * @param string $wanted_structure
	 * @param mixed  $initial_data
	 * @return data_structure
	 */
	public function get($wanted_structure, $initial_data = array()) {
		if ( strpos($wanted_structure, '/') === false) {
			$modul = $this->built_in;
			$name = $wanted_structure;
			$structure_name = 'fl_data_structures_' . $name;

		} else {
			list($modul, $name) = explode('/', $wanted_structure, 2);
			$structure_name = $name . '_data';
		}

		if ( $initial_data == null ) {
			$initial_data = array();
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
	public function load_structure($modul, $name) {
		if ( $modul === $this->built_in ) {
			$file = $this->libpath . 'data/structures/'.$name.'.php';
		} else {
			$file = $this->modulepath . $modul.'/data/'.$name.'.php';
		}

		require_once $file;
	}

	/**
	 * Datenstrukturdatei einlesen
	 *
	 * @param string $wanted_structure
	 * @deprecated
	 */
	public function load($wanted_structure) {
		trigger_error(
			'deprecated, use load_structure($modul, $name) instead',
			E_USER_WARNING
		);

		return $this->load_structure($this->built_in, $wanted_structure);
	}

	/**
	 * Pruefung, ob Datenstrukturdatei existiert
	 *
	 * @param string $modul
	 * @param string $name
	 * @return boolean
	 */
	public function exists($modul, $name) {
		$filename = ( $modul === $this->built_in )?
			$this->libpath . 'data/structures/'.$name.'.php':
			$this->modulepath.$modul.'/data/'.$name.'.php';

		return file_exists($filename);
	}
}
