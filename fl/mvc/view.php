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
	protected $datamodel;
	protected $cap; // array( c a p )
	protected $route; // fl_route
	protected $functions;

	protected $headers;

	protected $modulepath;
	protected $apppath;
	protected $elementpath;
	protected $layoutpath;

	protected $translator = NULL;

	/**
	 * Daten
	 */
	protected $data = null;

	/**
	 * Kontruktor der Viewklasse
	 *
	 * @todo $data_access hier entfernen (kein DB-Zugriff aus dem View heraus!)
	 *
	 * @param array        $data         Daten, die dargestellt werden sollen
	 * @param data_access  $data_access  Datenzugriffsobjekt
	 * @param functions $functions    Federleicht-Hilfsobjekt
	 * @param string       $model_name   Name des in erster Linie verwendeten Models
	 */
	public function __construct($data, data_access $data_access, $functions, $model_name) {
		$this->datamodel = $data_access;
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
		$this->translator = $model->translator;
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
			if ( $prefix == 'comon' ) {
				$path = $this->layoutpath;
			} else {
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
		require_once $path . $layout . '.php';
		$template = ob_get_contents();
		ob_end_clean();
		
		return $template;
	}

	/**
	 * Sucht den zur Action passenden Unterview heraus.
	 */
	protected function get_sub_view() {
		require_once($this->modulepath . $this->cap['controller'] . '/views/' . $this->subview . '.php');
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
			$path = $this->modulepath . '/' . $modul . '/elements/';
		}

		if ( file_exists($path . $file . '.php') ) {
			require_once $path . $file . '.php';
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
			$result = $this->datamodel->retrieve('optionen', '*', "optionname='seitentitel'", '', '1');
			#define('SEITENTITEL', $result['value']);
			$title = $result[0]['value'];
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
	 * Übersetzten Text ausgeben
	 *
	 * Es wird ein Text zurückgegeben, der entweder eine Übersetzung 
	 * des übergebenen Textes ist oder der übergebene Text selbst.
	 *
	 * @todo Achtung: Funktion ist auch in fl/model.php definiert. Sollte besser nur dort sein. Entwurf verbessern!
	 * @param string $text
	 * @param string $lang
	 * @return string
	 */
	protected function translate($text, $lang=LANG) {
		if ( is_object($this->translator) ) { 
			$translation = $this->translator->get($text, $lang);
		} else {
			$translation = $text;
		}

		return $translation;
	}

	/**
	 * URL zur aktuellen Seite ausgeben
	 *
	 * @todo Routen so erweitern, das die aktuelle URL ausgegeben werden kann.
	 * @todo Federleicht so umstellen, dass das erfolgreiche fl_route-Objekt in der Registry ist und vorrangig verwendet wird.
	 */
	protected function current_url() {
		return $this->route->get_current_url();

		$registry = fl_registry::get_instance();
		$cap = $registry->get('request', 'route');

		$url = 'http://'.$_SERVER['HTTP_HOST'].'/'.$cap['controller'].'/'.$cap['action'].'/'.$cap['param'];
		return $url;
	}
}
?>
