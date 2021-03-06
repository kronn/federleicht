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
	protected $_default_value;
	protected $_fallback_prefix;

	/**
	 * Konstruktor
	 *
	 * @param array $data
	 */
	public function __construct(array $data = array()) {
		$this->set_data($data);
		$this->_fallback_prefix = 'get_';
	}

	/**
	 * Standardwert setzen
	 *
	 * @param mixed $default
	 */
	public function default_value($default = null) {
		$this->_default_value = $default;
	}

	/**
	 * Daten als Array exportieren
	 */
	public function export() {
		$export = array();

		foreach( $this as $key => $value ) {
			$export[$key] = $value;
		}

		return $export;
	}

	/**
	 * Wert direkt holen
	 */
	public function value_of($key) {
		return $this->_get_value($key);
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
		return $this->_set_field($key, $value);
	}
	public function get($key, $value_preferred = null) {
		$value_preferred = ( $value_preferred !== null ) ? true: false;

		if ( $value_preferred ) {
			return $this->_prefer_value($key);
		} else {
			return $this->_prefer_method($key);
		}
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
		return ( $this->_method_defined($key) or $this->_isset_field($key) );
	}
	public function remove($key) {
		return $this->_unset_field($key);
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
	 * Daten aus Objekt holen, vorrangig per entsprechender Methode
	 *
	 * @param string $key
	 * @return mixed
	 */
	protected function _prefer_method($key) {
		if ( $this->_method_defined($key) ) {
			$fallback_method = $this->_fallback_prefix . $key;
			$value = $this->$fallback_method();
		} else {
			$value = $this->_get_value($key);
		}

		return $value;
	}

	protected function _prefer_value($key) {
		if ( $this->_isset_field($key) or !$this->_method_defined($key) ) {
			$value = $this->_get_value($key);
		} else {
			$fallback_method = $this->_fallback_prefix . $key;
			$value = $this->$fallback_method();
		}

		return $value;
	}

	/**
	 * Datenwert aus Objekt holen
	 *
	 * @param string $key
	 * @return mixed
	 */
	protected function _get_value($key) {
		if ( $this->_isset_field($key) ) {
			if ( $this->$key instanceof data_loader ) {
				$this->set($key, $this->$key->execute());
				$value = $this->$key;
			} else {
				$value = $this->$key;
			}
		} else {
			$value = ( isset($this->_default_value) )? $this->_default_value: '';
		}

		return $value;
	}

	/**
	 * Daten in Datenobjekt schreiben
	 *
	 * @param string $key
	 * @param mixed  $value
	 * @return boolean
	 */
	protected function _set_field($key, $value) {
		$this->$key = $value;
		return ( $this->$key === $value );
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

		return ( !$this->_isset_field($key) );
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

	/**
	 * Pruefen, ob Funktion definiert ist
	 *
	 * @param string $key
	 * @return boolean
	 */
	protected function _method_defined($key) {
		return method_exists( $this, $this->_fallback_prefix . $key);
	}
}
