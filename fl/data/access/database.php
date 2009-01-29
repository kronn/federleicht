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
	protected $total_db_time = 0.0;

	public $show_errors = false;
	public $convert_results = true;
	protected $table_info = array();

	protected $query_details = array();

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
	 * @param string $table  optional
	 * @return integer
	 */
	abstract public function last_insert_id($table=null);

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
	 * Metadaten einer Tabelle zurückgeben
	 *
	 * @param string $table  Tabellenname
	 * @return array
	 */
	abstract public function get_table_information($table);

	/**
	 * Datenbankabfragen loggen
	 */
	protected function log_query($sql, fl_timer $timer) {
		$time = $timer->get_time();
		$this->total_db_time += $time;

		$this->query_count++;

		if ( $sql == '' ) return;

		$this->lastSQL = $sql;
		$this->allSQL[] = $sql;

		if ( fl_registry::get_instance()->is_set('logger') ) {
			fl_registry::get_instance()->get('logger')->log(
				'DB: '.$sql. ' ('. $timer->format_time($time).'s)',
				fl_logger::WITHOUT_TIME
			);
		}
	}

	public function export_query_stats() {
		return array(
			'time'=>$this->total_db_time,
			'count'=>$this->query_count
		);
	}

	public function export_query_log() {
		return $this->allSQL;
	}

	/**
	 * SQL-Anfrage an Datenbank senden
	 *
	 * @param mixed $sql
	 * @param boolean $log_query
	 * @return mixed
	 */
	public function query($sql, $log_query = true) {
		if ( is_array($sql) ) {
			foreach ( $sql as $nr => $query ) {
				$output[$nr] = $this->query( $query );
			}

		} else {
			$timer = new fl_timer();
			$timer->start();

			$output = $this->_query_db( $sql );
			if ( ! $log_query ) $sql = '';

			$timer->stop();
			$this->log_query($sql, $timer);
			unset($timer);
		}
		return $output;
	}

	/**
	 * Datendank nutzen
	 */
	abstract protected function _query_db($sql);

	/**
	 * Fehlermeldungen ausgeben und Ausführung stoppen
	 *
	 * @param string $error
	 * @param string $sql
	 */
	protected function error($error, $sql) {
		$error = new SqlException($error, $sql);
		$error->setTable($this->query_details['table']);
		$error->setData($this->query_details['data']);
		throw $error;
	}

	public function __toString() {
		return 'Datenbank-Objekt: ' . get_class($this);
	}
}
?>
