<?php
/**
 * Basisklassen für alle Module
 *
 * @package federleicht
 * @subpackage base
 */
class modul {
	/**
	 * Instanzvariablen
	 */
	var $cap = array();
	var $name = null;

	/**
	 * Speicher für erstellte Objekte
	 */
	var $controller;
	var $model;
	var $view;

	/**
	 * Referenzen auf benötigte externe Objekte und Daten
	 */
	var $datamodel;
	var $functions;
	var $registry;

	var $modulepath;
	var $apppath;

	/**
	 * Modul-Konstruktor
	 *
	 * @param data_access  $data_access
	 * @param functions $functions
	 */
	function modul($data_access, $functions) {
		$this->datamodel = $data_access;
		$this->functions = $functions;
		$this->registry =& registry::getInstance();

		$this->cap = $this->registry->get('request', 'route');
		$this->modulepath = $this->registry->get('path', 'module');
		$this->apppath = $this->registry->get('path', 'app');

		if ( $this->name === null ) {
			 $this->name = ucfirst( get_class( $this ) );
		}
	}

	/**
	 * Controller erzeugen
	 *
	 * @param string $name Modulname
	 * @return object
	 */
	function create_controller($name) {
		require_once $this->modulepath . $name . '/controller.php';
		$controller_name = $name . '_controller';

		$controller = new $controller_name($this->datamodel, $this->functions, $this->create_model($name));

		return $controller;
	}

	/**
	 * Model erzeugen
	 *
	 * @param string $name Modulname
	 * @return object
	 */
	function create_model($name) {
		return $this->functions->get_model($name);
	}

	/**
	 * View-Objekt erzeugen
	 *
	 * @param string $name Modulname
	 * @param array  Daten
	 * @return object
	 */
	function create_view($name, $data) {
		require_once $this->modulepath . $name . '/view.php';
		$view_name = $name . '_view';

		$view = new $view_name($data, $this->datamodel, $this->functions, $name);
		return $view;
	}

	/**
	 * Modulausführung starten
	 *
	 * Die eigentliche Ausführung des Moduls wird gestartet.
	 * Es werden Controller und View erzeugt und der Standardablauf
	 * ausgeführt.
	 * Einhakpunkte sind:
	 *
	 * $modul->prepare		   Vorbereitende Aktionen für das Modul
	 * $controller->common	   vor allen anderen Aktionen
	 * $controller->alternate	wird ausgeführt, wenn $controller->common FALSE zurückgibt
	 * $modul->clean_up		  Aufräumen nach allen Aktionen
	 *
	 * @pattern "Template Method"
	 */
	function start_execution() {
		$modul_name = str_replace('_modul', '', get_class($this));

		// Modul vorbereiten
		$this->prepare();

		$action = $this->cap['action'];
		$params = $this->cap['param'];

		// Standardablauf ausführen
		$this->controller = $this->create_controller($modul_name);
		$this->model = $this->create_model($modul_name);

		if ( $this->controller->common() ) {
			if ( !isset($action) OR !method_exists($this->controller, $action) ) {
				$action = $this->controller->defaultAction;
			}
			$this->controller->$action($params);
			$data = $this->controller->data;
			$layout = $this->controller->layout;

			$this->registry->set('subview', $this->controller->view);

			$this->view = $this->create_view($modul_name, $data);
			$this->view->render_layout($layout);
		} else {
			$this->controller->alternate();
		}
		$this->clean_up();
	}

	/**
	 * Vorbereitung des Moduls (Hook)
	 *
	 * Das Ergebnis wird nicht geprüft.
	 */
	function prepare() {
		return;
	}

	/**
	 * Aufräumfunktion (Hook)
	 *
	 * $modul->clean_up wird nach allen anderen Funktionen im Modulkontext
	 * aufgerufen und bietet so einen letzten Einhakpunkt für Aktionen.
	 *
	 * Das Ergebnis wird nicht geprüft.
	 */
	function clean_up() {
		return;
	}
}
?>
