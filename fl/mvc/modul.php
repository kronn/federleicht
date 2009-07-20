<?php
/**
 * Basisklassen für alle Module
 *
 * @package federleicht
 * @subpackage base
 */
class fl_modul {
	/**
	 * Instanzvariablen
	 */
	protected $cap = array();
	protected $name = null;
	protected $content;

	/**
	 * Speicher für erstellte Objekte
	 */
	protected $controller;
	protected $model;
	protected $view;

	/**
	 * Referenzen auf benötigte externe Objekte und Daten
	 */
	protected $datamodel;
	protected $functions;
	protected $factory;
	protected $registry;

	protected $modulepath;
	protected $apppath;

	/**
	 * Modul-Konstruktor
	 *
	 * @param data_source_access  $data_access
	 * @param functions $functions
	 */
	public function __construct(data_source_access $data_access, $functions) {
		$this->datamodel = $data_access;
		$this->functions = $functions;
		$this->factory = $functions->factory;
		$this->registry = fl_registry::getInstance();

		$this->cap = $this->registry->get('request', 'request');
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
	protected function create_controller($name) {
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
	protected function create_model($name) {
		return $this->factory->get_model($name);
	}

	/**
	 * View-Objekt erzeugen
	 *
	 * @param string $name Modulname
	 * @param array  Daten
	 * @return object
	 */
	protected function create_view($name, array $data) {
		// @todo unsauber! verbessern!!!
		require_once FL_ABSPATH . 'fl/mvc/view.php';

		if ( file_exists($this->modulepath . $name . '/view.php') ) {
			require_once $this->modulepath . $name . '/view.php';
			$view_name = $name . '_view';
		} else {
			$view_name = 'fl_view';
		}

		$view = new $view_name($data, $this->functions, $name);
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
	final public function start_execution() {
		$modul_name = str_replace('_modul', '', get_class($this));

		// Modul vorbereiten
		$this->prepare();

		$action = $this->cap['action'];
		$params = isset( $this->cap['param'] ) ? $this->cap['param'] : '';

		// Standardablauf ausführen
		$this->controller = $this->create_controller($modul_name);
		$this->model = $this->create_model($modul_name);

		try {
			$this->controller->common($params);
			if ( !isset($action) OR !method_exists($this->controller, $action) ) {
				$action = 'defaultAction';
			}
			$this->call_action($action, $params);

			/**
			 * Übergangsweise
			 */
					$response = $this->controller->get_response();

					$this->registry->set('subview', $response->get('subview'));

					$this->view = $this->create_view($modul_name, $response->get('data'));
					$this->contents = $this->view->render_layout($response->get('layout'));
					$this->output_contents();
			/**
			 * Ende Übergangsweise Code
			 */

			/**
			foreach( $this->controller->get_response() as $response ) {
				$view = $this->factory->create_view($response->get_type());
			 */
				/**
				 * Der folgende Code muss von view übernommen und
				 * mit $view->execute($response) ausgeführt werden
				 
					$data = $response->get('data');
					$layout = $response->get('layout');

					$this->registry->set('subview', $response->get('subview'));

					$this->view = $this->create_view($modul_name, $data);
					$this->contents = $this->view->render_layout($layout);
					$this->output_contents();
				 */
			/**
				$view->execute($response);
			}
			 */
		} catch ( Exception $e ) {
			$this->controller->alternate($e);

			$response = $this->controller->get_response();

			$this->registry->set('subview', $response->get('subview'));

			$this->view = $this->create_view($modul_name, $response->get('data'));
			$this->contents = $this->view->render_layout($response->get('layout'));
			$this->output_contents();
		}
		$this->clean_up();
	}

	/**
	 * Vorbereitung des Moduls (Hook)
	 *
	 * Das Ergebnis wird nicht geprüft.
	 */
	protected function prepare() {
		return;
	}
	
	/**
	 * Aufruf der Aktion
	 *
	 * Kann ggf. überschrieben werden, um weitere oder andere Parameter zu ermöglichen
	 */
	protected function call_action($action, $params) {
		$this->controller->$action($params);
	}

	/**
	 * Ausgabe der Daten
	 */
	public function output_contents() {
		echo $this->contents;
	}

	/**
	 * Aufräumfunktion (Hook)
	 *
	 * $modul->clean_up wird nach allen anderen Funktionen im Modulkontext
	 * aufgerufen und bietet so einen letzten Einhakpunkt für Aktionen.
	 *
	 * Das Ergebnis wird nicht geprüft.
	 */
	protected function clean_up() {
		return;
	}
}
?>
