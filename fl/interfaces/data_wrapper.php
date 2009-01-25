<?php
/**
 * Interface fuer Datenwrapper
 *
 * @author Matthias Viehweger <kronn@kronn.de>
 * @version 0.2
 * @package federleicht
 * @subpackage base
 */
interface data_wrapper {
	public function set($key, $value);
	/**
	 * @param string $key
	 * @param mixed  $options  Zugriffsoptionen der implementierenden Klasse, z.B. ob auf den Wert direkt oder per Methode zugegriffen werden soll.
	 */
	public function get($key, $options = null);
	public function say($key);

	public function set_data(array $data);
	public function is_set($key);
	public function remove($key);
}
