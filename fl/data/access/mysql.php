<?php
/**
 * Speicherung in einer MySQL-Datenbank
 *
 * Der Zugriff auf die Datenbank erfolgt über CRUD ZugriffsFunktionen
 * - create
 * - retrieve
 * - update
 * - del
 * - query
 *
 * Weiterhin gibt es:
 * - clear_table
 * - optimize_table
 *
 * @version 0.2.1
 */
class fl_data_access_mysql implements data_access {
	protected $connection;
	public $database;

	public $lastSQL = '';
	public $allSQL = array();
	public $query_count = 0;
	public $table_prefix = '';

	public $show_errors = FALSE;

	public $true_value = 1;
	public $false_value = 0;

	public function __construct($config) {
		$this->table_prefix = (string) $config['table_prefix'];
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
	public function create($table, array $data, $type='INSERT') {
		$data_length = count($data);
		$i = 0;

		switch ( strtoupper($type) ) {
		case 'IGNORE':
		case 'INSERT IGNORE':
			$type = 'INSERT IGNORE';
			break;

		case 'REPLACE':
		case 'INSERT':
			$type = strtoupper($type);
			break;

		default:
			$type = 'INSERT';
			break;
		}
	
		$sql = $type . " INTO ".$this->table_prefix.$table." SET ";
		foreach ($data as $field=>$content) {
			$this->_secureFieldContent($content);

			$sql .= " ".$field."='".$content."'";
			if ( ( $data_length - 1 ) > $i++ )
				$sql .= ",";
		}
		$sql .= ";";

		$result = $this->query($sql);
		return $result;
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
		if ( $limit == '') {
			$sql_limit = FALSE;
		} elseif ( strpos($limit, ',') === FALSE ) {
			$sql_limit = '0,' . $limit;	
		} else {
			$sql_limit = $limit;
		}

		$sql = "SELECT ".$field." FROM ".$this->table_prefix.$table;
		if ( !empty($condition) )
			$sql .= " WHERE ".$condition;
		if ( !empty($order) )
			$sql .= " ORDER BY ".$order;
		if ( $sql_limit !== FALSE )
			$sql .= " LIMIT ".$sql_limit;

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
	public function update($table, array $data, $id, $id_field='id', $all=FALSE) {
		$data_length = count($data);
		$i = 0;

		$sql = "UPDATE ".$this->table_prefix.$table." SET".PHP_EOL;
		foreach ($data as $field=>$content) {
			$this->_secureFieldContent($content);

			$sql .= " ".$field."='".$content."'";
			if ( ($data_length - 1 ) > $i++ )
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
		if ( !is_numeric($id) ) {
			return FALSE;
		}

		$sql = "DELETE FROM ".$this->table_prefix.$table."
		 WHERE id='".$id."' LIMIT 1;";

		$result = $this->query($sql);
		return $result;
	}

	/**
	 * Datenbank-Ergebnisse in richtige Typen umwandeln
	 *
	 * @param string table
	 * @param array $result
	 * @return array
	 * @todo Funktion fuer MySQL umarbeiten
	 */
	public function convert_result($table, $result) {
		return $result;

		$table = $this->table_prefix . $table;
		$converted = $result;

		$sql = <<<SQL
SELECT column_name AS col, CASE
	WHEN data_type = 'numeric' THEN 'float'
	ELSE data_type
END AS type
FROM information_schema.columns
WHERE table_name='{$table}'
	AND data_type IN ('boolean', 'integer', 'numeric');
SQL;
		$types = $this->query($sql);

		foreach ( $result as $row_num => $rows ) {
			foreach ( $types as $type ) {
				$col = $type['col'];
				$new_type = $type['type'];
				$converted[$row_num][$col] = settype($row[$col], $new_type);
			}
		}

		return $converted;
	}

	/**
	 * Tabelle leeren
	 *
	 * @param string $table Tabellennname
	 * @return boolean
	 */
	public function clear_table($table) {
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
	public function optimize_table($table) {
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
	public function count($table, $condition='') {
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
	public function find_id($table, $condition) {
		$result = $this->retrieve($table, 'id', $condition);
		$id = (integer) $result['id'];
		return $id;
	}

	/**
	 * Datenbankabfrage als SQL abgeben
	 *
	 * @param string $sql
	 * @return mixed
	 */
	public function query($sql) {
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
	private function _open_db($host, $db, $user, $pass) {
		if ( !extension_loaded('mysql') ) {
		  die("PHP-Erweiterung 'mysql' nicht geladen");
		}

		$this->connection = @mysql_connect( $host, $user, $pass) 
			OR die("Keine Verbindung zur Datenbank m&ouml;glich. Fehlermeldung: ".mysql_error());
		$this->database = $db;

		mysql_query('SET NAMES "utf8";');

		$this->_select_db();
	}

	/**
	 * Datenbank auswählen
	 */
	private function _select_db() {
		mysql_select_db($this->database) 
			OR die("Konnte Datenbank nicht benutzen, Fehlermeldung: ".mysql_error());
	}

	/**
	 * Datenbank abfragen
	 *
	 * @param mixed $sql String oder Array, das die SQL-Abfragen enthält
	 * @todo Funktion überarbeiten
	 * @return mixed
	 */
	private function _query_db($sql) {
		$this->_select_db();

		if ( is_array($sql) ) {
			foreach ( $sql as $nr => $query ) {
				$output[$nr] = $this->query( $query );
			}

		} else {
			$this->_logSQL($sql);

			$output = array();
			$aktionen = array('UPDATE','DELETE', 'ALTER ', 'CREATE', 'DROP T', 'TRUNCA', 'REPLAC', 'OPTIMI');

			$abfrage = is_string($sql) ? trim($sql) :  $this->_error('Fehlerhafte Daten', $sql);

			$result = mysql_query($abfrage) OR $this->_error(mysql_error(), $sql);
			$this->query_count++;

			$abfragetyp = strtoupper(substr($abfrage,0,6));

			if ( ( in_array($abfragetyp,$aktionen) ) ) {
				$output = ( $result )? TRUE: FALSE;
			} elseif ( ($abfragetyp == 'INSERT') ) {
				$output = ( $result )? mysql_insert_id(): FALSE;
			} else {
				while($row = mysql_fetch_assoc($result)) {
					$output[] = $row;
				}
			}
			unset($abfragetyp);
			unset($aktionen);

		}
		return $output;
	}

	/**
	 * Feldinhalte gegen Hackingversuche schützen
	 *
	 * Dies sind nur grundlegende Schutzmaßnahmen
	 *
	 * @param mixed &$var
	 */
	private function _secureFieldContent(&$var){
		if ( is_array($var) ) {
			$varvalue = var_export($var, TRUE);
			$this->_error('Array sollte gespeichert werden.', $varvalue );
		}
		$var = mysql_real_escape_string($var, $this->connection);
	}

	/**
	 * Datenbankabfragen loggen
	 */
	private function _logSQL($sql) {
		$this->lastSQL = $sql;
		$this->allSQL[] = $sql;
	}

	/**
	 * Fehlermeldungen ausgeben und Ausführung stoppen
	 *
	 * @todo in Fehlerbehandlungsklasse auslagern
	 */
	private function _error($error, $sql) {
		if ( $this->show_errors OR 
			( error_reporting() > 0 AND ini_get('display_errors') == 1 ) ) {
			require_once ABSPATH . 'app/helper/var_dump.php';
			$err = new varDumper('data-access', 'Fehler');
			$err->sql($sql, 'Datenbankabfrage, die zu Fehler gefuehrt hat'); 
		}

		trigger_error($error, E_USER_ERROR);
	}
}
?>
