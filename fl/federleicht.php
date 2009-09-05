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
	 * Statistik-Variablen
	 */
	private $execution_time = 0;

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
		if ( !defined('FL_ABSPATH') ) {
			$abspath = realpath(dirname(__FILE__) . '/../') . '/';
			define('FL_ABSPATH', $abspath);
		}

		$path = array(
			'lib'=>FL_ABSPATH . 'fl/',
			'app'=>FL_ABSPATH . 'app/',
			'module'=>FL_ABSPATH . 'app/', // deprecated (offensichtlich doppelt)
			'layouts'=>FL_ABSPATH . 'app/common/layouts/',
			'helper'=>FL_ABSPATH . 'app/common/helper/',
			'elements'=>FL_ABSPATH . 'app/common/elements/',
			'log'=>FL_ABSPATH . 'log/',
		);

		$this->import_classes($path);

		$this->registry = fl_registry::get_instance();
		$this->registry->set( 'url', (string) $url );
		$this->registry->set( 'path', $path );

		$this->functions = new fl_functions();

		$this->registry->set( 'config', $this->read_config() );

		$classes = $this->functions->factory->search_application_classes();

		$this->registry->set('modules', $classes['modules']);
		$this->registry->set('helpers', $classes['helpers']);

		if ( count($classes['modules']) == 0 ) {
			$this->functions->stop('<h2>Fehler</h2><p>Keine Module installiert</p>');
		}

		$this->functions->log('-- ' .$url. ' --');

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

		$lang = $this->registry->get('config', 'lang');
		$dispatcher = new fl_dispatcher(
			new fl_lang($lang['default'], $lang['all']),
			$this->registry->get('modules')
		);

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
		$this->functions->log( "URL => ". fl_converter::array_to_string($request->get('request')) );

		$modul = $this->registry->get('request', 'modul');

		$modul_object = $this->functions->factory->get_modul($modul, $this->functions);
		$modul_object->start_execution();
	}

	/**
	 * Session starten
	 */
	private function start_session() {
		// Einstellungen vornehmen
		// 7 * 24 * 60 * 60 = 604800
		//          40 * 60 =   2400
		ini_set('session.gc_maxlifetime', 604800);
		ini_set('session.use_only_cookies', '1');
		ini_set('session.cookie_httponly', true);

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
			'data_source_access',
			'data_wrapper'
		);

		$exceptions = array(
			'federleicht',
			'sql',
		);

		foreach ($interfaces as $interface) {
			require_once $path['lib'] . 'interfaces/'. $interface . '.php';
		}

		foreach ($exceptions as $exception) {
			require_once $path['lib'] . 'exceptions/'. $exception . '.php';
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
		$config = array();

		$configfiles = glob( FL_ABSPATH . 'config/*.ini');

		foreach( (array) $configfiles as $file) {
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
		if ( !in_array(FL_ABSPATH.'config/database.ini', $configfiles) ) {
			die('Keine Datenbankkonfiguration angegeben.

				Es sollte eine database.ini.example als Vorlage vorhanden sein.
				Mit type=null kann auf eine Datenbank verzichtet werden.');
		}

		/**
		 * PHP-Konfigurationsdateien einlesen
		 */
		foreach( (array) glob(FL_ABSPATH . 'config/*.conf.php') as $file) {
			require_once $file;
		}

		return (array) $config;
	}

	public function execution_time($time) {
		$this->execution_time = $time;
	}

	/**
	 * Federleicht anhalten
	 */
	public function stop() {
		if ( $this->datamodel instanceof fl_data_access_database ) {
			$db_stats = $this->datamodel->export_query_stats();
			$this->functions->log(
				'DB: '.$db_stats['count'].' Queries, '.$this->format_time($db_stats['time']).'s, ' .
				'Total time: '.$this->format_time($this->execution_time).'s = '.
				substr(round(1/($this->execution_time + 0.0000001), 5),0,4)
				.' Requests per Second.',
				fl_logger::WITHOUT_TIME
			);
		} else {
			$this->functions->log(
				'Total time: '.$this->format_time($this->execution_time).'s = '.
				substr(round(1/($this->execution_time + 0.0000001), 5),0,4)
				.' Requests per Second.',
				fl_logger::WITHOUT_TIME
			);
		}
		$this->functions->stop();
	}

	protected function format_time($time) {
		return substr(round($time, 5), 0, 7);
	}
}
