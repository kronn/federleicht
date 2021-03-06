<?php
/**
 * Model
 *
 * Die grundlegende Modelklasse.
 *
 * @package federleicht
 * @subpackage base
 */
class fl_model {
	/**
	 * Instanzvariablen
	 */
	protected $modul = '';
	protected $error_messages = array();

	/**
	 * extern eingebundene Objekte und Variablen
	 */
	protected $datamodel;
	protected $factory;
	protected $modulepath;

	/**
	 * Kontruktor des Models
	 *
	 * Das Model enthält das eigentliche Programm. Zugriff auf die Methoden
	 * erfolgen durch den Controller.
	 *
	 * @param data_source_access  $data_access  Datenzugriff
	 * @param factory             $factory      Objekterzeugungsobjekt
	 * @param string              $path         Pfad zu Moduldateien
	 */
	public function __construct(data_source_access $data_access, &$factory, $path) {
		$this->datamodel = $data_access;
		$this->factory = $factory;
		$this->modulepath = $path;

		$class = explode('_', get_class($this));
		$this->modul = $class[1];
	}

	/**
	 * Daten holen
	 *
	 * Wrappperfunktion, die entweder direkt aus der Datenbank die Daten holt, 
	 * oder die entsprechende Funktion eines Model aufruft.
	 *
	 * @param string $modul
	 * @param mixed  $id
	 * @param string $id_alternate_name
	 * @return array
	 */
	public function get_data($modul, $id, $id_alternate_name='slug') {
		$model = ( $modul != $this->modul )?
			$this->factory->get_model($modul):
			$this;

		if ( is_numeric($id) ) $id_alternate_name = 'id';

		$data = ( is_object($model) AND method_exists($model, 'get_data_from_model') )?
			$model->get_data_from_model($id, $id_alternate_name):
			$this->get_data_from_db($modul, $id, $id_alternate_name);

		return $data;
	}

	/**
	 * Holt die Daten aus der Datenbank
	 *
	 * @param string $table Name der Tabelle
	 * @param mixed $page eindeutige id der Seite
	 * @param string $string_field Feldname, der den Speichernamen enthält
	 */
	protected function get_data_from_db($table, $page, $string_field='slug') {
		if ( empty($page) ) {
			$page = $this->datamodel->retrieve($table.'_options', 'value', "optionname = 'STARTPAGE'", '', '1');
			$page = $page['value'];
		}

		if ( is_numeric($page) ) {
			$condition = 'id='.$page;
		} elseif ( is_string($page) ) {
			$condition = $string_field.'="'.$page.'"';
		} 

		$data = $this->datamodel->retrieve($table, '*', $condition, '', '1');

		return $data;
	}

	/**
	 * Einen Datensatz zurückgeben
	 *
	 * @param array $result  Datenbankergebnis
	 * @param string $field  optional
	 * @return array
	 * @todo ins Datenbank-Objekt!
	 */
	public function find_one(array $result, $field='id') {
		while ( !isset($result[$field]) AND is_array($result) ) {
			$result = array_shift($result);
		}

		return (array) $result;
	}

	/**
	 * Mehrere Datensätze zurückgeben
	 *
	 * @param array $result  Datenbankergebnis
	 * @param string $field  optional
	 * @return array
	 * @todo ins Datenbank-Objekt!
	 */
	public function find_many(array $result, $field='id') {
		$result = (array) $result;

		$many_results = ( isset($result[$field]) )?
			array($result):
			$result;

		return $many_results;
	}

	/**
	 * Umlaute in Daten fuer HTML umwandeln
	 *
	 * Es wird angenommen, dass ein zweidimensionales Array mit
	 * Datenbankergebnissen umzuwandlen ist.
	 *
	 * @param array $data
	 * @param string $field
	 * @return array
	 */
	public function encode_entities(array $data, $field = 'name') {
		foreach($data as $key => $value ) {
			$data[$key][$field] = htmlentities( 
				html_entity_decode(
					$value[$field],
					ENT_QUOTES,
					'UTF-8'
				), 
				ENT_QUOTES, 
				'UTF-8'
			);
		}

		return $data;
	}

	/**
	 * Checkboxen in Binärzahlen umwandeln
	 *
	 * Die Funktion verhält sich standardmäßig unauffälig und übergeht fehlende 
	 * (also auch falschgeschriebene) Indizes. Diese Verhalten kann mit dem 
	 * optionalen Parameter $strict so verändert werden, dass fehlende Checkboxen 
	 * mit dem Wert 0 erzeugt werden. 
	 *
	 * @param array  $postdata	Array mit Daten
	 * @param string $checkboxes  CSV-String der umzuwandelnden Checkboxen
	 * @param bool   $strict	  nichtvorhandene Felder werden mit Wert 0 
	 * erzeugt
	 * @return array
	 */
	public function transform_checkboxes(array $postdata, $checkboxes, $strict = FALSE) {
		$checkboxes = explode(',', $checkboxes);
		$data = (array) $postdata;

		foreach( $checkboxes as $checkbox ) {
			$checkbox = trim($checkbox);

			if ( !isset($data[$checkbox]) ) {
				if ( $strict ) {
					$data[$checkbox] = 0;
				}

				continue;
			}

			$data[$checkbox] = ( $data[$checkbox] === $checkbox )?
				1:
				0;
		}

		return $data;
	}

	/**
	 * Fehlermeldungen holen
	 *
	 * @return string
	 */
	public function get_error_messages() {
		return implode('<br />', (array) $this->error_messages);
	}
}
?>
