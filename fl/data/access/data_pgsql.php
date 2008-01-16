<?php
/**
 * Speicherung in einer PostgreSQL-Datenbank
 *
 * Der Zugriff auf die Datenbank erfolgt über CRUD ZugriffsFunktionen
 * - create
 * - retrieve
 * - update
 * - del
 *
 * Weiterhin gibt es:
 * - query
 *
 * @version 0.1
 */
class data_pgsql {
	var $connection;

	var $lastSQL = '';
	var $allSQL = array();
	var $query_count = 0;
	var $table_prefix = '';
	var $schema = '';

	var $show_errors = FALSE;

	function data_pgsql($config) {
		$this->table_prefix = (string) $config['table_prefix'];
		$this->schema = ( isset($config['schema']) )?
			$config['schema']:
			'public';

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
	function create($table, $data) {
		$rows = array_keys($data);

		$values = array();
		foreach( array_values($data) as $value ) {
			$values[] = $this->_secureFieldContent($value);
		}

		$sql  = 'INSERT INTO '. $this->_tableName($table);
		$sql .= ' ( ' . implode(', ', $rows) . ' ) ';
		$sql .= " VALUES ( '". implode("', '", $values) . "' );";

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
	function retrieve($table, $field='*', $condition='', $order='', $limit='') {
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

		$result = $this->query($sql);
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
	function update($table, $data, $id, $id_field='id', $all=FALSE) {
		$data_length = count($data);
		$i = 0;

		$sql = "UPDATE ".$this->table_prefix.$table." SET".PHP_EOL;
		foreach ($data as $field=>$content) {
			$this->_secureFieldContent($content);

			$sql .= " ".$field."='".$content."'";
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
	function del($table, $id) {
		if ( !is_numeric($id) ) {
			return FALSE;
		}

		$sql = "DELETE FROM ".$this->table_prefix.$table."
		 WHERE id='".$id."' LIMIT 1;";

		$result = $this->query($sql);
		return $result;
	}

	/**
	 * Tabelle leeren
	 *
	 * @param string $table Tabellennname
	 * @return boolean
	 */
	function clear_table($table) {
		$sql = 'TRUNCATE TABLE '.$this->table_prefix.$table;

		$result = (boolean) $this->query($sql);
		return $result;
	}

	/**
	 * Tabelle optimieren
	 *
	 * @param string $table Tabellenname
	 * @return boolean
	 */
	function optimize_table($table) {
		$sql = 'OPTIMIZE TABLE ' . $this->table_prefix.$table;

		$result = (boolean) $this->query($sql);
		return $result;
	}

	/**
	 * Anzahl der Datensätze mit bestimmter Bedingung zurückgeben
	 *
	 * @param string $table		Tabelle in der DB
	 * @param string $condition Bedingung, die überprüft werden soll, optional
	 * @return integer
	 */
	function count($table, $condition='') {
		$result = $this->retrieve($table, 'COUNT(*) AS anzahl', $condition);
		$anzahl = (integer) $result['anzahl'];
		return $anzahl;
	}

	/**
	 * ID holen
	 *
	 * @param string $table		Tabelle in der DB
	 * @param string $condition Bedingung, mit der gesucht werden
	 * @return integer
	 */
	function find_id($table, $condition) {
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
	function last_insert_id($table) {
		$id = $this->query('SELECT last_value FROM '.$table.'_id_seq');
		return $id;
	}

	/**
	 * Datenbankabfrage als SQL abgeben
	 *
	 * @param string $sql
	 * @return mixed
	 */
	function query($sql) {
		return $this->_query_db($sql);
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
	function _open_db($host, $db, $user, $pass) {
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
	function _query_db($sql) {
		if ( is_array($sql) ) {
			foreach ( $sql as $nr => $query ) {
				$output[$nr] = $this->query( $query );
			}

		} else {
			$this->_logSQL($sql);

			$output = array();

			$abfrage = is_string($sql) ? trim($sql) :  $this->_error('Fehlerhafte Daten', $sql);
			$abfragetyp = strtoupper(substr($abfrage,0,6));

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
			}
			
			if ( $abfragetyp !== 'SELECT' ) {
				$output = ( pg_result_status($this->connection, $result) === PGSQL_COMMAND_OK )?  
					true:
					false;
			} else {
				$output = pg_fetch_all($result);

				if ( count($output) > 100 ) {
					pg_free_result($result);
				}
			}
			unset($abfragetyp);

		}
		return $output;
	}

	/**
	 * Tabellenbezeichner herstellen
	 *
	 * @param string $table
	 * @return string
	 */
	function _tableName($table) {
		return $this->schema . '.' . $this->table_prefix . $table;
	}

	/**
	 * Feldinhalte gegen Hackingversuche schützen
	 *
	 * Dies sind nur grundlegende Schutzmaßnahmen
	 *
	 * @param mixed &$var
	 */
	function _secureFieldContent(&$var){
		if ( is_array($var) ) {
			$varvalue = var_export($var, TRUE);
			$this->_error('Array wurde uebergeben, kann aber nicht gespeichert werden.', $varvalue );
		}
		return $var = pg_escape_string($var);
	}

	/**
	 * Datenbankabfragen loggen
	 */
	function _logSQL($sql) {
		$this->lastSQL = $sql;
		$this->allSQL[] = $sql;
	}

	/**
	 * Fehlermeldungen ausgeben und Ausführung stoppen
	 *
	 * @todo in Fehlerbehandlungsklasse auslagern
	 */
	function _error($error, $sql) {
		if ( $this->show_errors OR 
			( error_reporting() > 0 AND ini_get('display_errors') == 1 ) ) {
			needs('var_analyze');
			$err = new var_analyze('data-access', 'Fehler');
			$err->sql($sql, 'Datenbankabfrage, die zu Fehler gefuehrt hat'); 
		}

		trigger_error($error, E_USER_ERROR);
	}
}
?>
