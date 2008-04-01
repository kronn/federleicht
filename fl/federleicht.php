<?php
/**
 * Federleicht Basisklassen
 *
 * @package federleicht
 * @subpackage base
 */
/**
 * Federleicht-Klasse
 *
 * Die Klasse federleicht enthält die vorbereitenden Funktionen für den
 * Anwendungsablauf. Außerdem hält es Referenzen auf die Datenzugriffs-
 * klasse, das aktuelle Modul (sobald es als Objekt existiert) und die
 * Funktionenklasse, die an die meisten nachfolgenden Objekte weiter-
 * gegeben wird.
 */
class federleicht {
	/**
	 * Objektreferenzen
	 */
	var $datamodel;
	var $functions;
	var $registry;

	/**
	 * Federleicht erstellen
	 *
	 * Der Konstruktor lädt die Standardklassen von Federleicht
	 * und sucht nach Modulen und Helfern. Wenn keine Module
	 * gefunden wurden, bricht das Programm ab. Andernfalls wird
	 * Datenbankverbindung aufgebaut.
	 *
	 * @param string $url
	 */
	function __construct($url='') {
		if ( !defined('ABSPATH') ) {
			$abspath = realpath(dirname(__FILE__) . '/../');
			define('ABSPATH', $abspath . '/');
		}

		$path = array(
			'lib'=>ABSPATH . 'fl/',
			'app'=>ABSPATH . 'app/',
			'module'=>ABSPATH . 'app/modules/',
			'helper'=>ABSPATH . 'app/helper/',
			'elements'=>ABSPATH . 'app/elements/',
			'layouts'=>ABSPATH . 'app/layouts'
		);

		$this->import_classes($path);

		$this->registry = fl_registry::getInstance();
		$this->registry->set('url', (string) $url);
		$this->registry->set('path', $path);

		$config = $this->read_config();
		$modules = $this->search_modules();
		$helpers = $this->search_helpers();

		$this->registry->set('config', $config);
		$this->registry->set('modules', $modules);
		$this->registry->set('helpers', $helpers);

		$this->functions = new fl_functions();

		if ( count($modules) == 0 ) {
			$this->functions->stop('<h2>Fehler</h2><p>Keine Module installiert</p>');
		}

		if ( NO_DATABASE ) {
			$data = new fl_data_access( null );
		} else {
			$data = new fl_data_access($this->registry->get('config', 'database'));
		}

		$this->datamodel = $data->get_data_souce();
		$this->functions->set_data_access($this->datamodel);
	}

	/**
	 * Federleicht starten
	 *
	 * Der Dispatcher geladen. Nach der URL-Analyse wird das
	 * entsprechende Modul geladen und gestartet.
	 */
	function start() {
		$this->start_session();

		$this->functions->start_flash();

		if ( !defined('DEFAULTSECTION') ) {
			$result = $this->datamodel->retrieve(
				ADMINMODULE.'_options','value',
				"optionname = 'DEFAULTSECTION'", '', '1');
			define('DEFAULTSECTION', $result['value']);
		}

		$dispatcher = new fl_dispatcher($this->registry->get('config', 'lang'));
		$dispatcher->modules = $this->registry->get('modules');
		$dispatcher->set_default_controller(DEFAULTSECTION);
		foreach( $this->registry->get('config', 'routes') as $route ) {
			$dispatcher->add_route( $route );
		}

		$request = $this->functions->structures->get(
			'request', 
			$dispatcher->analyse(
				$this->registry->get('url')
			) 
		);
		$this->registry->set('request', $request);

		$modul = $this->registry->get('request', 'modul');

		require_once $this->registry->get('path', 'module') . $modul . '/modul.php';

		$modul_name = $modul . '_modul';
		$modul_object = new $modul_name($this->datamodel, $this->functions);
		$modul_object->start_execution();
	}

	/**
	 * Session starten
	 */
	function start_session() {
		// Einstellungen vornehmen
		// 7 * 24 * 60 * 60 = 604800
		//          40 * 60 =   2400
		#ini_set('session.gc_maxlifetime', 2400);
		#ini_set('session.use_only_cookies', '1');
		
		// Session stored in Cookies
		#$this->functions->needs('cookiesession'); 

		// Session starten
		session_start();
	}

	/**
	 * Einbindung der autoload-Funktion
	 *
	 * @param array $path
	 */
	function import_classes(array $path) {
		require_once  $path['lib'] . 'tools/autoload.php';

		$interfaces = array(
			'data_access'
		);

		foreach ($interfaces as $interface) {
			require_once $path['lib'] . 'interfaces/'. $interface . '.php';
		}

		return;
	}

	/**
	 * Nach Modulen suchen und diese einbinden
	 *
	 * Das Verzeichnis modulepath wird auf entsprechende Dateien
	 * untersucht. Die Liste der gefundenen Module wird zurück-
	 * gegeben.
	 *
	 * @return array
	 * @todo in Factory verschieben
	 */
	function search_modules() {
		$modules = glob( $this->registry->get('path', 'module') . '*/modul.php');
		$installed_modules = array();

		if ( !is_array($modules) ) return $installed_modules;


		foreach ($modules as $module) {
			$installed_modules[] = preg_replace('#'.addslashes( $this->registry->get('path', 'module') ).'([-_a-z0-9]+)/modul.php#','$1',$module);
		}

		return $installed_modules;
	}

	/**
	 * Nach Helfermodulen suchen und diese einbinden
	 *
	 * Das Verzeichnis helper wird auf entsprechende Dateien
	 * untersucht. Die Liste der gefundenen Helfer wird zurück-
	 * gegeben.
	 *
	 * @return array
	 * @todo in Factory verschieben
	 */
	function search_helpers() {
		$helpers = glob( $this->registry->get('path', 'helper') . '*.php');
		$installed_helpers = array();

		if ( !is_array($helpers) ) return $installed_helpers;

		foreach ($helpers as $helper) {
			$installed_helpers[] = preg_replace('#'.addslashes($this->registry->get('path', 'helper')).'([-_a-z0-9]+)\.php#','$1',$helper);
		}

		return $installed_helpers;
	}

	/**
	 * Konfiguration einlesen
	 * 
	 * Die Konfigurationsdateien werden eingelesen und deren 
	 * Inhalt als Array zurückgegeben.
	 *
	 * @return array
	 */
	function read_config() {
		$configfiles = glob( ABSPATH . 'config/*.ini');

		if ( empty($configfiles) ) {
			die('Keine Konfigurationsdateien gefunden.');
		}

		$config = array();

		foreach($configfiles as $file) {
			$config += parse_ini_file($file, true);
		}

		/**
		 * Spezielle Behandlung bestimmter Einstellungen
		 */
		// Konstanten setzen
		if ( isset( $config['constants'] ) ) {
			foreach ( $config['constants'] as $key => $value ) {
				define( strtoupper($key), $value );
			}
		}

		// Sprachenliste in Array umwandeln
		if ( isset( $config['lang'] ) ) {
			$config['lang']['all'] = explode( ',', $config['lang']['all'] );
		}

		// Wenn keine Datenbankkonfiguration angegeben ist und auch nicht
		// gesagt wurde, dass keine Datenbank verwendet wird, abbrechen.
		if ( !in_array(ABSPATH.'config/database.ini', $configfiles) AND 
			( !defined('NO_DATABASE') OR NO_DATABASE === false ) ) {
			die('Keine Datenbankkonfiguration angegeben.');
		}

		/**
		 * Routen einlesen
		 */
		require_once ABSPATH . 'config/routes.conf.php';

		return (array) $config;
	}

	/**
	 * Federleicht anhalten
	 */
	function stop() {
		$this->functions->stop();
	}
}
?>
