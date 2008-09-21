<?php 
/**
 * Registry für Federleicht
 *
 * @package federleicht
 * @subpackage base
 */
class fl_registry {
	protected $values;
	private static $instance;

	/**
	 * Konstruktor
	 */
	protected function __construct() {
		$this->values = array();
	}

	/**
	 * fl_registry ist ein Singleton, folglich darf es nicht geklont (kopiert) werden.
	 *
	 * Da die Funktion nicht getestet werden kann, ohne einen Fatal Error auszuloesen, 
	 * wird sie bei Testcoverage-Analyse ausgespart.
	 *
	 * @codeCoverageIgnoreStart
	 */
	private function __clone() {}
	// @codeCoverageIgnoreEnd

	/**
	 * Instanz holen
	 * @deprecated
	 */
	public static function getInstance() {
		return self::get_instance();
	}

	/**
	 * Instanz holen
	 */
	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
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
	public function get($key, $index = null) {
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
	public function set($key, $value) {
		$key = (string) $key;

		$this->values[$key] = $value;
	}

	/**
	 * Prüfung, ob Wert existiert
	 *
	 */
	public function is_set($key) {
		$available = false;

		if ( isset($this->values[$key]) ) {
			$available = true;
		}

		return $available;
	}
}
