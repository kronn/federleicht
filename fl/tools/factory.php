<?php
/**
 * Federleicht-Factory
 *
 * Sammlung aller Objekterzeugungsmethoden, um einen
 * zentralen Ort dafür zu haben
 *
 * @package federleicht
 * @subpackage base
 */
class factory {
	/**
	 * Referenzen auf externe Objekte
	 */
	var $registry = null;
	var $data_access = null;
	var $structures = null;
	var $inflector = null;

	/**
	 * Konstruktor
	 */
	function factory() {
		$this->registry =& registry::getInstance();
		$this->structures = new structures();

		$lang = $this->registry->get('config', 'lang');
		$this->inflector = new inflector($lang['application']);
	}

	/**
	 * Datenzugriff setzen
	 *
	 * @param data_access $data_access
	 */
	function set_data_access($data_access) {
		$this->data_access = &$data_access;
	}

	/**
	 * Weiteres Model holen
	 *
	 * Nach Möglichkeit wird das Model aus der Registry geholt.
	 *
	 * @param string $modul     Name des Moduls, aus dem das Model geholt wird.
	 * @return model
	 */
	function get_model($modul) {
		if (  $this->registry->get('loaded_model_'.$modul) === FALSE ) {
			if ( $this->data_access == NULL ) { 
				return FALSE;
			}

			$this->load_module($modul);

			$model_name = $modul.'_model';
			$get_model = new $model_name(
				$this->data_access, 
				$this, 
				(string) $this->registry->get('path', 'module'));

			$this->registry->set('loaded_model_'.$modul, $get_model);
		}
		
		return $this->registry->get('loaded_model_'.$modul);
	}

	/**
	 * Klasse holen
	 *
	 * @param string $class
	 * @param array  $options (dynamic)
	 * @return object
	 */
	function get_class($class) {
		if ( strpos($class, '/') === false) {
			return false;
		} else {
			list($modul, $class_name) = explode('/', $class, 2);
		}

		$this->load_class($modul, $class_name);

		$instance = new $class_name();
		if ( func_num_args() > 1 ) {
			$args = func_get_args();
			array_shift($args);
			$options = array_values($args);

			$instance->set_options($options);
		}

		return $instance;
	}

	/**
	 * Activerecord-Klasse holen
	 *
	 * @param string $class
	 * @param int $id
	 * @param array $data
	 * @return active_record
	 */
	function get_ar_class($class, $id = 0, $data = array()) {
		if ( strpos($class, '/') === false) {
			return false;
		} else {
			list($modul, $class_name) = explode('/', $class, 2);
		}

		$this->load_structure('active_record');
		$this->load_class($modul, $class_name);

		$data = (array) $data;
		if (!empty($data)) {
			$loaded = true;
		} else {
			$loaded = false;
		}

		if ( $this->is_structure($modul, $class_name) ) {
			$data_structure = $this->get_structure($modul.'/'.$class_name, $data);
		} else {
			$data_structure = $this->get_structure('data', $data);
		}

		$instance = new $class_name(
			$this->data_access, 
			$this->inflector->plural($class_name),
			$id, 
			$data_structure,
			$loaded
		);

		return $instance;
	}

	/**
	 * Datenstruktur holen
	 *
	 * @param string $wanted_structure
	 * @param array  $data
	 * @return data_structure
	 */
	function get_structure($wanted_structure, $data = null) {
		return $this->structures->get($wanted_structure, $data);
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
		$this->load_helper($wanted_helper);

		switch ( func_num_args() ) {
		case 3:
			$f1 = func_get_arg(1);
			$f2 = func_get_arg(2);
			$helper = new $wanted_helper($f1, $f2);
			break;

		case 2:
			$f1 = func_get_arg(1);
			$helper = new $wanted_helper($f1);
			break;

		case 1:
		default:
			$helper = new $wanted_helper();
		}

		return $helper;
	}


	/**
	 * ############# Funktionen zum Einlesen der entsprechenden Dateien ######
	 */


	/**
	 * Datenstruktur einlesen
	 *
	 * @param string $wanted_structure
	 */
	function load_structure($wanted_structure) {
		if ( strpos($wanted_structure, '/') === false ) {
			$this->structures->load_structure($this->structures->built_in, $wanted_structure);
		}
	}

	/**
	 * Klassendatei einlesen
	 *
	 * @param string $modul
	 * @param string $class
	 */
	function load_class($modul, $class) {
		if ( $modul === 'common' ) {
			$class_path = $this->registry->get('path', 'app') . 'classes/'. $class . '.php';
		} else {
			$class_path = $this->registry->get('path', 'module') . $modul . '/classes/'.$class.'.php';
		}

		require_once $class_path;
	}

	/**
	 * Aufruf eines Helfermodul
	 *
	 * Der Quellcode eines namentlich benannten
	 * Helfermoduls wird eingelesen.
	 *
	 * @param string $wanted Name des gewünschten Helfermoduls
	 * @return boolean
	 */
	function load_helper($wanted) {
		if ( !in_array( $wanted, $this->registry->get('helpers') ) ) {
			return FALSE;
		}

		include_once $this->registry->get('path', 'app') . 'helper/' . $wanted . '.php';

		return TRUE;
	}

	/**
	 * Moduldatei einlesen
	 *
	 * @param string $modul
	 */
	function load_module($modul) {
		$modul_path = $this->registry->get('path', 'module') . $modul . '/model.php';

		if ( !in_array($modul, $this->registry->get('modules')) ) {
			$common_path = $this->registry->get('path', 'module') . 'common/models/'.$modul.'.php';

			if ( file_exists( $common_path ) ) {
				$modul_path = $common_path;
			} else {
				return FALSE;
			}
		}

		require_once $modul_path;
	}



	/**
	 * ########## Funktionen, die auf Existenz der entsprechenden Dateien ####
	 */


	/**
	 * Pruefung, ob Datenstruktur existiert
	 *
	 * @param string $modul
	 * @param string $class
	 * @return boolean
	 */
	function is_structure($modul, $class) {
		return $this->structures->exists($modul, $class);
	}
}
?>
