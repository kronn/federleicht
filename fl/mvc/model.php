<?php
/**
 * Model
 *
 * Die grundlegende Modelklasse.
 *
 * @package federleicht
 * @subpackage base
 */
class model {
	/**
	 * Instanzvariablen
	 */
	var $modul = '';
	var $error_messages = array();

	/**
	 * extern eingebundene Objekte und Variablen
	 */
	var $datamodel;
	var $factory;
	var $modulepath;
	var $translator = NULL;

	/**
	 * Kontruktor des Models
	 *
	 * Das Model enthält das eigentliche Programm. Zugriff auf die Methoden
	 * erfolgen durch den Controller.
	 *
	 * @param data_access  &$data_access  Datenzugriff
	 * @param factory      &$factory      Objekterzeugungsobjekt
	 * @param string       &$path         Pfad zu Moduldateien
	 */
	function model(data_access $data_access, &$factory, $path) {
		$this->datamodel = $data_access;
		$this->factory = $factory;
		$this->modulepath = $path;

		$this->modul = array_shift(explode('_', get_class($this)));

		# $this->translator = $this->get_translator();
	}

	/**
	 * Übersetzungsobjekt holen
	 *
	 * @todo php4-Version des Ubersetzers erstellen und ggf. einbinden.
	 * @return GetText
	 */
	function get_translator() {
		$this->functions->needs('localization');
		if ( class_exists('flGetText') AND class_exists('LocalizationDB') AND class_exists('RessourceManager') ) {
			require ABSPATH . 'config/database.conf.php';
			$db_config = $config['db']; 
			$locale_db = new LocalizationDB(
				'localization',
				$db_config['mysql_host'],
				$db_config['mysql_user'],
				$db_config['mysql_pass']
			);
			$rm = new RessourceManager($locale_db);
			$translator = new flGetText(LANG, $rm);
		}

		return $translator;
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
	function get_data($modul, $id, $id_alternate_name='slug') {
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
	function get_data_from_db($table, $page, $string_field='slug') {
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
	function find_one($result, $field='id') {

		while ( !isset($result[$field]) AND is_array($result) ) {
			$result = array_shift($result);
		}

		return $result;
	}

	/**
	 * Mehrere Datensätze zurückgeben
	 *
	 * @param array $result  Datenbankergebnis
	 * @param string $field  optional
	 * @return array
	 * @todo ins Datenbank-Objekt!
	 */
	function find_many($result, $field='id') {
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
	public function encode_entities($data, $field = 'name') {
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
	function transform_checkboxes($postdata, $checkboxes, $strict = FALSE) {
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
	function get_error_messages() {
		return implode('<br />', (array) $this->error_messages);
	}

	/**
	 * Übersetzten Text ausgeben
	 *
	 * Es wird ein Text zurückgegeben, der entweder eine Übersetzung 
	 * des übergebenen Textes ist oder der übergebene Text selbst.
	 *
	 * @todo Achtung: Funktion ist auch in fl/view.php definiert
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
	 * Array übersetzen
	 *
	 * @param array  $array
	 * @param string $lang
	 * @param string $index
	 * @return array
	 */
	function translate_array($array, $lang=LANG, $index='') {
		if ( $index !== '' AND isset($array[0][$index])) {
			foreach ( $array as $key => $value ) {
				$array[$key][$index] = $this->translate($value[$index], $lang);
			}

		} else {
			foreach ( $array as $key => $value ) {
				$array[$key] = $this->translate($value, $lang);
			}

		}

		return $array;
	}

	function count($table, $condition='') {
		trigger_error('veraltet. neu: data_access->count()', E_USER_ERROR);
	}
}
?>
