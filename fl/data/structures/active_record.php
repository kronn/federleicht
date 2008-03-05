<?php
/**
 * ActiveRecord-Objekt
 *
 * @author Matthias Viehweger <kronn@kronn.de>
 * @version 0.2
 * @package federleicht
 * @subpackage data
 * @todo $unneeded_fields zu $fields (positiv liste) umwandeln
 * @todo $fields automatisch aus Datenbank auslesen
 * @todo validator in Basisklasse instanziieren
 * @todo validate_data als standardfunktion in Basisklasse uebernehmen
 * @todo active_record als Datenzugriffsklasse (und nicht als Datenstruktur) im richtigen Verzeichnis ablegen und von dort laden lassen.
 */
class active_record {
	/**
	 * Instanzvariablen
	 */
	public $db = null;
	public $table = '';
	public $data = null;
	public $id = null;

	/**
	 * Variablen
	 */
	protected $unneeded_fields = array();

	/**
	 * Konstruktor
	 *
	 * @param datamodel $db
	 * @param string $table
	 * @param int $id
	 * @param data_structure $data
	 * @param boolean $loaded
	 */
	public function __construct(data_accessor $db, $table, $id, data_structure $data, $loaded=false) {
		$this->db =& $db;
		$this->table = $table;
		$this->id = $id;

		$this->data = $data;

		if ( !$loaded ) {
			$this->load();
		}
	}

	/**
	 * Daten setzen
	 */
	public function set_data($data) {
		foreach ( $data as $key => $value ) {
			if ( empty($value) ) continue;
			
			$this->data->set($key, $value);
		}
	}

	/**
	 * Daten holen
	 */
	public function get_data() {
		$data = array();

		foreach ( $this->data->get_data() as $key => $value ) {
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
	 * Daten aus Datenbank laden
	 */
	public function load() {
		if ( $this->id > 0 ) {
			$result = $this->db->convert_result(
				$this->table,
				$this->db->retrieve($this->table, '*', 'id='.$this->id)
			);
			$data = $result[0];
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
		$this->remove_unneeded_fields($this->unneeded_fields);
		$this->prepare_data();

		if ( $this->id > 0 ) {
			$result = $this->db->update($this->table, $this->get_data(), $this->id);
		} else {
			$result = $this->db->create($this->table, $this->get_data());
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
	 * Nicht benoetigte Daten loeschen
	 *
	 * @param array $unneeded_fields
	 */
	protected function remove_unneeded_fields($unneeded_fields) {
		foreach ( $unneeded_fields as $key ) {
			$this->data->remove($key);
		}
	}

	/**
	 * Daten vorbereiten
	 */
	protected function prepare_data() {}

	/**
	 * zusätzliche Daten laden
	 */
	protected function load_additional_data_parts() {}
}
