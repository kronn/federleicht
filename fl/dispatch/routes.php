<?php 
/**
 * Routen
 * 
 * Routen dienen der Zuordnung einer Adresszeile
 * zu einem Modul. 
 *
 * @package federleicht
 * @subpackage base
 * @author Matthias Viehweger <kronn@kronn.de>
 * @version 0.2
 */
class route {
	var $route = '';
	var $regex = '';
	var $modul = '';
	var $defaults = array();
	var $priority = 1;
	var $language_key = '';

	var $default_regex = array();
	var $partial_regex = array();

	/**
	 * Konstruktor
	 *
	 * @param string $route
	 * @param string $regex
	 */
	function route($route, $regex='') {
		$this->route = (string) $route;

		$this->default_regex = array(
			'normal_item'=>'[-_+0-9a-z\|:%,\.]+',
			'last_item'=>'[-_/+0-9a-zA-Z\|:%,\.]+'
		);

		if ( $route === 'regex' AND $regex != '' ) {
			$this->regex = $regex;
		} else {
			$this->regex = $this->compile($route);
		}

		$this->set_priority( 1 );
		$this->set_defaults( array() );
	}

	/**
	 * Route zu regulärem Asudruck umwandeln
	 *
	 * @param string $route
	 * @return $string
	 */
	function compile($route) {
		$elements = explode('/', $route);
		$group_count = 0;

		$beginning = '@^/';
		$route_regex = '';
		$end = '$@';

		foreach( $elements as $key => $value ) {
			if ( empty($value) ) {
				unset($elements[$key]);
			}
		}
		$elements = array_values($elements);

		foreach( $elements as $key => $value ) {
			if ( empty($value) ) continue;

			$is_last = ( $key === ( count($elements) - 1 ) )? TRUE: FALSE;

			if ( strpos($value, ':') === 0 ) {
				$name = substr($value, 1);
				$transformed_route = '(?P<'.$name.'>';

				$regex = $this->get_partial_regex($name, $is_last);
				if ( !empty( $regex ) ) { 
					$transformed_route .= $regex;
				} elseif ( $is_last ) {
					$transformed_route .= $this->default_regex['last_item'];
				} else {
					$transformed_route .= $this->default_regex['normal_item'];
				}

				$transformed_route .= ')';

				if ( $group_count > 0 ) {
					$transformed_route = '(?('.$group_count.')' . $transformed_route . '?+)';
				}

				$group_count++;

			} else {
				$transformed_route = $value;
			}

			$route_regex .= $transformed_route;

			if ( !$is_last ) {
				$route_regex .= '(/)?';
				$group_count++;
			}
		}

		$regex = $beginning . $route_regex . $end;
		return $regex;
	}

	/**
	 * Versuchen, Route in URL zu erkennen
	 *
	 * @param string  $url
	 * @param boolean $last_route
	 * @return boolean
	 */
	function try_route($url, $last_route=FALSE) {
		$treffer = array();

		$host = ( isset($_SERVER['HTTP_HOST']) )? $_SERVER['HTTP_HOST']: 'localhost';
		$parsed_url = parse_url('http://'.$host.'/'.ltrim($url,'/'));
		$url_path = $parsed_url['path'];

		$result = preg_match($this->regex, $url_path, $treffer);

		$request = $this->defaults;
		$request['query'] = (isset($parsed_url['query']))? $parsed_url['query']:'';

		foreach ( $treffer as $key => $value ) {
			if ( is_numeric($key) ) continue;
			if ( empty($value) ) continue;

			$request[$key] = $value;
		}

		if ( $url === '/' AND $last_route === TRUE ) {
			$result = 1;
		}
		if ( $this->modul === $this->defaults['controller'] ) {
			$request['modul'] = $request['controller'];
		}

		$this->request = $request;

		$route_success = ( intval($result) > 0 )? TRUE: FALSE;
		return $route_success;
	}

	/**
	 * Defaultwerte setzen
	 *
	 * @param array $defaults
	 * @param string $modul
	 */
	function set_defaults($defaults, $modul=NULL) {
		$this->defaults = array_merge($this->defaults, (array) $defaults);

		if ( is_null($modul) AND isset($this->defaults['controller']) )  {
			$modul = $this->defaults['controller'];
		} else {
			$modul = '';
		}

		$this->set_modul($modul);
	}

	/**
	 * Priorität der Route setzen
	 *
	 * @param integer $priority
	 */
	function set_priority($priority) {
		$this->priority = (integer) $priority;
	}

	/**
	 * Defaultkey für Sprachinformationen setzen
	 *
	 * @param string $key
	 */
	function set_language_key($key) {
		$this->language_key = (string) $key;
	}

	/**
	 * Teilregeln für einzelne Routenbestandteile setzen
	 *
	 * Nach dem setzen der Regeln wird die Route neu kompiliert.
	 * 
	 * @param string $key
	 * @param string $regex
	 */
	function set_partial_regex($key, $regex) {
		$this->partial_regex[$key] = $regex;
		$this->regex = $this->compile($this->route);
	}

	/**
	 * Modulzugehörigkeit setzen
	 *
	 * @param string $modul
	 */
	function set_modul($modul) {
		$this->modul = (string) $modul;
	}

	/**
	 * Request-Daten holen
	 *
	 * @return array
	 */
	function get_request() {
		$request = array_merge($this->request, array('modul'=>$this->modul));

		return $request;
	}

	/**
	 * Priorität holen
	 *
	 * @return integer
	 */
	function get_priority() {
		return $this->priority;
	}

	/**
	 * Teilregel für eine Routenbestandteil holen
	 *
	 * @param string  $key
	 * @param boolean $id_last
	 * @return string
	 */
	function get_partial_regex($key, $is_last=FALSE) {
		if ( isset( $this->partial_regex[$key] ) ) { 
			$regex = $this->partial_regex[$key];
		} elseif ( $is_last ) {
			$regex = $this->default_regex['last_item'];
		} else {
			$regex = $this->default_regex['normal_item'];
		}

		return $regex;
	}

	/**
	 * Sprachschlüssel holen
	 *
	 * Entweder ist der Sprachschlüssel mit $this->set_language_key 
	 * gesetzt worden, oder es wird der letzte Eintrag der Default-
	 * Werte verwendet.
	 *
	 * @return string
	 */
	function get_language_key() {
		if ( isset( $this->language_key ) AND !empty($this->language_key)  ) {
			return $this->language_key;
		} else {
			return array_pop(array_keys($this->defaults));
		}
	}

	/**
	 * URL erzeugen, die der aktuellen Route entspricht
	 *
	 * @param  string $route
	 * @param  array  $parts
	 * @return string
	 */
	function make_url($route, $parts) {
		$elements = explode('/', $route);

		$url = '';

		foreach( $elements as $key => $value ) {
			if ( empty($value) ) {
				unset($elements[$key]);
			}
		}
		$elements = array_values($elements);

		foreach( $elements as $key => $value ) {
			if ( empty($value) ) continue;

			if ( strpos($value, ':') === 0 ) {
				$name = substr($value, 1);

				$part = $parts[$name]; 
				if ( !empty( $part ) ) { 
					$url .= $part;
				} else {
					$url .= $this->defaults[$name];
				}

			} else {
				$url = $value;
			}

			$url .= '/';
		}

		return $url;
	}

	/**
	 * URL zurückgeben, die zur aktuellen Route passt
	 *
	 * @return string
	 */
	function get_current_url() {
		return $this->make_url($this->route, $this->request);
	}


	/**
	 * Vergleichsfunktion zur Sortierung von Routen
	 *
	 * Kann mit usort($array_of_routes, array('route', 'compare_routes');
	 * verwendet werden.
	 *
	 * @param route $a
	 * @param route $b
	 * @return integer
	 */
	function compare_routes($a, $b) {
		$ap = $a->get_priority();
		$bp = $b->get_priority();

		if ( $ap == $bp ) {
			$result = 0;
		} else {
			$result = ( $ap > $bp )? 1: -1;
		}
		return $result;
	}
}
