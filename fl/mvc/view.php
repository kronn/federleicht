<?php
/**
 * View
 *
 * Eine grundlegende Viewklasse.
 *
 * @package federleicht
 * @subpackage base
 */
class view {
	/**
	 * Instanzvariablen
	 */
	var $layout = 'default';
	var $subview = '';

	/**
	 * extern eingebundene Objekte und Variablen
	 */
	var $datamodel;
	var $cap; // zu route umbennenen
	var $functions;

	var $headers;

	var $modulepath;
	var $apppath;

	var $translator = NULL;

	/**
	 * Daten
	 */
	var $data = array();

	/**
	 * Kontruktor der Viewklasse
	 *
	 * @todo $data_access hier entfernen und statt dessen Referenz auf Model übergeben, das dann die einzige Schnittstelle bietet.
	 *
	 * @param array        $data         Daten, die dargestellt werden sollen
	 * @param data_access  $data_access  Datenzugriffsobjekt
	 * @param functions $functions    Federleicht-Hilfsobjekt
	 * @param string       $model_name   Name des in erster Linie verwendeten Models
	 */
	function view($data, $data_access, $functions, $model_name) {
		$this->data = $data;

		$this->datamodel = $data_access;
		$this->functions = $functions;

		$registry =& registry::getInstance();
		$this->cap = $registry->get('request', 'route');
		$this->subview = $registry->get('subview');

		$this->modulepath = $registry->get('path', 'module');
		$this->apppath = $registry->get('path', 'app');

		$this->headers = $registry->get('config', 'headers');

		$model = $this->functions->get_model($model_name);
		$this->translator = $model->translator;
	}

	/**
	 * Ruft das Template auf
	 *
	 * @param array $data
	 */
	function render_layout($layout) {
		if ( strpos($layout, '/') === FALSE ) {
			$path = $this->modulepath . $this->cap['controller'] . '/';
		} else {
			list($prefix, $layout) = explode('/', $layout, 2);
			$path = $this->apppath;
		}

		/**
		 * Content-Type setzen, wenn Header noch nicht gesendet
		 */
		if ( !headers_sent() ) {
			header('Content-Type: ' . $this->headers['content-type'] . 
				'; charset=' . $this->headers['charset'] );
		}

		require_once $path . 'layouts/'  . $layout . '.php';
	}

	/**
	 * Sucht den zur Action passenden Unterview heraus.
	 */
	function get_sub_view() {
		require_once($this->modulepath . $this->cap['controller'] . '/views/' . $this->subview . '.php');
	}

	/**
	 * Ein Seitenelement holen
	 *
	 * Es wird ein vordefinierter HTML-Baustein geholt. Er steht allen Seitenbereichen zu Verf&uuml;gung.
	 * Variablen k&ouml;nnen als Assoziatives Array &uuml;bergeben werden. Sonstigen Variablen sind meist nicht
	 * verf&uuml;gbar.
	 *
	 * @param string $name      Dateiname (ohne Endung) des Elements.
	 * @param array  $variablen Assoziatives Array mit Variablen f&uuml;r das Element.
	 */
	function get_element($name, $variablen='') {
		$vars = ( !is_array($variablen) )? array($variablen): $variablen;
		require_once $this->apppath . 'elements/' . $name . '.php';
	}

	/**
	 * Teilbereich eines Subview holen
	 *
	 * @oaram string $name  Name des Teilbereichs
	 */
	function get_partial($name, $forgiving=FALSE) {
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
	function render_flash($namespace='') {
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
	function get_site_title() {
		if ( !defined('SEITENTITEL') ) {
			$result = $this->datamodel->retrieve(ADMINMODULE.'_options', '*', "optionname='SEITENTITEL'", '', '1');
			#define('SEITENTITEL', $result['value']);
			$title = $result['value'];
		} else {
			$title = SEITENTITEL;
		}

		echo $title;
		return;
	}

	/**
	 * Datenfeld ausgeben
	 *
	 * Vor der Datenausgaben werden alle HTML-Sonderzeichen
	 * maskiert, um Ausgabeprobleme zu vermeiden.
	 *
	 * @param string $field       Name des Datenfeldes
	 * @param string $type        Typehint für die Ausgabe, ggf. werden die 
	 *                            Daten vor Ausgabe entsprechend umgewandelt.
	 * @param array  $source      Quelldatenfeld, das untersucht werden soll
	 * @param bool   $raw_output  Angabe, ob die Daten ohne HTML-Konvertierung 
	 *                            ausgegeben werden sollen.
	 * @param string $default     Text der ausgegeben werden soll, wenn keine Daten vorliegen
	 * @return mixed [string, array oder object]
	 */
	function get_field($field, $type='', $source=NULL, $raw_output=FALSE, $default='') {
		$daten = ( is_null($source) OR empty($source) )? 
			$this->data: 
			$source;

		$data = ( isset($daten[$field]) )?
			$daten[$field]:
			(string) $default;
		if ( is_array($data) OR is_object($data) ) return $data;

		switch ($type) {
		case 'int':
		case 'integer':
			$content = intval($data);
			break;

		case 'bool':
		case 'boolean':
			$content = (bool) $data;
			break;

		case 'double':
		case 'float':
			$content = (double) $data;
			break;

		case 'string':
			$content = (string) $data;
		default:
			$content = ( isset($content) )? 
				$content: 
				$data;

			if ( !$raw_output ) {
				$content = htmlentities( html_entity_decode($content, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');
			}
		}

		return $content;
	}

	/**
	 * Wrapper für "get_field();"
	 */
	function get($field, $type='', $source=NULL, $raw_output=FALSE, $default='') {
		return $this->get_field($field, $type, $source, $raw_output, $default);
	}

	/**
	 * Wrapper für "echo get_field();"
	 */
	function say($field, $type='', $source=NULL, $raw_output=FALSE, $default='') {
		echo $this->get_field($field, $type, $source, $raw_output, $default);
	}

	/**
	 * Übersetzten Text ausgeben
	 *
	 * Es wird ein Text zurückgegeben, der entweder eine Übersetzung 
	 * des übergebenen Textes ist oder der übergebene Text selbst.
	 *
	 * @todo Achtung: Funktion ist auch in fl/model.php definiert
	 * @param string $text
	 * @param string $lang
	 * @return string
	 */
	function translate($text, $lang=LANG) {
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
	function get_username() {
		if ( isset($_SESSION['username']) ) {
			echo ucwords($_SESSION['username']);
		}
	}

	/**
	 * Alter aus Geburtsdatum berechnen und zurückgeben
	 */
	function get_age($geburtsdatum) {
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
	 */
	function current_url() {
		$registry =& registry::getInstance();
		$cap = $registry->get('request', 'route');
		$url = 'http://'.$_SERVER['HTTP_HOST'].'/'.LANG.'/'.$cap['controller'].'/'.$cap['action'].'/'.$cap['param'];
		return $url;
	}
}
?>
