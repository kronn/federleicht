<?php
/**
 * ActiveRecord-Objekt
 *
 * @author Matthias Viehweger <kronn@kronn.de>
 * @version 0.2
 * @package federleicht
* @subpackage data
 * @todo $fields automatisch aus Datenbank auslesen
 * @todo validator in Basisklasse instanziieren?
 * @todo active_record als Datenzugriffsklasse (und nicht als Datenstruktur) im richtigen Verzeichnis ablegen und von dort laden lassen.
 * @todo active_record sollte auch das Interface data_access und data_wrapper implementieren, da es sowohl Datenzugriff wie Daten selbst darstellt.
 */
abstract class fl_data_structures_activerecord implements data_wrapper {
	/**
	 * Instanzvariablen
	 */
	protected $db = null;
	protected $table = '';
	protected $data = null;
	public $id = null;

	public $error_messages = array();

	/**
	 * Konstruktor
	 *
	 * @param data_access $db
	 * @param string $table
	 * @param int $id
	 * @param data_wrapper $data
	 * @param boolean $loaded
	 */
	public function __construct(data_access $db, $table, $id, data_wrapper $data, $loaded=false) {
		$this->db =& $db;
		$this->table = ( empty($table) ) ? $this->table : $table;
		$this->id = $id;

		$this->data = $data;

		if ( !$loaded ) {
			$this->load();
		}
	}

	/**
	 * Daten setzen
	 */
	public function set_data(array $data) {
		foreach ( $data as $key => $value ) {
			if ( empty($value) ) continue;
			
			$this->data->set($key, $value);
		}
	}

	/**
	 * Daten holen
	 *
	 * @return data_structure
	 */
	public function get_data() {
		return clone $this->data;
	}

	/**
	 * Daten holen und als Array zurueckgeben
	 *
	 * @return array
	 */
	public function get_data_as_array() {
		$data = array();

		foreach ( $this->data as $key => $value ) {
			if ( empty($value) OR $value === null ) continue;

			$data[$key] = $value;
		}

		return $data;
	}

	/**
	 * Einzelnes Datenfeld ausgeben
	 *
	 * @param string $key
	 */
	public function say($key) {
		return $this->data->say($key);
	}

	/**
	 * Einzelnes Datenfeld holen
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get($key) {
		return $this->data->get($key);
	}

	/**
	 * Einzelnes Datenfeld setzen
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function set($key, $value) {
		return $this->data->set($key, $value);
	}

	/**
	 * Prüfung, ob Datenfeld existiert
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function is_set($key) {
		return $this->data->is_set($key);
	}

	/**
	 * Einzelnes Datenfeld löschen
	 *
	 * @param string $key
	 */
	public function remove($key) {
		return $this->data->remove($key);
	}

	/**
	 * Daten aus Datenbank laden
	 */
	public function load() {
		if ( $this->id > 0 ) {
			$result = $this->db->convert_result(
				$this->table,
				$this->db->retrieve($this->table, '*', 'id='.$this->id)
			);
			$data = (array) $result[0];
		} else {
			$data = array();
		}

		$result = $this->data->set_data($data);

		if ( $this->id > 0 ) {
			$this->load_additional_data_parts();
		}

		return $result;
	}

	/**
	 * Daten in Datenbank speichern
	 *
	 * @return boolean
	 */
	public function save() {
		$this->prepare_data();

		if ( $this->id > 0 ) {
			$result = $this->db->update($this->table, $this->get_data_as_array(), $this->id);
		} else {
			$result = $this->db->create($this->table, $this->get_data_as_array());
			if ( is_numeric($result) ) {
				$this->id = $result;
				$this->load();
				$result = true;
			}
		}

		return $result;
	}

	/**
	 * Daten aus Datenbank loeschen
	 *
	 * @return boolean
	 */
	public function delete() {
		if ( $this->id > 0 ) {
			$result = $this->db->del($this->table, $this->id);
		} else {
			$result = false;
		}

		return $result;
	}

	/**
	 * Objekt als String verwenbar machen
	 */
	public function __toString() {
		return (string) $this->data;
	}

	/**
	 * Daten vorbereiten
	 */
	protected function prepare_data() {}

	/**
	 * zusätzliche Daten laden
	 */
	protected function load_additional_data_parts() {}

	/**
	 * Datenprüfung
	 *
	 * @return array
	 */
	public function validate_data() {
		/**
		 * Prüfregeln durchlaufen
		 */
		$validator = $this->get_validator();
		$this->error_messages += $validator->validate_form($this->get_data());

		return $this->error_messages;
	}

	/**
	 * Datenprüfungsobjekt erzeugen
	 *
	 * @return validation
	 */
	abstract public function get_validator();
}
