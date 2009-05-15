<?php
/**
 * View
 *
 * Eine grundlegende Viewklasse.
 *
 * @package federleicht
 * @subpackage base
 */
class fl_view {
	/**
	 * Instanzvariablen
	 */
	protected $layout = 'default';
	protected $subview = '';

	/**
	 * extern eingebundene Objekte und Variablen
	 */
	protected $cap; // array( c a p )
	protected $route; // fl_route
	protected $functions;

	protected $headers;

	protected $modulepath;
	protected $apppath;
	protected $elementpath;
	protected $layoutpath;

	/**
	 * Daten
	 */
	protected $data = null;

	/**
	 * Kontruktor der Viewklasse
	 *
	 * @param array        $data         Daten, die dargestellt werden sollen
	 * @param functions $functions    Federleicht-Hilfsobjekt
	 * @param string       $model_name   Name des in erster Linie verwendeten Models
	 */
	public function __construct($data, $functions, $model_name) {
		$this->functions = $functions;
		$this->factory   = $functions->factory;

		$this->data = $this->factory->get_structure('view', $data);

		$registry = fl_registry::getInstance();
		$this->cap = $registry->get('request', 'request');
		$this->route = $registry->get('request', 'route');

		$this->subview = $registry->get('subview');

		$this->modulepath = $registry->get('path', 'module');
		$this->apppath = $registry->get('path', 'app');
		$this->elementpath = $registry->get('path', 'elements');
		$this->layoutpath = $registry->get('path', 'layouts');

		$this->headers = $registry->get('config', 'headers');

		$model = $this->factory->get_model($model_name);

		if ( isset($model->translator) ) {
			$this->translator = $model->translator;
		} else {
			$this->translator = null;
		}
	}

	/**
	 * Ruft das Template auf
	 *
	 * @param string $layout
	 * @return string
	 */
	public function render_layout($layout) {
		if ( strpos($layout, '/') === false ) {
			$path = $this->modulepath . $this->cap['controller'] . '/layouts/';
		} else {
			list($prefix, $layout) = explode('/', $layout, 2);
			switch( $prefix ) {
			case 'common':
				$path = $this->layoutpath;
				break;

			case 'builtin':
				$path = fl_registry::get_instance()->get('path', 'lib') . 'builtin/layouts/';
				break;

			default :
				$path = $this->modulepath . $prefix . '/layouts/';
			}
		}

		/**
		 * Content-Type setzen, wenn Header noch nicht gesendet
		 */
		if ( !headers_sent() ) {
			header('Content-Type: ' . $this->headers['content-type'] . 
				'; charset=' . $this->headers['charset'] );
		}

		ob_start();
		require $path . $layout . '.php';
		$template = ob_get_contents();
		ob_end_clean();
		
		return $template;
	}

	/**
	 * Sucht den zur Action passenden Unterview heraus.
	 */
	protected function get_sub_view() {
		$file = $this->modulepath . $this->cap['controller'] . '/views/' . $this->subview . '.php';

		if ( file_exists($file) ) {
			require $file;
		} else {
			throw new Exception('Datei ' . $file . ' nicht gefunden');
		}
	}

	/**
	 * Ein Seitenelement holen
	 *
	 * Es wird ein vordefinierter HTML-Baustein geholt. 
	 * Er steht allen Seitenbereichen zu Verf&uuml;gung.
	 * Variablen k&ouml;nnen als Assoziatives Array &uuml;bergeben werden. 
	 * Sonstigen Variablen sind meist nicht
	 * verf&uuml;gbar.
	 *
	 * @param string $name   Dateiname (ohne Endung) des Elements.
	 * @param array  $vars   Assoziatives Array mit Variablen fuer das Element.
	 */
	protected function get_element($name, array $vars = array() ) {
		if ( strpos($name, '/') === false) {
			$path = $this->elementpath;
			$file = $name;
		} else {
			list($modul, $file) = explode('/', $name, 2);
			switch( $modul ) {
			case 'common':
				$path = $this->elementpath;
				break;

			case 'builtin':
				$path = fl_registry::get_instance()->get('path', 'lib') . 'builtin/elements/';
				break;

			default :
				$path = $this->modulepath . '/' . $modul . '/elements/';
				break;
			}
		}

		if ( file_exists($path . $file . '.php') ) {
			require $path . $file . '.php';
		}
	}

	/**
	 * Teilbereich eines Subview holen
	 *
	 * @param string $name  Name des Teilbereichs
	 * @deprecated
	 */
	protected function get_partial($name, $forgiving=FALSE) {
		trigger_error('veraltet: $this->get_element() verwenden.');

		return $this->get_element($this->cap['controller'].'/'.$name);
	}

	/**
	 * komplette Seite mit vorherigem Controlleraufruf holen
	 *
	 * @param string $url
	 */
	protected function get_component($url) {
		$registry = fl_registry::get_instance();
		$lang = $registry->get('config', 'lang');

		$dispatcher = new fl_dispatcher(
			new fl_lang($lang['default'], $lang['all']),
			$registry->get('modules')
		);

		foreach( $registry->get('config', 'routes') as $route ) {
			$dispatcher->add_route( $route );
		}

		$request = $this->functions->factory->get_structure(
			'request', 
			$dispatcher->analyse($url) 
		);

		$old_request = $registry->get('request');
		$registry->set('request', $request);

		$modul = $this->functions->factory->get_modul($request['modul'], $this->functions);
		$modul->start_execution();

		$registry->set('request', $old_request);
	}

	/**
	 * Gibt die Flash-Nachricht zurück
	 *
	 * @param string $namespace
	 * @return string
	 */
	protected function render_flash($namespace='') {
		$html = '';

		$messages = $this->functions->flash->get_messages($namespace);
		foreach( $messages as $message ) {
			$html .= '<p class="'.$message['type'].'">'.$message['msg'].'</p>'.PHP_EOL;
		}
		$this->functions->flash->clear_messages($namespace);

		return $html;
	}

	/**
	 * Seitentitel ausgeben
	 *
	 * @return string
	 */
	protected function get_site_title() {
		if ( !defined('SEITENTITEL') ) {
			$title = '';
		} else {
			$title = SEITENTITEL;
		}

		echo $title;
		return;
	}

	/**
	 * Wrapper fuer Datenobjekt
	 */
	protected function get($field, $type='string', $source=NULL, $raw=FALSE, $default='') {
		if ( $source instanceof data_structure) {
			return $source->get($field, $type, $raw, $default);
		}

		$this->data->set_raw_output($raw);
		$former_default = $this->data->set_default($default);

		$value = $this->data->get($field, $type);

		$this->data->set_default($former_default);
		return $value;
	}

	/**
	 * Wrapper fuer Datenobjekt
	 */
	protected function say($field, $type='string', $source=NULL, $raw_output=FALSE, $default='') {
		echo $this->get($field, $type, $source, $raw_output, $default);
	}

	/**
	 * Datenobjekt zurueckgeben
	 * 
	 * @return view_data
	 */
	public function get_data_object() {
		return $this->data;
	}

	/**
	 * URL zur aktuellen Seite ausgeben
	 */
	protected function current_url() {
		return $this->route->get_current_url();
	}
}
?>
