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
class fl_factory {
	/**
	 * Referenzen auf externe Objekte
	 */
	protected $registry = null;
	protected $data_access = null;
	protected $structures = null;
	protected $inflector = null;

	/**
	 * Konstruktor
	 */
	public function __construct() {
		$this->registry = fl_registry::getInstance();
		$this->structures = new fl_data_structures();

		$lang = $this->registry->get('config', 'lang');
		$this->inflector = new fl_inflector($lang['application']);
	}

	/**
	 * Datenzugriff setzen
	 *
	 * @param data_access $data_access
	 */
	public function set_data_access(data_access $data_access) {
		$this->data_access = $data_access;
	}

	/**
	 * Eine Federleicht-interne Klasse erzeugen und zurückgeben
	 *
	 * @todo verbessern, indem die verschiedenen Klassen mit den entsprechenden Parametern versorgt werden oder an passende Methoden deligiert wird...
	 * @param string $class_name
	 * @return mixed
	 */
	public function create($class_name) {
		$class = 'fl_$classname';
		return new $class();
	}

	/**
	 * Weiteres Model holen
	 *
	 * Nach Möglichkeit wird das Model aus der Registry geholt.
	 * @pattern IdentityMap
	 *
	 * @throws LogicException   Wenn keine Datenzugriffsklasse gesetzt wurde.
	 *
	 * @param string $modul     Name des Moduls, aus dem das Model geholt wird.
	 * @return fl_model
	 */
	public function get_model($modul) {
		if (  $this->registry->get('loaded_model_'.$modul) === FALSE ) {
			if ( $this->data_access == null ) { 
				throw new LogicException('Der Factory ist keine Datenzugriffsklasse bekannt: Mit set_data_access(data_access) setzen!');
			}

			$this->load_module($modul);

			$model_name = 'app_' . $modul . '_model';
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
	public function get_class($class) {
		if ( strpos($class, '/') === false) {
			throw new InvalidArgumentException('Klassenname muss in der Form modul/class angegeben werden.');
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
	 * Nach Möglichkeit wird der Record aus der Registry geholt.
	 * @pattern IdentityMap
	 *
	 * @throws InvalidArgumentException  Wenn der erste Parameter keinen Slash enthält
	 *
	 * @param string $class
	 * @param int $id
	 * @param array $data
	 * @return fl_data_structures_activerecord
	 */
	public function get_ar_class($class, $id = 0, array $data = array()) {
		if ( strpos($class, '/') === false) {
			throw new InvalidArgumentException('Klassenname muss in der Form modul/class angegeben werden.');
		} else {
			list($modul, $class_name) = explode('/', $class, 2);
		}

		$this->load_structure('activerecord');
		$this->load_class($modul, $class_name);

		$data = (array) $data;
		if (!empty($data)) {
			$loaded = true;
		} else {
			$loaded = false;
			$data += array('id'=>$id);
		}

		if ( $this->is_structure($modul, $class_name) ) {
			$data_structure = $this->get_structure($modul.'/'.$class_name, $data);
		} else {
			$data_structure = $this->get_structure('data', $data);
		}

		$identifier = "{$class_name}_{$id}";

		if ( !$this->registry->is_set('loaded_record_'.$identifier) === FALSE ) {
			$instance = new $class_name(
				$this->data_access, 
				$this->inflector->plural($class_name),
				$id, 
				$data_structure,
				$loaded
			);

			$this->registry->set('loaded_record_'.$identifier, $instance);
		}
		
		return $this->registry->get('loaded_record_'.$identifier);
	}

	/**
	 * Datenstruktur holen
	 *
	 * @param string $wanted_structure
	 * @param array  $data
	 * @return data_wrapper
	 */
	public function get_structure($wanted_structure, $data = null) {
		return $this->structures->get($wanted_structure, $data);
	}

	/**
	 * Helfermodul holen
	 *
	 * @param string $wanted_helper
	 * Weitere Parameter werden übernommen und an den Konstruktor weitergegeben.
	 *
	 * @return object
	 */
	public function get_helper($wanted_helper) {
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
	public function load_structure($wanted_structure) {
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
	public function load_class($modul, $class) {
		require_once $this->registry->get('path', 'module') . $modul . '/classes/'.$class.'.php';
	}

	/**
	 * Aufruf eines Helfermodul
	 *
	 * Der Quellcode eines namentlich benannten
	 * Helfermoduls wird eingelesen.
	 *
	 * @throws OutOfRangeException  Wenn die gewünschte Helperklasse nicht existiert
	 *
	 * @param string $wanted Name des gewünschten Helfermoduls
	 * @return boolean
	 */
	public function load_helper($wanted) {
		if ( !in_array( $wanted, $this->registry->get('helpers') ) ) {
			throw new OutOfRangeException("Helperklasse {$wanted} nicht gefunden");
		}

		include_once $this->registry->get('path', 'helper') . $wanted . '.php';

		return TRUE;
	}

	/**
	 * Moduldatei einlesen
	 *
	 * @throws OutOfRangeException  Wenn die passende Moduldatei nicht existiert
	 * @param string $modul
	 */
	public function load_module($modul) {
		$modul_path = $this->registry->get('path', 'module') . $modul . '/model.php';

		if ( !in_array($modul, $this->registry->get('modules')) ) {
			$common_path = $this->registry->get('path', 'module') . 'common/models/'.$modul.'.php';

			if ( file_exists( $common_path ) ) {
				$modul_path = $common_path;
			} else {
				throw new OutOfRangeException("Modul {$modul} wurde nicht gefunden");
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
	public function is_structure($modul, $class) {
		return $this->structures->exists($modul, $class);
	}

	/**
	 * ########## interne Funktionen #########################################
	 */

	/**
	 * Klassenidentifikator parsen
	 *
	 * @param string $class
	 * @return array
	 * @access private
	 */
	protected function parse_class_name($class) {
		$common = 'common';
		$builtin = 'builtin';

		if ( strpos($class, '/') === false) {
			$result = array($common, $class);
		} else {
			$result = explode('/', $class, 2);
		}

		return $result;
	}
}
?>
