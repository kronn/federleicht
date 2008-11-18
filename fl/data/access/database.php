<?php
/**
 * abtrakte Klasse zur Speicherung in Datenbank
 *
 * @version 0.2
 */
abstract class fl_data_access_database {
	protected $lastSQL;
	protected $allSQL = array();
	protected $query_count = 0;

	public $show_errors = false;
	public $convert_results = false;

	/**
	 * Anzahl der Datensätze mit bestimmter Bedingung zurückgeben
	 *
	 * @param string $table		Tabelle in der DB
	 * @param string $condition Bedingung, die überprüft werden soll, optional
	 * @return integer
	 */
	public function count($table, $condition='') {
		$result = $this->retrieve($table, 'COUNT(*) AS anzahl', $condition);
		$anzahl = (integer) $result[0]['anzahl'];
		return $anzahl;
	}

	/**
	 * ID holen
	 *
	 * @param string $table		Tabelle in der DB
	 * @param string $condition Bedingung, mit der gesucht werden
	 * @return integer
	 */
	public function find_id($table, $condition) {
		$result = $this->retrieve($table, 'id', $condition);
		$id = (integer) $result['id'];
		return $id;
	}

	/**
	 * Zuletzt einfügte ID zurückgeben
	 *
	 * @param string $table
	 * @return integer
	 */
	abstract public function last_insert_id($table);

	/**
	 * Eindeutigen Bezeichner für eine Tabelle zurückgeben
	 *
	 * @param string $table
	 * @return string
	 */
	abstract public function get_table_name($table);

	/**
	 * Datenbank-Ergebnisse in richtige Typen umwandeln
	 *
	 * @param string table
	 * @param array $result
	 * @return array
	 */
	abstract public function convert_result($table, array $result);

	/**
	 * Datenbankabfragen loggen
	 */
	protected function log_query($sql) {
		$this->lastSQL = $sql;
		$this->allSQL[] = $sql;
		$this->query_count++;
	}


	public function export_query_log() {
		return $this->allSQL;
	}

	/**
	 * Fehlermeldungen ausgeben und Ausführung stoppen
	 *
	 * @param string $error
	 * @param string $sql
	 */
	protected function error($error, $sql) {
		if ( $this->show_errors OR 
			( error_reporting() > 0 AND ini_get('display_errors') == 1 ) ) {
				/*
			$factory = new fl_factory();
			$err = $factory->get_helper('var_analyze', 'data-access', 'Fehler');
			$err->sql($sql, 'Datenbankabfrage, die zu Fehler gefuehrt hat');
				 */

			/**
			 * Um mehr Informationen mit xDebug erhalten zu koennen, 
			 * das Datenbankobjekt in den lokalen Scope holen
			 */
			$database_object = $this;
		}

		throw new Exception($error);
	}

	public function __toString() {
		return 'Datenbank-Objekt: ' . get_class($this);
	}
}
?>
