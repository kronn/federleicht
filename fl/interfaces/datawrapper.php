<?php
/**
 * Interface fuer Datenwrapper
 *
 * @author Matthias Viehweger <kronn@kronn.de>
 * @version 0.1
 * @package federleicht
 * @subpackage base
 */
interface datawrapper {
	/**
	 * Einen Wert setzen
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	function set($key, $value);

	/**
	 * Einen Wert holen
	 *
	 * @param string $key
	 * @return mixed
	 */
	function get($key);

	/**
	 * Einen Wert ausgeben
	 *
	 * @param string $key
	 */
	function say($key);

	/**
	 * alle Daten holen
	 * 
	 * @return array
	 */
	function get_data();

	/**
	 * ein Array von Daten setzen
	 *
	 * @param array $data
	 */
	function set_data($data);
}
