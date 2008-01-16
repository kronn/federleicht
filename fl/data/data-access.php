<?php
/**
 * Datenzugriff
 *
 * Die Daten können auf verschiedene Arten gespeichert werden.
 * - data_mysql: Speicherung in MySQL-Datenbank
 * - data_pgsql: Speicherung in PostgreSQL-Datenbank
 *
 * Die für den Betrieb notwendigen Daten, wie SQL-Zugangsdaten,
 * Speicherpfade oder FTP-Zugangsdaten werden bei der Objekterstellung
 * als Parameter übergeben
 *
 * @package federleicht
 * @subpackage base
 */
class data_access {
	var $data_source;

	/**
	 * Kontruktor des Datenmodells, enthält Zugangsdaten
	 *
	 * @param array $config
	 */
	function data_access($config = null ) {
		if ( $config === null ) {
			$type = 'data_null';
		} else {
			$type = 'data_' . strtolower($config['type']);
		}

		$registry = registry::getInstance();

		require_once $registry->get('path', 'lib') . 'data/access/' . $type . '.php';
		$this->data_source = new $type($config);
	}

	/**
	 * Datenzugriffsobjekt zurückgeben
	 *
	 * @return object $type
	 */
	function get_data_souce() {
		return $this->data_source;
	}
}
?>
