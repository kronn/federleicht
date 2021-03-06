<?php
/**
 * Speicherung in einer PostgreSQL-Datenbank
 *
 * Der Zugriff auf die Datenbank erfolgt über CRUD ZugriffsFunktionen
 * - create
 * - retrieve
 * - update
 * - del
 * - query
 * Dies wird durch das Interface data_source_access ausgedrückt.
 *
 * @version 0.3
 */
class fl_data_access_pgsql extends fl_data_access_database implements data_source_access {
	protected $connection;
	protected $database;

	public $table_prefix = '';
	public $schema = '';

	public $true_value = 't';
	public $false_value = 'f';

	public function __construct($config) {
		$this->table_prefix = (string) $config['table_prefix'];
		$this->schema = ( isset($config['schema']) )?
			$config['schema']:
			'public';

		$this->database = $config['database'];

		$this->_open_db($config['host'], $config['database'], $config['user'], $config['pass']);
	}

	/**
	 * Datenbankeintrag erzeugen
	 *
	 * Die create-Methode bietet eine Schnittstelle, um Daten zur Datenbank
	 * hinzuzufügen.
	 *
	 * @param string $table Tabellenname
	 * @param array  $data  assoziatives Array, das die Daten enthält.
	 * @param string $type  Art der Einfügeoperation (INSERT, INSERT IGNORE, REPLACE)
	 * @return string Ergebnis der Datenbankoperation
	 */
	public function create($table, array $data) {
		$this->query_details = array(
			'table' => $table,
			'data' => $data
		);

		$rows = array_keys($data);
		$types = $this->get_table_information($table);

		$values = array();

		foreach( $data as $key => $value ) {
			$values[] = $this->prepare_value_for_db($value, $types[$key]);
		}

		$sql  = 'INSERT INTO '. $this->_tableName($table);
		$sql .= ' ( ' . implode(', ', $rows) . ' ) ';
		$sql .= " VALUES ( ". implode(", ", $values) . " );";

		return ( $this->query($sql) )?
			$this->last_insert_id($table):
			false;
	}

	/**
	 * Datenbankeintrag holen
	 *
	 * Die retrieve-Methode bietet eine Schnittstelle, um Daten aus der
	 * Datenbank zu lesen.
	 *
	 * @param string $table Tabellenname
	 * @param string $field Feldnamen, die abgefragt werden
	 * @param string $condition Bedigungen, nach denen die Tabellenzeilen ausgewählt werden
	 * @param string $order Sortierungsreihenfolge
	 * @param string $limit maximale Anzahl von Zeilen
	 * @return array Assoziatives Array mit den Daten.
	 */
	public function retrieve($table, $field='*', $condition='', $order='', $limit='') {
		$this->query_details = array(
			'table' => $table,
			'data' => $field
		);

		if ( $limit == '') {
			$sql_limit = false;
		} elseif ( strpos($limit, ',') === false ) {
			$sql_limit = '0,' . $limit;	
		} else {
			$sql_limit = $limit;
		}

		$sql = 'SELECT '.$field.' FROM '.$this->schema.'.'.$this->table_prefix.$table;
		if ( !empty($condition) )
			$sql .= ' WHERE '.$condition;
		if ( !empty($order) )
			$sql .= ' ORDER BY '.$order;
		if ( $sql_limit !== false ) {
			list($offset, $sql_limit) = explode(',', $sql_limit);
			$sql .= ' LIMIT '.$sql_limit . ' OFFSET '. $offset;
		}

		$sql .= ';';

		$result = $this->query($sql);

		if ( $result === false ) {
			$result = array();
		}

		return $result;
	}

	/**
	 * Datenbankeintrag aktualisieren
	 *
	 * Die update-Methode bietet eine Schnittstelle, um Daten in der Datenbank
	 * zu aktualisieren. Dies ist die vermutlich die häufigste Form der
	 * Speicherung.
	 *
	 * @param string  $table	 Tabellenname
	 * @param array   $data	  assoziatives Array, das die Daten enthält.
	 * @param int	 $id		id des Datenbankeintrages
	 * @param string  $id_field  Feldname des id-Feldes
	 * @param boolean $all	   Alle Zeilen verändern
	 *
	 * @return string Ergebnis der Datenbankoperation
	 */
	public function update($table, array $data, $id, $id_field='id', $all=FALSE) {
		$this->query_details = array(
			'table' => $table,
			'data' => $data
		);

		$types = $this->get_table_information($table);
		$data_length = count($data);
		$i = 0;

		$sql = "UPDATE ".$this->table_prefix.$table." SET".PHP_EOL;
		foreach ($data as $field=>$content) {
			$sql .= " $field = {$this->prepare_value_for_db($content, $types[$field])}";

			if ( ( $data_length - 1 ) > $i++ )
				$sql .= ",";
		}
		if ( !$all ) {
			$sql .= " WHERE ".$id_field."='".$id."' ;";
		} else {
			$sql .= ';';
		}

		$result = $this->query($sql);
		return $result;
	}

	/**
	 * Datenbankeintrag löschen
	 *
	 * Die del-Methode bietet eine Schnittstelle, um Daten aus der Datenbank
	 * zu löschen.
	 *
	 * @param string  $table Tabellenname
	 * @param int	 $id	id des Datenbankeintrages
	 * @return boolean Ergebnis der Datenbankoperation
	 */
	public function del($table, $id) {
		$this->query_details = array(
			'table' => $table,
			'data' => $id
		);

		if ( !is_numeric($id) ) {
			return FALSE;
		}

		$sql = "DELETE FROM $this->table_prefix$table WHERE id=$id;";

		$result = $this->query($sql);
		return $result;
	}

	/**
	 * Datenbank-Ergebnisse in richtige Typen umwandeln
	 *
	 * Es werden die Datentypen boolean, integer und numeric ausgewertet. 
	 * String-Datentypen muessen nicht ausgewertet werden, da PHP diesen
	 * Datentype automatisch annimmt.
	 *
	 * @param string table
	 * @param array $result
	 * @return array
	 */
	public function convert_result($table, array $result) {
		if ( ! $this->convert_results ) {
			return $result;
		}

		$converted = $result;
		$types = $this->get_table_information($table);

		foreach ( $types as $type ) {
			foreach ( $result as $row_num => $rows ) {
				$col = $type['column_name'];
				$new_type = $type['php_type'];

				if ( $new_type == 'boolean' ) {
					$converted[$row_num][$col] = ( $converted[$row_num][$col] == $this->true_value )?
						(boolean) true:
						(boolean) false;
				} else {
					settype($converted[$row_num][$col], $new_type);
				}
			}
		}

		return $converted;
	}

	/**
	 * Metadaten einer Tabelle zurückgeben
	 *
	 * @param string $table  Tabellenname
	 * @return array
	 */
	public function get_table_information($table) {
		if ( ! isset($this->table_info[$table]) ) {
			$sql = <<<SQL
SELECT
	column_name, data_type,
	CASE
		WHEN data_type = 'numeric' THEN 'float'
		WHEN data_type IN ('boolean', 'integer') THEN data_type
		ELSE 'string'
	END AS php_type,
	CASE
		WHEN is_nullable = 'YES' THEN 1
		ELSE 0
	END AS null_allowed
FROM information_schema.columns
WHERE table_name='{$this->table_prefix}{$table}'
SQL;
			$table_info = array();
			foreach( $this->query($sql, false) as $col ) {
				$table_info[$col['column_name']] = $col;
			}

			$this->table_info[$table] = $table_info;
		}

		return $this->table_info[$table];
	}

	/**
	 * Tabelle leeren
	 *
	 * @param string $table Tabellennname
	 * @return boolean
	 * @todo Funktion fuer PostgreSQL umarbeiten
	 */
	public function clear_table($table) {
		return false;

		$sql = 'TRUNCATE TABLE '.$this->table_prefix.$table;

		$result = (boolean) $this->query($sql);
		return $result;
	}

	/**
	 * Tabelle optimieren
	 *
	 * @param string $table Tabellenname
	 * @return boolean
	 * @todo Funktion fuer PostgreSQL umarbeiten, VACCUUM
	 */
	public function optimize_table($table) {
		return false;

		$sql = 'OPTIMIZE TABLE ' . $this->table_prefix.$table;

		$result = (boolean) $this->query($sql);
		return $result;
	}

	/**
	 * Zuletzt einfügte ID zurückgeben
	 *
	 * @param string $table
	 * @return integer
	 */
	public function last_insert_id($table=null) {
		if ( $table !== null ) {
			$index = $this->query($sql = "SELECT column_default FROM information_schema.columns 
				WHERE table_catalog='{$this->database}' AND table_name='{$this->table_prefix}{$table}' AND column_name='id';");
			$index_result = $index[0]['column_default'];
			$index_name = substr(
				$index_result, 
				strpos($index_result, "'") + 1 , 
				strrpos($index_result, "'") - strlen($index_result)
			);
		} else {
			$index_name = '';
		}
		
		/**
		 * Wenn kein Index gefunden wurde, muss die Funktion lastval verwendet werden.
		 * solange keine persistenten Verbindungen verwendeten werden, sollte das auch
		 * ohne Probleme funktionieren.
		 */
		$query = empty($index_name)? 
			'SELECT lastval() as last_value;':
			'SELECT last_value FROM '.$index_name.';';

		try {
			$result = $this->query($query);
			return $result[0]['last_value'];
		} catch ( Exception $e ) {
			return 0;
		}
	}

	// interne Funktionen
	/**
	 * Datenbankverbindung öffnen
	 *
	 * @param string $host
	 * @param string $db
	 * @param string $user
	 * @param string $pass
	 */
	private function _open_db($host, $db, $user, $pass) {
		if ( !extension_loaded('pgsql') ) {
		  die("PHP-Erweiterung 'pgsql' nicht geladen");
		}

		$conn_str = "host='$host' user='$user' password='$pass' dbname='$db'";

		if ( !($this->connection = pg_connect($conn_str)) ) {
			die('Keine Verbindung zur Datenbank m&ouml;glich. (Fehlermeldung: '.pg_last_error().')');
		}

		pg_set_client_encoding($this->connection, 'utf8');
	}

	/**
	 * Datenbank abfragen
	 *
	 * @param mixed $sql String oder Array, das die SQL-Abfragen enthält
	 * @return mixed
	 */
	protected function _query_db($sql) {
		$output = array();

		$abfrage = is_string($sql) ? trim($sql) :  $this->error('Fehlerhafte Daten', $sql);
		$abfragetyp = strtoupper(substr($abfrage,0,6));

		/* Asynchrone Abfrage
		if (!pg_connection_busy($this->connection)) {
			pg_send_query($this->connection, $abfrage);
		} else {
			$this->_error('Verbindung ausgelastet');
		}

		$result = pg_get_result($this->connection);

		// pg_result_error gibt einen String zurück, wenn ein Fehler 
		// vorliegt und FALSE, wenn kein Fehler vorliegt.
		// Es scheint aber auch einen leeren String zurückzugeben, wenn 
		// kein Fehler vorliegt.
		$error = pg_result_error($result);
		if( is_string($error) AND trim($error) !== '' ) {
			$this->_error($error, $abfrage);
		} else {
			$this->query_count++;
			$result_status = pg_result_status($result);
		}
		 */

		/* Einzelne, synchrone Abfrage */
		if ( ($result = @pg_query($this->connection, $abfrage)) === false ) {
			$this->error(pg_last_error($this->connection), $abfrage);
		} else {
			$result_status = pg_result_status($result);
		}


		if ( $abfragetyp !== 'SELECT' ) {
			$output = ( $result_status === PGSQL_COMMAND_OK );
		} else {
			$output = pg_fetch_all($result);

			if ( count($output) > 100 ) {
				pg_free_result($result);
			}
		}

		return $output;
	}

	/**
	 * Tabellenbezeichner herstellen
	 *
	 * @param string $table
	 * @return string
	 */
	protected function _tableName($table) {
		return $this->schema . '.' . $this->table_prefix . $table;
	}
	public function get_table_name($table) {
		return $this->_tableName($table);
	}

	/**
	 * Feldinhalte gegen Hackingversuche schützen
	 *
	 * Dies sind nur grundlegende Schutzmaßnahmen
	 *
	 * @param mixed &$var
	 */
	protected function _secureFieldContent(&$var){
		if ( is_array($var) ) {
			$varvalue = var_export($var, TRUE);
			$this->error('Array wurde uebergeben, kann aber nicht gespeichert werden.', $varvalue );
		}
		return $var = pg_escape_string($var);
	}

	protected function prepare_value_for_db($value, $info = null) {
		if ( $value === null ) {
			$db_value = 'NULL';
		} elseif ( is_bool($value) ) {
			$db_value = "'".($value? $this->true_value: $this->false_value)."'";
		} elseif ( is_numeric($value) ) {
			$db_value = $this->_secureFieldContent($value);
		} elseif ( empty($value) and is_array($info) and $info['null_allowed'] ) {
				$db_value = 'NULL';
		} else {
			$db_value = "'". $this->_secureFieldContent($value) . "'";
		}

		return $db_value;
	}
}
?>
