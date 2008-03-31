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
class fl_data_structures_data implements ArrayAccess {
	/**
	 * Daten direkt ausgeben
	 *
	 * @param string $key
	 */
	public function say($key) {
		echo $this->get($key);
	}

	/**
	 * Daten zurückgeben
	 * 
	 * @param string $key
	 * @return mixed
	 */
	public function get($key) {
		return $this->_get_field($key);
	}

	/**
	 * Daten in Datenstruktur setzen
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	public function set($key, $value) {
		$this->_set_field($key, $value);
	}

	/**
	 * Daten in Datenstruktur loeschen
	 *
	 * @param string $key
	 */
	public function remove($key) {
		$this->_unset_field($key);
	}

	/**
	 * Ein assoziatives Array als Daten übernehmen
	 *
	 * @param array $data
	 */
	public function set_data($data) {
		foreach ($data as $key => $value ) {
			$this->set($key, $value);
		}
	}

	/**
	 * Pruefen, ob eine Datenfeld existiert
	 * 
	 * @param string $key
	 * @return boolean
	 */
	public function is_set($key) {
		return $this->_isset_field($key);
	}

	public function get_data() {
		throw new Exception('Funktion nicht mehr unterstuetzt. "foreach($data_structure as $key=>value) { $data[$key] = $value }" verwenden!');
	}
	
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
		if ( $this->_isset_field($key) ) {
			$value = $this->$key;
		} else {
			$fallback_method = 'get_'.$key;

			if ( method_exists( $this, $fallback_method ) ) {
				$value = $this->$fallback_method();
			} else {
				$value = ( isset($this->_default) )? $this->_default: '';
			}
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
