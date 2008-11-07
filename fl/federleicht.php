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
 * klasse und die Funktionenklasse, die an die meisten nachfolgenden 
 * Objekte weitergegeben wird.
 *
 * öffentliche Schnittstelle:
 *
 *   federleicht->__construct($url)
 *   federleicht->start()
 *   federleicht->stop()
 */
class federleicht {
	/**
	 * Objektreferenzen
	 */
	public $datamodel;
	public $functions;
	public $registry;

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
	public function __construct($url='') {
		if ( !defined('ABSPATH') ) {
			$abspath = realpath(dirname(__FILE__) . '/../');
			define('ABSPATH', $abspath . '/');
		}

		$path = array(
			'lib'=>ABSPATH . 'fl/',
			'app'=>ABSPATH . 'app/',
			'module'=>ABSPATH . 'app/modules/',
			'layouts'=>ABSPATH . 'app/modules/common/layouts/',
			'helper'=>ABSPATH . 'app/helper/',
			'elements'=>ABSPATH . 'app/elements/',
		);

		$this->import_classes($path);

		$this->registry = fl_registry::getInstance();
		$this->registry->set('url', (string) $url);
		$this->registry->set('path', $path);

		$config = $this->read_config();
		$this->registry->set('config', $config);

		$this->functions = new fl_functions();
		$classes = $this->functions->factory->search_application_classes();

		$this->registry->set('modules', $classes['modules']);
		$this->registry->set('helpers', $classes['helpers']);

		if ( count($classes['modules']) == 0 ) {
			$this->functions->stop('<h2>Fehler</h2><p>Keine Module installiert</p>');
		}

		$data = new fl_data_access($this->registry->get('config', 'database'));

		$this->datamodel = $data->get_data_source();
		$this->functions->set_data_access($this->datamodel);
	}

	/**
	 * Federleicht starten
	 *
	 * Der Dispatcher geladen. Nach der URL-Analyse wird das
	 * entsprechende Modul geladen und gestartet.
	 */
	public function start() {
		$this->start_session();

		$this->functions->start_flash();

		/**
		 * @deprecated
		 * @todo entfernen, sobald das config-verzeichnis im svn ist
		 * @todo jede Referenz auf die Konstante DEFAULTSECTION entfernen
		 */
		if ( !defined('DEFAULTSECTION') ) {
			$result = $this->datamodel->retrieve(
				ADMINMODULE.'_options','value',
				"optionname = 'DEFAULTSECTION'", '', '1');
			define('DEFAULTSECTION', $result['value']);
		}

		$lang = $this->registry->get('config', 'lang');
		$dispatcher = new fl_dispatcher(
			new fl_lang($lang['default'], $lang['all']),
			$this->registry->get('modules')
		);
		/**
		 * @deprecated, siehe oben, zeile 93
		 */
		$dispatcher->set_default_controller(DEFAULTSECTION);

		foreach( $this->registry->get('config', 'routes') as $route ) {
			$dispatcher->add_route( $route );
		}

		$request = $this->functions->factory->get_structure(
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
	private function start_session() {
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
	private function import_classes(array $path) {
		require_once  $path['lib'] . 'tools/autoload.php';

		$interfaces = array(
			'data_access',
			'data_wrapper'
		);

		foreach ($interfaces as $interface) {
			require_once $path['lib'] . 'interfaces/'. $interface . '.php';
		}

		return;
	}

	/**
	 * Konfiguration einlesen
	 * 
	 * Die Konfigurationsdateien werden eingelesen und deren 
	 * Inhalt als Array zurückgegeben.
	 *
	 * @return array
	 */
	private function read_config() {
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
		if ( !in_array(ABSPATH.'config/database.ini', $configfiles) ) {
			die('Keine Datenbankkonfiguration angegeben. 
				
				Mit type=null kann auf eine Datenbank verzichtet werden.');
		}

		/**
		 * Routen einlesen
		 */
		require_once ABSPATH . 'config/routes.conf.php';

		/**
		 * Inflector-Definitionen einlesen
		 */
		require_once ABSPATH. 'config/Inflector.conf.php';

		return (array) $config;
	}

	/**
	 * Federleicht anhalten
	 */
	public function stop() {
		$this->functions->stop();
	}
}
