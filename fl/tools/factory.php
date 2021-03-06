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
	public $inflector = null;

	/**
	 * Klassenkonstanten
	 */
	const ONLY_MODULES = 'only_modules';
	const AR_CLASSNAME = 'ar_classname';

	/**
	 * Konstruktor
	 */
	public function __construct() {
		$this->registry = fl_registry::getInstance();
		$this->structures = new fl_data_structures();

		$this->inflector = new fl_inflector(
			$this->registry->get('config', 'inflections')
		);
	}

	/**
	 * Datenzugriff setzen
	 *
	 * @param data_source_access $data_access
	 */
	public function set_data_access(data_source_access $data_access) {
		$this->data_access = $data_access;
	}

	public function get_inflector() {
		return $this->inflector;
	}
	public function get_data_access() {
		return $this->data_access;
	}
	
	/**
	 * Module und Helper-Klassen suchen
	 *
	 * return array
	 */
	public function search_application_classes() {
		$classes = array(
			'helpers'=>$this->search_helpers(),
			'modules'=>$this->search_modules()
		);

		return $classes;
	}
	/**
	 * Eine Federleicht-interne Klasse erzeugen und zurückgeben
	 *
	 * @param string $class_name
	 * @return mixed
	 */
	public function create($class_name) {
		$class = "fl_{$class_name}";

		if ( !class_exists($class) ) {
			// Der Autoloader wird von class_exists in diesem Fall aufgerufen.
			// Wenn wir also hier sind, wurde die Klassendatei nicht gefunden.
			throw new LogicException("Klasse '$class' nicht gefunden");
		}

		$args = func_get_args();

		// Den erste Parameter "löschen", da er den Klassennamen selbst enthält
		// und folglich keinen Parameter zur Erzeugung dieser Klasse darstellt.
		array_shift($args);
				
		// Reflection-Eigenschaften von PHP5 nutzen...
		// Mehr: http://www.php.net/manual/en/language.oop5.reflection.php
		$reflection = new ReflectionClass($class);
		return $reflection->newInstanceArgs($args); 
	}

	/**
	 * Modul erzeugen zu zurückgeben
	 *
	 * @param string $modul
	 * @param fl_functions $functions
	 */
	public function get_modul($modul, fl_functions $functions) {
		require_once $this->registry->get('path', 'module') . $modul . '/modul.php';

		$modul_name = $modul . '_modul';
		return new $modul_name($this->get_data_access(), $functions);
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
		list($modul, $class_name) = $this->parse_class_name($class);
		$this->load_class($modul, $class_name);

		$args = func_get_args();

		array_shift($args);
		$reflection = new ReflectionClass($class_name);
		return $reflection->newInstanceArgs($args); 
	}

	/**
	 * ActiveRecord-Loader holen
	 *
	 * @pattern LazyLoad
	 * @param string $type
	 * @param string $class
	 * @param array  $keys
	 * @param array  $data
	 * @param array  $options
	 * @return data_loader
	 */
	public function get_loader($type, $class, array $keys, array $data = array(), array $options = array() ) {
		$loader = $this->create(
			'data_loader_'.$type['loader'].'_'.$type['relation'], 
			$class, $keys, $data, $options
		);

		$loader->set_factory($this);

		return $loader;
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
		$class = (string) $class;
		$id = (integer) $id;

		list($modul, $class_name) = $this->parse_class_name($class, self::AR_CLASSNAME);

		$identifier = "{$class_name}_{$id}";

		if ( !$this->registry->is_set('loaded_record_'.$identifier) ) {
			$this->load_class($modul, $class_name);

			$data = (array) $data;
			if (!empty($data) and $id != 0) {
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

			$instance = new $class_name(
				$this->data_access, 
				$this->inflector->plural($class_name),
				$id, 
				$data_structure,
				$loaded
			);

			// i don't want to save an ar_object in 
			// the registry/identity map without a database id 
			if ( $id == 0 ) return $instance; 

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

		$args = func_get_args();

		array_shift($args);
		$reflection = new ReflectionClass($wanted_helper);
		return $reflection->newInstanceArgs($args); 
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
		} else {
			list($modul, $structure) = explode('/', $wanted_structure, 2);
			$this->structures->load_structure($modul, $structure);
		}
	}

	/**
	 * Klassendatei einlesen
	 *
	 * @param string $modul
	 * @param string $class
	 */
	public function load_class($modul, $class) {
		$file = $this->registry->get('path', 'module') . $modul . '/classes/'.$class.'.php';

		if ( file_exists($file) ) {
			require_once $file;
		} else {
			throw new Exception('Klassendatei "'.$modul.'/classes/'.$class.'" konnte nicht gefunden werden.');
		}
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
		$prefixes = array(
			$this->registry->get('path', 'lib').'builtin/helper/',
			$this->registry->get('path', 'helper')
		);

		foreach( $prefixes as $prefix ) {
			$clean_prefix = substr($prefix, strlen(FL_ABSPATH));
			if ( !in_array( $clean_prefix . $wanted, $this->registry->get('helpers') ) ) {
				continue;
			}

			include_once $prefix . $wanted . '.php';

			return TRUE;
		}

		// Wenn man hierhin gelangt, wurde die Helperklasse nicht gefunden.
		throw new OutOfRangeException("Helperklasse {$wanted} nicht gefunden");
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
	 * @param string  $class
	 * @param mixed $restrictions  sollte fl_factory::ONLY_MODULES oder fl_factory::AR_CLASSNAME sein
	 * @return array
	 */
	public function parse_class_name($class, $restrictions = null) {
		$common = 'common';
		$builtin = 'builtin';

		if ( strpos($class, '/') === false) {
			if ( in_array($restrictions, array(self::ONLY_MODULES, self::AR_CLASSNAME) ) ) {
				throw new InvalidArgumentException('Klassenname muss in der Form modul/class angegeben werden.');
			}
			$result = array($common, $class);
		} else {
			list($modul, $class) = explode('/', $class, 2);
			if ( $restrictions == self::AR_CLASSNAME ) {
				if (! $this->inflector->is_singular($class)) {
					$class = $this->inflector->singular($class);
				}
			}
			$result = array($modul, $class);
		}

		return $result;
	}

	/**
	 * Nach Modulen suchen
	 *
	 * Das Verzeichnis modulepath wird auf entsprechende Dateien
	 * untersucht. Die Liste der gefundenen Module wird zurück-
	 * gegeben.
	 *
	 * @return array
	 */
	protected function search_modules() {
		$modules = glob( $this->registry->get('path', 'module') . '*/modul.php');
		$installed_modules = array();

		if ( !is_array($modules) ) return $installed_modules;

		foreach ($modules as $module) {
			$installed_modules[] = preg_replace('#'.addslashes( $this->registry->get('path', 'module') ).'([-_a-z0-9]+)/modul.php#','$1',$module);
		}

		return $installed_modules;
	}

	/**
	 * Nach Helfermodulen suchen
	 *
	 * Das Verzeichnis helper wird auf entsprechende Dateien
	 * untersucht. Die Liste der gefundenen Helfer wird zurück-
	 * gegeben.
	 *
	 * @return array
	 */
	protected function search_helpers() {
		$app_helper_path = $this->registry->get('path', 'helper');
		$app_helpers = (array) glob( $app_helper_path . '*.php');

		$framework_helper_path = $this->registry->get('path', 'lib') . 'builtin/helper/';
		$framework_helpers = (array) glob( $framework_helper_path . '*.php');

		$installed_helpers = array();

		$helpers = array_merge($app_helpers, $framework_helpers);

		foreach ($helpers as $helper) {
			$helper = substr($helper, strlen(FL_ABSPATH));
			$installed_helpers[] = preg_replace('#([-_a-z0-9]+)\.php#','$1',$helper);
		}

		return $installed_helpers;
	}
}
