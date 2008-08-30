<?php
/**
 * Allgemeines Datenstrukturobjekt
 *
 * @author Matthias Viehweger <kronn@kronn.de>
 * @version 0.4
 * @package federleicht
 * @subpackage base
 * @todo Datenstruktur "abschliessbar" machen, also hinzufuegen neuer Werte verhindern.
 */
class fl_data_structures_data implements ArrayAccess, data_wrapper {
	/**
	 * Konstruktor
	 *
	 * @param array $data
	 */
	public function __construct($data = null) {
		$this->set_data((array) $data);
	}

	/**
	 * Datenobjekte als String verwendbar machen
	 */
	public function __toString() {
		return 'Datenobjekt: ' . get_class($this);
	}

	/**
	 * Methoden des Interface data_wrapper
	 */
	public function set($key, $value) {
		$this->_set_field($key, $value);
	}
	public function get($key) {
		return $this->_get_field($key);
	}
	public function say($key) {
		echo $this->get($key);
	}

	public function set_data(array $data) {
		foreach ($data as $key => $value ) {
			$this->set($key, $value);
		}
	}
	public function is_set($key) {
		return $this->_isset_field($key);
	}
	public function remove($key) {
		$this->_unset_field($key);
	}
	/**
	 * data_wrapper Ende
	 */

	/**
	 * Methoden des Interface ArrayAccess
	 */
	public function offsetExists($offset) {
		return $this->is_set($offset);
	}
	public function offsetGet($offset) {
		return $this->get($offset);
	}
	public function offsetSet($offset, $value) {
		return $this->set($offset, $value);
	}
	public function offsetUnset($offset) {
		return $this->remove($offset);
	}
	/**
	 * ArrayAccess Ende
	 */

	/** ====== Zugriffsfunktionen auf die internen Daten ====== */
	/**
	 * Daten aus Objekt holen
	 *
	 * @param string $key
	 * @return mixed
	 */
	protected function _get_field($key) {
		$fallback_method = 'get_'.$key;

		if ( method_exists( $this, $fallback_method ) ) {
			$value = $this->$fallback_method();
		} elseif ( $this->_isset_field($key) ) {
			$value = $this->$key;
		} else {
			$value = ( isset($this->_default) )? $this->_default: '';
		}

		return $value;
	}

	/**
	 * Daten in Datenobjekt schreiben
	 *
	 * es wird eine Liste der hinzugefügten Schlüssel geführt.
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	protected function _set_field($key, $value) {
		$this->$key = $value;
	}

	/**
	 * Datenfeld aus Datenobjekt loeschen
	 *
	 * @param string $key
	 */
	protected function _unset_field($key) {
		if ( $this->_isset_field($key) ) {
			unset($this->$key);
		}
	}

	/**
	 * Pruefen, ob Datenfeld existiert
	 *
	 * @param string $key
	 * @return boolean
	 */
	protected function _isset_field($key){
		return isset($this->$key);
	}
}
