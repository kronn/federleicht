<?php 
/**
 * Registry für Federleicht
 *
 * @package federleicht
 * @subpackage base
 */
class registry {
	var $values  = array();

	/**
	 * Konstruktor
	 */
	function registry() {
	}

	/**
	 * Instanz holen
	 */
	function &getInstance() {
		if ( !isset($GLOBALS['federleicht_registry_instance']) ) { 
			$GLOBALS['federleicht_registry_instance'] = new registry(); 
		} 
		
		return $GLOBALS['federleicht_registry_instance'];
	}

	/**
	 * Allgemeines GET
	 *
	 * Der interne Variablenspeicher wird erst nach dem Schlüssel durchsucht.
	 * Wenn weiterhin ein Index gegeben ist, wird versucht, die Variable als 
	 * Array oder Objekt zu verwenden und so den Variableninhalt direkt 
	 * zurückzugeben.
	 *
	 * Wenn der angefragte Variableninhalt nicht gefunden wurde, wird FALSE 
	 * zurückgegeben.
	 *
	 * @param  string $key
	 * @paran  string $index
	 * @return mixed
	 */
	function get($key, $index = null) {
		$key = (string) $key;
		$methodcall = 'get_'.$index;

		if ( isset($this->values[$key]) ) {
			$value = $this->values[$key];
		} else {
			$value = FALSE;
		}

		if ( $index !== null ) {
			if ( is_array($value) AND isset($value[$index]) ) {
				$value = $value[$index];
			} elseif ( is_object($value) AND method_exists($value, $methodcall) ) {
				$value = $value->$methodcall();
			} elseif ( is_object($value) AND isset($value->$index) ) {
				$value = $value->$index;
			} else {
				$value = FALSE;
			}
		}

		return $value;
	}

	/**
	 * Allgemeines SET
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	function set($key, $value) {
		$key = (string) $key;

		$this->values[$key] = $value;
	}
}
