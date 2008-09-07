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
 * @version 0.3
 */
class fl_route {
	protected $route = '';
	protected $regex = '';
	protected $modul = '';
	protected $defaults = array();
	protected $priority = 1;
	protected $language_key = '';

	protected $default_regex = array();
	protected $partial_regex = array();

	/**
	 * Konstruktor
	 *
	 * @param string $route
	 */
	public function __construct($route) {
		$this->route = (string) $route;

		$this->default_regex['normal_item']='[-_0-9a-z\.]+';
		$this->default_regex['last_item']='[-_/0-9a-zA-Z%\.]+';

		$this->regex = $this->compile($route);

		$this->set_priority( 1 );
		$this->set_defaults( array() );
	}

	/**
	 * Vereinfachte Objekterzeugung
	 *
	 * @pattern facade
	 *
	 * @param string $route
	 * @param string $defaults
	 * @param int    $priority
	 * @param array  $partial_regex
	 * @return fl_route
	 */
	public static function get_instance($route, $defaults, $priority, array $partial_regex=array()) {
		$route_object = new self($route);
		
		$defaults = is_string($defaults)? 
			fl_converter::string_to_array($defaults): 
			$defaults;
		$route_object->set_defaults($defaults);

		$route_object->set_priority((int) $priority);
		$route_object->set_language_key('lang');

		foreach ( $partial_regex as $part ) {
			$route_object->set_partial_regex($part['key'], $part['regex']);
		}

		return $route_object;
	}

	/**
	 * Route zu regulärem Asudruck umwandeln
	 * 
	 * @param string $route 
	 * @return $string
	 */
	protected function compile($route) {
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
	public function try_route($url, $last_route=FALSE) {
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
	public function set_defaults(array $defaults, $modul=NULL) {
		$this->defaults = array_merge($this->defaults, $defaults);

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
	public function set_priority($priority) {
		$this->priority = (integer) $priority;
	}

	/**
	 * Defaultkey für Sprachinformationen setzen
	 *
	 * @param string $key
	 */
	public function set_language_key($key) {
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
	public function set_partial_regex($key, $regex) {
		$this->partial_regex[$key] = $regex;
		$this->regex = $this->compile($this->route);
	}

	/**
	 * Modulzugehörigkeit setzen
	 *
	 * @param string $modul
	 */
	public function set_modul($modul) {
		$this->modul = (string) $modul;
	}

	/**
	 * Request-Daten holen
	 *
	 * @return array
	 */
	public function get_request() {
		$request = array_merge($this->request, array('modul'=>$this->modul));

		return $request;
	}

	/**
	 * Priorität holen
	 *
	 * @return integer
	 */
	public function get_priority() {
		return $this->priority;
	}

	/**
	 * Teilregel für eine Routenbestandteil holen
	 *
	 * @param string  $key
	 * @param boolean $is_last
	 * @return string
	 */
	public function get_partial_regex($key, $is_last = false) {
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
	public function get_language_key() {
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
	public function make_url($route, array $parts) {
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
	public function get_current_url() {
		return $this->make_url($this->route, $this->request);
	}


	/**
	 * Vergleichsfunktion zur Sortierung von Routen
	 *
	 * Kann mit usort($array_of_routes, array('fl_route', 'compare_routes');
	 * verwendet werden.
	 *
	 * @param fl_route $a
	 * @param fl_route $b
	 * @return integer
	 */
	public function compare_routes(fl_route $a, fl_route $b) {
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
