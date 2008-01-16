<?php
/**
 * URL-Analyse
 *
 * @package federleicht
 * @subpackage base
 * @version 0.1.8
 */
/**
 * Dispatcher-Klasse
 */
class dispatcher {
	/**
	 * Speicher für die Routen
	 */
	var $routes = array();

	/**
	 * Referenz auf die Language-Klasse
	 */
	var $lang;
	/**
	 * Array mit der liste der installierten Module
	 */
	var $modules = array();

	/**
	 * Standardcontroller
	 */
	var $default_controller;

	/**
	 * Dispatcher
	 *
	 * @param object $db     Datenbankobjekt
	 * @param array  $lang   Sprachkonfiguration
	 */
	function dispatcher($lang) {
		$this->clean_superglobals();
		$this->lang = new lang($lang['default'], $lang['all']);
	}

	/**
	 * Route hinzufügen
	 *
	 * @param object $route
	 */
	function add_route($route) {
		$this->routes[] = $route;
	}

	/**
	 * DefaultController setzen
	 *
	 * @param string $default_controller
	 */
	function set_default_controller($default_controller) {
		$this->default_controller = (string) $default_controller;
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
	function analyse($url){
		$url = preg_replace('@[/]{2,}@', '/', $url); // gegen Unsinn

		$route_success = FALSE;
		usort( $this->routes, array('route', 'compare_routes'));

		$request = array();

		foreach ( $this->routes as $route ) {
			if ( $route->try_route($url) ) {
				$request = $route->get_request();
				$route_success = $route;
				break;
			} else {
				continue;
			}
		}
		if ( $route_success === FALSE ) {
			$last_route = array_pop($this->routes);
			$last_route->try_route($url, TRUE);

			$request = $last_route->get_request();
			$route_success = $last_route;
		}

		if ( isset( $request['lang'] ) ) {
			$this->lang->set($request['lang']);
		} else {
			$this->lang->set( $route_success->get_language_key() );
		}

		# $request['_url'] = $route_success->get_current_url();

		if ( $request['controller'] === 'defaultController' ) {
			$request['controller'] = $this->default_controller;
			# $request['_url'] = str_replace('defaultController', $this->default_controller, $_url);
		}

		if ( $request['modul'] === 'defaultController' ) {
			$request['modul'] = $request['controller'];
		}

		if ( !in_array($request['modul'], $this->modules) ) {
			$request = array(
				'modul'=>$this->default_controller,
				'controller'=>$this->default_controller,
				'action'=>'defaultAction',
				'param'=>''
			);
		}

		return $request;
	}

	/**
	 * Superglobale bereinigen
	 */
	function clean_superglobals() {
		if(get_magic_quotes_gpc()) {
			$this->_arrayStripSlashes($_GET);
			$this->_arrayStripSlashes($_POST);
			$this->_arrayStripSlashes($_COOKIE);
		}
	}

	/**
	 * Stripslashes verallgemeinert (Strings und Arrays)
	 */
	function _arrayStripSlashes(&$var) {
		if ( is_string($var) ) {
			$var = stripslashes($var);
		} elseif( is_array($var) ) {
			foreach( $var AS $key => $value ) {
				$this->_arrayStripSlashes($var[$key]);
			}
		}
	}
}
?>
