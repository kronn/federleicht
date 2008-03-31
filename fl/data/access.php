<?php
/**
 * Datenzugriff
 *
 * Die Daten können auf verschiedene Arten gespeichert werden.
 * - data_mysql: Speicherung in MySQL-Datenbank
 * - data_pgsql: Speicherung in PostgreSQL-Datenbank
 * - data_null:  NULL-Objekt, dass wie data_mysql aussieht
 *
 * Die für den Betrieb notwendigen Daten, wie SQL-Zugangsdaten,
 * Speicherpfade oder FTP-Zugangsdaten werden bei der Objekterstellung
 * als Parameter übergeben
 *
 * @package federleicht
 * @subpackage base
 */
class fl_data_access {
	protected $data_source;

	/**
	 * Kontruktor des Datenmodells, enthält Zugangsdaten
	 *
	 * @param array $config
	 */
	public function __construct($config = null ) {
		if ( $config === null ) {
			$type = 'null';
		} else {
			$type =  strtolower($config['type']);
		}
		$object_name = 'data_'.$type;

		$registry = registry::getInstance();

		require_once $registry->get('path', 'lib') . 'data/access/' . $type . '.php';
		$this->data_source = new $object_name($config);
	}

	/**
	 * Datenzugriffsobjekt zurückgeben
	 *
	 * @return data_access
	 */
	public function get_data_souce() {
		return $this->data_source;
	}
}
?>
