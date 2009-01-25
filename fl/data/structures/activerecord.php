<?php
/**
 * ActiveRecord-Objekt
 *
 * @author Matthias Viehweger <kronn@kronn.de>
 * @version 0.2
 * @package federleicht
 * @subpackage data
 *
 * @todo validator in Basisklasse instanziieren?
 * @todo active_record als Datenzugriffsklasse (und nicht als Datenstruktur) im richtigen Verzeichnis ablegen und von dort laden lassen.
 * @todo active_record sollte auch das Interface data_access und data_wrapper implementieren, da es sowohl Datenzugriff wie Daten selbst darstellt.
 */
abstract class fl_data_structures_activerecord implements data_wrapper, data_access {
	/**
	 * Instanzvariablen
	 */
	protected $db = null;
	protected $table = '';
	protected $data = null;
	public $id = null;
	protected $filter_conditions = '';
	protected $field_cache = null;

	protected $validation_flags = null;

	public $error_messages = array();

	/**
	 * Relationen zu anderen ActiveRecord-Objekten/Datensätzen
	 */
	protected $has_one = array();
	protected $has_many = array();
	protected $belongs_to = array();

	/**
	 * Hilfsklassen
	 */
	protected $factory = null;

	/**
	 * Konstruktor
	 *
	 * @param data_source_access $db
	 * @param string $table
	 * @param int $id
	 * @param data_wrapper $data
	 * @param boolean $loaded
	 */
	public function __construct(data_source_access $db, $table, $id, data_wrapper $data, $loaded=false) {
		$this->db = $db;
		$this->table = ( $this->table !== '' ) ? $this->table : $table;
		$this->id = $id;

		$this->data = $data;

		$this->factory = new fl_factory();
		$this->factory->set_data_access($this->db);

		$this->field_cache = $this->factory->get_structure('data');

		if ( !$loaded ) {
			$this->load();
		} else {
			$this->load_field_cache();
		}
	}

	/**
	 * verwendete Tabelle zurückgeben
	 */
	public function get_table() {
		return $this->table;
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
	public function get($key, $options = null) {
		return $this->data->get($key, $options);
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
	 * Interface data_access
	 */
	public function create(array $data) {
		$this->set_data($data);
		$errors = $this->validate_data($this->validation_flags);

		if ( count($errors) == 0 ) {
			$this->before_create();
			$this->save();
			$this->after_create();
			return $this->id;

		} else {
			$this->error_messages += $errors;
			return 0;
		}
	}
	public function retrieve() {
		$this->load();
		return $this;
	}
	public function update(array $data) {
		$this->set_data($data);

		$errors = $this->validate_data($this->validation_flags);

		if ( count($errors) == 0 ) {
			$result = $this->save();
		} else {
			$this->error_messages += $errors;
			$result = false;
		}

		return $result;
	}
	public function del() {
		return $this->delete();
	}
	/**
	 * Interface data_access ENDE
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
			// Die ID ändert sich nicht, wird also auch nicht gespeichert.
			if ( $key === 'id' ) continue;
			if ( ! $this->field_cache->is_set($key) ) continue;
			// leere Werte werde nicht gespeichert, außer der 0
			if ( $value === null ) continue;
			if ( empty($value) AND $value != 0 ) continue;
			if ( in_array($key, $this->relation_keys()) ) continue;

			$data[$key] = $value;
		}

		return $data;
	}

	/**
	 * Filterbedingungen zurückgeben
	 *
	 * @param string $conn
	 * @return string
	 */
	protected function db_conditions($conn='AND') {
		$db_conditions = ( $this->filter_conditions != '' )?
			' '.$conn.' '.$this->filter_conditions:
			'';

		return $db_conditions;
	}

	/**
	 * Field-Cache laden
	 *
	 * @param array $fields
	 */
	protected function load_field_cache(array $fields = array()) {
		if ( count($fields) == 0 ) {
			$result = $this->db->retrieve($this->table, '*', '', '', '1');
			$columns = array_keys((array) $result[0]);

			if ( fl_registry::get_instance()->is_set('logger') ) {
				fl_registry::get_instance()->get('logger')->log(
					'AR: Fieldcache für "'.$this->table.'" musste manuell geladen werden. ('.$this.')'
				);
			}
		} else {
			$columns = array_values($fields);
		}

		if ( count($columns) > 0 ) {
			$this->field_cache->set_data(
				array_combine($columns, $columns)
			);
		}
	}

	/**
	 * Daten aus Datenbank laden
	 */
	public function load() {
		$this->before_load();

		if ( $this->id > 0 ) {
			$unconverted_result = $this->db->retrieve($this->table, '*', 'id='.$this->id.$this->db_conditions('AND'));

			if ( $this->db instanceof fl_data_access_database ) {
				$result = $this->db->convert_result($this->table, $unconverted_result);
			} else {
				$result = $unconverted_result;
			}

			$data = (array) $result[0];
		} else {
			$data = array();
		}

		$this->load_field_cache(array_keys($data));

		$result = $this->data->set_data($data);

		if ( $this->id > 0 ) {
			$this->load_additional_data_parts();
			$this->after_load();
		}

		return $result;
	}

	/**
	 * Daten in Datenbank speichern
	 *
	 * @return boolean
	 */
	public function save() {
		$this->before_save();
		$this->prepare_data();

		if ( $this->id > 0 ) {
			$result = $this->db->update($this->table, $this->get_data_as_array(), $this->id);
			$this->save_additional_data_parts();
			$this->after_save();
		} else {
			$result = $this->db->create($this->table, $this->get_data_as_array());
			if ( is_numeric($result) ) {
				$this->id = $result;
				$this->save_additional_data_parts();
				$this->after_save();
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
		return 'ActiveRecord-Objekt: '.get_class($this). '#'.$this->id;
	}

	protected function before_save() {}
	protected function after_save() {}
	protected function before_load() {}
	protected function after_load() {}
	protected function before_create() {}
	protected function after_create() {}

	/**
	 * Daten vorbereiten
	 */
	protected function prepare_data() {}

	/**
	 * zusätzliche Daten laden
	 *
	 * Die zusätzlichen Daten können im ActiveRecord-Objekt einstellt werden:
	 *
	 * activerecord::has_one = [ name => info, name2 = info2, ... ]
	 * activerecord::has_many = [ name => info, name2 = info2, ... ]
	 *
	 * info ist dabei ein Array mit den Schlüsseln:
	 *      class     für den Klassen-Identifier
	 *                (modul/klasse)
	 *      key_name  für den Namen zu verwenden Tabellen-Fremdschlüssel
	 *                (Spaltenname in der fremden Tabelle)
	 *      key       für den Namen des Wertes auf den sich der Fremdschlüssel bezieht
	 *                (Spaltenname aus der ursprünglichen Tabelle)
	 *      data      für immer zu übergebende Daten 
	 *                (z.B. für nicht datenbankgestütze Klassen)
	 * 
	 * Beispielsweise
	 * account::has_one = array( 
	 *   'picture' => array(
	 *     'class' => 'account/picture',
	 *     'key' => 'picture_id',
	 *   )
	 * ) 
	 * Bei has_one wird als ID für die Klasse account/picture
	 * der Wert des Schlüssels 'picture_id' aus der Tabelle account
	 * verwendet
	 *
	 * account::has_many = array( 
	 *   'bankaccounts' => array(
	 *     'class' => 'account/financial',
	 *     'key_name' => 'account_id',
	 *     'key' => 'id'
	 *   )
	 * )
	 * Bei has_many wird die ID der Klasse financial/account so ermittelt:
	 * Der Wert des Schlüssel 'id' aus der Tabelle 'account' mit dem Wert
	 * des Schlüssel 'account_id' aus der Tabelle 'financial' verglichen.
	 *
	 * Die Tabellen der Klassen können in den jeweilgen Klassen angegeben werden,
	 * daher reicht der Verweis auf die Klasse.
	 */
	protected function relation_definitions() {
		$relations = array(
			'has_one' => array(
				'type' => array(
					'loader' => 'activerecord',
					'relation' => 'hasone'
				),
				'standards' => array(
					'class'=>get_class($this).'/%s',
					'key_name'=>'id',
					'key'=>'%s_id'
				)
			),
			'belongs_to' => array(
				'type' => array(
					'loader' => 'activerecord',
					'relation' => 'hasone'
				),
				'standards' => array(
					'class'=>'%1$s/%1$s',
					'key_name'=>'id',
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

		return $relations;
	}

	protected function load_additional_data_parts() {
		foreach ($this->relation_definitions() as $key => $relation ) {
			$this->load_relations($key, $relation['type'], $relation['standards']);
		}
	}
	protected function save_additional_data_parts() {
		foreach ($this->relation_definitions() as $key => $relation ) {
			$this->save_relations($key, $relation['type'], $relation['standards']);
		}
	}

	protected function relation_keys() {
		$relation_keys = array();

		foreach( array_keys($this->relation_definitions()) as $rkey ) {
			foreach( $this->$rkey as $key => $content ) {
				if ( is_numeric($key) ) { 
					$relation_keys[] = (string) $content; 
				} else {
					$relation_keys[] = $key;
				}
			}
		}

		return $relation_keys;
	}

	/**
	 * Relationen parsen
	 */
	private function parse_relations($name, $information, $standards) {
		$class = ( isset($information['class']) )?  $information['class']:
			strtolower(sprintf($standards['class'], $name));

		list($modul, $class_name) = $this->factory->parse_class_name($class, fl_factory::ONLY_MODULES);

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

		if ( count($information) > 0 ) {
			$options = array_combine(
				array_keys($information),
				array_values($information)
			);
		} else {
			$options = array();
		}
		if ( isset($options['class']) ) unset($options['class']);
		if ( isset($options['key_name']) ) unset($options['key_name']);
		if ( isset($options['key']) ) unset($options['key']);
		if ( isset($options['data']) ) unset($options['data']);

		return array(
			'class'=>$class,
			'keys'=>$keys,
			'data'=>$data,
			'options'=>$options
		);
	}

	/**
	 * Relationen laden
	 */
	private function load_relations($relation_data_key, $type, $standards) {
		$relation_data = (array) $this->$relation_data_key;

		foreach ( $relation_data as $name => $information ) {
			if ( is_string($information) and is_numeric($name) ) {
				$name = $information;
				$information = array();
			}

			$loader_args = $this->parse_relations($name, $information, $standards);
			$this->set(
				$name, 
				$this->factory->get_loader(
					$type, 
					$loader_args['class'], 
					$loader_args['keys'], 
					$loader_args['data'], 
					$loader_args['options']
				)
			);
		}
	}

	/**
	 * Relationen speichern
	 */
	private function save_relations($relation_data_key, $type, $standards) {
		if ( !in_array($relation_data_key, array('has_one', 'has_many')) ) {
			return; // nur Speichern, wenn es sich um Kinder handelt
		}

		$relation_data = (array) $this->$relation_data_key;

		foreach ( $relation_data as $name => $information ) {
			if ( is_string($information) and is_numeric($name) ) {
				$name = $information;
				$information = array();
			}

			$saver_args = $this->parse_relations($name, $information, $standards);
			$data = $this->get($name);
			
			// nur, wenn es ein Array ist, handelt es sich um geänderte Daten
			if ( is_array($data) ) {
				if ( isset($data['deleted']) ) {
					if ( !empty($data['deleted']) ) {
						foreach( explode(',', $data['deleted']) as $del_id ) {
							// $this->factory->get_ar_class($saver_args['class'], $del_id)->delete();
						}
					}
					unset($data['deleted']);
				}


				foreach ( $data as $key => $values ) {
					if ( !is_array($values) ) continue;
					
					if ( substr($key, 0, 3) == 'new' ) {
						$id = null;
					} else {
						$id = substr($key, strrpos($key, '_'));
					}

					$data_object = $this->factory->get_ar_class(
						$saver_args['class'], $id
					);
					$data_object->set( 
						$saver_args['keys']['key_name'],
						$saver_args['keys']['key']
					);
					$data_object->set_data($values);
					$data_object->save();
				}
			}

			$this->remove($name);
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

	/**
	 * Standardwert setzen
	 *
	 * @param string $key
	 * @param string $value
	 */
	protected function default_value($key, $value) {
		if (!$this->is_set($key) or $this->get($key) == '' ) {
			$this->set($key, $this->get($value));
		}
	}

	/**
	 * number_format rueckgangig machen
	 *
	 * @param string $formatted
	 * @return string $float_suitable
	 */
	protected function revert_number_format($formatted) {
		if ( class_exists('fl_converter') ) {
			return fl_converter::revert_number_format($formatted);
		} else {
			throw new Exception('Typumwandlungs-Klasse nicht gefunden');
		}
	}

	/**
	 * Checkboxen in Wahrheitswerte umwandeln
	 *
	 * Die Funktion verhält sich standardmäßig unauffälig und übergeht fehlende 
	 * (also auch falschgeschriebene) Indizes. Diese Verhalten kann mit dem 
	 * optionalen Parameter $strict so verändert werden, dass fehlende Checkboxen 
	 * mit dem Wert false erzeugt werden. 
	 *
	 * @param string $checkboxes  CSV-String der umzuwandelnden Checkboxen
	 * @param bool   $strict      nichtvorhandene Felder werden mit Wert false erzeugt
	 */
	protected function transform_checkboxes($checkboxes, $strict = false) {
		$this->transform_from_checkboxes($checkboxes, $strict);
	}
	protected function transform_from_checkboxes($checkboxes, $strict = false) {
		$checkboxes = explode(',', $checkboxes);

		foreach( $checkboxes as $checkbox ) {
			$checkbox = trim($checkbox);

			if ( !$this->is_set($checkbox) ) {
				if ( $strict ) {
					$this->set( $checkbox, $this->db->false_value );
				}

				continue;
			}

			( $this->get($checkbox) === $checkbox )?
				$this->set($checkbox, $this->db->true_value):
				$this->set($checkbox, $this->db->false_value);
		}
	}
	protected function transform_to_checkboxes($checkboxes) {
		$checkboxes = explode(',', $checkboxes);

		foreach( $checkboxes as $checkbox ) {
			$checkbox = trim($checkbox);

			( $this->get($checkbox) == $this->db->true_value )?
				$this->set($checkbox, $checkbox):
				$this->set($checkbox, '');
		}
	}
}
