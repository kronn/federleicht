<?php
/**
 * Erstellung und Verwaltung von Response-Objekten
 *
 * @package federleicht
 * @subpackage base
 */
class fl_responder implements data_wrapper, Iterator {
	protected $response_list = array();
	private $key = 0;

	public function __construct(fl_factory $factory) {
		$this->factory = $factory;
	}

	/**
	 * Neue Response hinzufügen
	 *
	 * Die neue Response wird als aktuelle Response gesetzt, um die
	 * Schreibarbeit in den anwendenden Klassen zu verringern.
	 *
	 * @param string $type
	 * @return int $response_key
	 */
	public function add_response($type) {
		$this->response_list[] = $this->factory->create('mvc_response_'.$type);

		$response_key = count($this->response_list) - 1;
		$this->set_current_response($response_key);

		return $response_key;
	}

	/**
	 * Aktuell zu bearbeitende Response setzen
	 *
	 * @param int $response_key
	 * @throws OutOfBoundsException
	 */
	public function set_current_response($response_key) {
		$previous_key = $this->key();
		$this->key = $response_key;

		if ( ! $this->valid() ) {
			$this->key = $previous_key;
			throw new OutOfBoundsException("Response {$response_key} ist nicht im Responder vorhanden");
		}
	}

	/** 
	 * Interface data_wrapper
	 *
	 * weil die Response-Objekte dieses Interface implementieren
	 */
	public function set($key, $value) {
		$response = $this->current();
		$response->set($key, $value);
	}
	public function get($key) {
		$response = $this->current();
		return $response->get($key);
	}
	public function say($key) {
		$response = $this->current();
		$response->say($key);
	}

	public function set_data(array $data) {
		$response = $this->current();
		$response->set_data($data);
	}
	public function is_set($key) {
		$response = $this->current();
		return $response->is_set($key);
	}
	public function remove($key) {
		$response = $this->current();
		$response->remove($key);
	}
	/** Ende des Interface data_wrapper */

	/** 
	 * Interface Iterator 
	 *
	 * Wird implementiert, um in fl_modul::start_execution() die verschiedenen 
	 * Rückgaben einzeln zu bearbeiten.
	 */
	public function &current() {
		return $this->response_list[$this->key()];
	}
	public function key() {
		return $this->key;
	}
	public function next() {
		$this->key++;
	}
	public function rewind() {
		$this->key = 0;
	}
	public function valid() {
		return $this->key < count($this->response_list);
	}
	/** Ende des Interface Interface */
}
