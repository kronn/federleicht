<?php
/**
 * View
 *
 * Eine grundlegende Viewklasse.
 *
 * @package federleicht
 * @subpackage base
 */
class fl_mvc_view {
	/**
	 * Instanzvariablen
	 */
	protected $layout = 'default';
	protected $subview = '';

	/**
	 * extern eingebundene Objekte und Variablen
	 */
	protected $datamodel;
	protected $cap; // zu route umbennenen
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
		$this->factory   = $functions->get_factory();

		$this->data = $this->factory->get_structure('view', $data);

		$registry = registry::getInstance();
		$this->cap = $registry->get('request', 'route');
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
	 */
	public function render_layout($layout) {
		if ( strpos($layout, '/') === FALSE ) {
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

		require_once $path . $layout . '.php';
	}

	/**
	 * Sucht den zur Action passenden Unterview heraus.
	 */
	public function get_sub_view() {
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
	 * @param string $name      Dateiname (ohne Endung) des Elements.
	 * @param array  $variablen Assoziatives Array mit Variablen fuer das Element.
	 */
	public function get_element($name, $variablen='') {
		$vars = ( !is_array($variablen) )? array($variablen): $variablen;
		require_once $this->elementpath . $name . '.php';
	}

	/**
	 * Teilbereich eines Subview holen
	 *
	 * @param string $name  Name des Teilbereichs
	 */
	public function get_partial($name, $forgiving=FALSE) {
		$file = $this->modulepath . $this->cap['controller'] . '/partials/' . $name . '.php';

		if ( $forgiving AND !file_exists($file) ) {
			return;
		} else {
			require_once $file;
		}
	}

	/**
	 * Gibt die Flash-Nachricht zurück
	 *
	 * @param string $namespace
	 * @return string
	 */
	public function render_flash($namespace='') {
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
	public function get_site_title() {
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
	public function get($field, $type='string', $source=NULL, $raw=FALSE, $default='') {
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
	public function say($field, $type='string', $source=NULL, $raw_output=FALSE, $default='') {
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
	public function translate($text, $lang=LANG) {
		if ( is_object($this->translator) ) { 
			$translation = $this->translator->get($text, $lang);
		} else {
			$translation = $text;
		}

		return $translation;
	}

	/**
	 * Usernamen des aktuell eingeloggten Users ausgeben
	 */
	public function get_username() {
		trigger_error(
			'veraltet, Wert sollte als Variable uebergeben werden',
			E_USER_NOTICE
		);
		if ( isset($_SESSION['username']) ) {
			echo ucwords($_SESSION['username']);
		}
	}

	/**
	 * Alter aus Geburtsdatum berechnen und zurückgeben
	 */
	public function get_age($geburtsdatum) {
		trigger_error(
			'veraltet, Wert sollte als Variable uebergeben werden. siehe: datum_model::alter()',
			E_USER_NOTICE
		);
		$date = explode('-', $geburtsdatum);

		# Windows-Workaround
		$year_diff = 0;
		if ( $date[0] < 1970 ) {
			$year_diff = 1970 - $date[0];
			$date[0] = 1970;
		}

		$birthday = mktime(0, 0, 0, $date[1], $date[2], $date[0]);

		return date('Y') - (date('Y', $birthday) - $year_diff) - 1 + (int)((date('m', $birthday) <= date('m')) && (date('d', $birthday) <= date('d')) );
	}

	/**
	 * URL zur aktuellen Seite ausgeben
	 *
	 * @todo Routen so erweitern, das die aktuelle URL ausgegeben werden kann.
	 */
	public function current_url() {
		$registry =& registry::getInstance();
		$cap = $registry->get('request', 'route');
		$url = 'http://'.$_SERVER['HTTP_HOST'].'/'.$cap['controller'].'/'.$cap['action'].'/'.$cap['param'];
		return $url;
	}
}
?>
