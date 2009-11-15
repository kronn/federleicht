<?php
/**
 * URL-Analyse
 *
 * @package federleicht
 * @subpackage base
 * @version 0.3
 */
/**
 * Dispatcher-Klasse
 */
class fl_dispatcher {
	/**
	 * Speicher für die Routen
	 */
	protected $routes = array();

	/**
	 * Referenz auf die Language-Klasse
	 */
	protected $lang;
	/**
	 * Array mit der liste der installierten Module
	 */
  public $modules = array();
  /**
   * Suchmaschinenauswertung
   */
  protected $searchengines;

	/**
	 * Dispatcher
	 *
	 * @param fl_lang  $lang    Sprachkonfiguration
	 * @param array    $modules Liste der vorhanden Module
	 */
	public function __construct(fl_lang $lang, array $modules) {
		$this->clean_superglobals();
		$this->lang = $lang;
    $this->modules = $modules;
    $this->searchengines = new fl_searchengines();
	}

	/**
	 * Route hinzufügen
	 *
	 * @param object $route
	 */
	public function add_route($route) {
		$prev_count = count($this->routes);
		$this->routes[] = $route;

		return ( $prev_count < count($this->routes) )? true: false;
	}

	public function set_default_controller($default_controller) {
		throw new Exception('Methode ist veraltet, nicht mehr verwenden.');
	}
	public function get_default_controller() {
		throw new Exception('Methode ist veraltet, nicht mehr verwenden.');
	}

	/**
	 * URL Analysieren
	 *
	 * Die URL wird versucht, mit den verschiedenen Routen in Verbindung 
	 * zu bringen. Die letzte Route wird bei Misserfolg erneut geprüft, um 
	 * die darin gespeicherten Vorgabewerte zu nutzen.
	 *
	 * Die Sprache wird danach versucht zu extrahieren. Entweder aus dem
	 * Feld 'lang' oder aus dem in der Route dafür vorgemerkten Feld.
	 *
	 * Zuletzt wird der controller ggf. auf den Vorgabewert der Anwendung 
	 * gesetzt.
	 *
	 * @param  url   Zu untersuchende URL
	 * @return array $request
	 */
	public function analyse($url){
		$url = preg_replace('@[/]{2,}@', '/', $url); // gegen Unsinn

		$route_success = false;
		usort( $this->routes, array('fl_route', 'compare_routes'));

		foreach ( $this->routes as $route ) {
			if ( $route->try_route($url) ) {
				$request = $route;
				$route_success = true;
				break;
			} else {
				continue;
			}
		}

		if ( $route_success === false ) {
			$last_route = array_pop($this->routes);
			$last_route->try_route($url, true);

			$request = $last_route;
			$route_success = true;
		}

		// Für Vergleichszwecke wird die Route in ein Array exportiert
		$tmp_req = $request->get_request();

		if ( isset( $tmp_req['lang'] ) ) {
			$this->lang->set( $tmp_req['lang'] );
		} else {
			$this->lang->set( $request->get_language_key() );
		}

		if ( $tmp_req['controller'] === 'defaultController' ) {
			throw new RuntimeException('Es konnte kein passender Controller gefunden werden');
		}

		if ( $tmp_req['modul'] === '&controller' ) {
			$request->set_modul($tmp->req['controller']);
    }

    $this->searchengines->analyze();
    $request->set_searches(
      $this->searchengines->searchwords,
      $this->searchengines->searchphrase
    );

		return $request;
	}

	/**
	 * Superglobale bereinigen
	 */
	protected function clean_superglobals() {
		if(get_magic_quotes_gpc()) {
			$this->arrayStripSlashes($_GET);
			$this->arrayStripSlashes($_POST);
			$this->arrayStripSlashes($_COOKIE);
		}
	}

	/**
	 * Stripslashes verallgemeinert (Strings und Arrays)
	 */
	public function arrayStripSlashes(&$var) {
		if ( is_string($var) ) {
			$var = stripslashes($var);
		} elseif( is_array($var) ) {
			foreach( $var AS $key => $value ) {
				$this->arrayStripSlashes($var[$key]);
			}
		}
	}
}
