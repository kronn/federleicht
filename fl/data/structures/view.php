<?php
/**
 * View_data
 *
 * Datenobjekt der internen Viewklasse
 *
 * @package federleicht
 * @subpackage base
 */
class fl_data_structures_view extends fl_data_structures_data {
	protected $_default = '';
	private $_raw_output = false;

	public $system = array();

	/**
	 * Datenfeld ausgeben
	 *
	 * Vor der Datenausgaben werden alle HTML-Sonderzeichen
	 * maskiert, um Ausgabeprobleme zu vermeiden.
	 *
	 * @param string $key    Name des Datenfeldes
	 * @param string $type   Typehint fuer die Ausgabe, ggf. werden die 
	 *                       Daten vor Ausgabe entsprechend umgewandelt.
	 * @return mixed
	 */
	public function get($key, $type='string') {
		$data = parent::get($key);
	
		if ( is_array($data) OR is_object($data) ) return $data;

		if ( $type === true ) {
			$type = 'string';
		}

		settype($data, $type);

		if ( $type == 'string' AND !$this->_raw_output ) {
			$data = htmlentities( 
				html_entity_decode(
					$data,
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
	 * Ausgabe ohne Umwandlung der HTML-Sonderzeichen aktivieren
	 *
	 * @param boolean $raw
	 */
	public function set_raw_output($raw) {
		$this->_raw_output = (boolean) $raw;
	}

	/**
	 * Zwischen direkter und umgewandelter HTML-Ausgabe umschalten
	 */
	public function toggle_raw_output() {
		$this->set_raw_output(!$this->_raw_output);
	}

	/**
	 * Defaultwert setzen
	 *
	 * Es wird der bisherige Defaultwert zurueckgegeben
	 *
	 * @param string $default
	 * @return string
	 */
	public function set_default($default) {
		$former_default = $this->_default;
		$this->_default = (string) $default;
		return $former_default;
	}
}
