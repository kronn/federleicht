<?php
/**
 * ActiveRecord-Objekt
 *
 * @author Matthias Viehweger <kronn@kronn.de>
 * @version 0.2
 * @package federleicht
 * @subpackage data
 *
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
	 * Relationen zu anderen ActiveRecord-Objekten/Datensätzen
	 */
	protected $has_one = array();
	protected $has_many = array();

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
		$this->db = $db;
		$this->table = ( empty($table) ) ? $this->table : $table;
		$this->id = $id;

		$this->data = $data;

		if ( !$loaded ) {
			$this->load();
		}
	}

	/**
	 * Interface data_wrapper
	 *
	 * Weiterleitungen auf das Datenobjekt
	 */
	public function set($key, $value) {
		return $this->data->set($key, $value);
	}
	public function say($key) {
		return $this->data->say($key);
	}
	public function get($key) {
		return $this->data->get($key);
	}
	public function set_data(array $data) {
		$this->data->set_data($data);
	}
	public function is_set($key) {
		return $this->data->is_set($key);
	}
	public function remove($key) {
		return $this->data->remove($key);
	}
	/**
	 * Interface data_wrapper ENDE
	 */

	/**
	 * Daten holen
	 *
	 * @return data_wrapper
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
	 * Daten aus Datenbank laden
	 */
	public function load() {
		if ( $this->id > 0 ) {
			$unconverted_result = $this->db->retrieve($this->table, '*', 'id='.$this->id);

			if ( $this->db instanceof fl_data_access_database ) {
				$result = $this->db->convert_result($this->table, $unconverted_result);
			} else {
				$result = $converted_result;
			}

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
	 * Objekt als String verwendbar machen
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
	protected function load_additional_data_parts() {
		$factory = new fl_factory();
		$factory->set_data_access($this->db);

		$relations = array(
			'has_one' => array(
				'type' => array(
					'loader' => 'activerecord',
					'relation' => 'hasone'
				),
				'standards' => array(
					'class'=>get_class($this).'/%s',
					'key_name'=>'%s_id',
					'key'=>'%s_id'
				)
			),
			'has_many' => array(
				'type' => array(
					'loader' => 'activerecord',
					'relation' => 'hasmany'
				),
				'standards' => array(
					'class'=>get_class($this).'/%s',
					'key_name'=>get_class($this).'_id',
					'key'=>'id'
				)
			)
		);

		foreach ($relations as $key => $relation ) {
			$this->load_relations($key, $relation['type'], $relation['standards'], $factory);
		}
	}

	private function load_relations($relation_data_key, $type, $standards, $factory) {
		$relation_data = (array) $this->$relation_data_key;

		foreach ( $relation_data as $name => $information ) {
			if ( is_string($information) and is_numeric($name) ) {
				$name = $information;
				$information = array();
			}

			$class = ( isset($information['class']) )?  $information['class']:
				strtolower(sprintf($standards['class'], $name));

			list($modul, $class_name) = $factory->parse_class_name($class, fl_factory::ONLY_MODULES);

			$key_name = ( isset($information['key_name']) )? $information['key_name']:
				strtolower(sprintf($standards['key_name'], $class_name));

			$key_value = ( isset($information['key']) )? $this->get($information['key']):
				$this->get(strtolower(sprintf($standards['key'], $class_name)));

			$data = ( isset($information['data']) )? $information['data']:
				array();

			$keys = array(
				'key_name' => $key_name,
				'key' => $key_value
			);

			$options = array_combine(
				array_keys($information),
				array_values($information)
			);
			unset(
				$options['class'],
				$options['key_name'],
				$options['key'],
				$options['data']
			);

			$this->set(
				$name, 
				$factory->get_loader($type, $class, $keys, $data, $options)
			);
		}
	}

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
