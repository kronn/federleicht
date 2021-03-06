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
class fl_data_access_mysql extends fl_data_access_database implements data_source_access {
	protected $connection;
	public $database;

	public $table_prefix = '';

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
		$this->query_details = array(
			'table' => $table,
			'data' => $data
		);

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
	
		$sql = $type . " INTO ".$this->get_table_name($table)." SET ";
		foreach ($data as $field=>$content) {
			$this->_secureFieldContent($content);
			$this->_quoteFieldName($field);

			$sql .= " ".$field."='".$content."'";
			if ( ( $data_length - 1 ) > $i++ )
				$sql .= ",";
		}

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
		$this->query_details = array(
			'table' => $table,
			'data' => $field
		);

		if ( $limit == '') {
			$sql_limit = FALSE;
		} elseif ( strpos($limit, ',') === FALSE ) {
			$sql_limit = '0,' . $limit;	
		} else {
			$sql_limit = $limit;
		}

		$sql = "SELECT ".$field." FROM ".$this->get_table_name($table);
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
		$this->query_details = array(
			'table' => $table,
			'data' => $data
		);

		$data_length = count($data);
		$i = 0;

		$sql = "UPDATE ".$this->get_table_name($table)." SET".PHP_EOL;
		foreach ($data as $field=>$content) {
			$this->_quoteFieldName($field);

			if  ( $content === null ) {
				$sql .= ' '.$field.'= NULL';
			} else {
				$this->_secureFieldContent($content);
				$sql .= " ".$field."='".$content."'";
			}

			if ( ($data_length - 1 ) > $i++ )
				$sql .= ",";
		}
		if ( !$all ) {
			$sql .= " WHERE ".$id_field."='".$id."'";
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

		$sql = "DELETE FROM ".$this->get_table_name($table)."
		 WHERE id='".$id."' LIMIT 1";

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
	public function convert_result($table, array $result) {
		if ( ! $this->convert_results ) {
			return $result;
		}

		$table = $this->get_table_name($table);
		$converted = $result;

		$types = $this->get_table_information($table);

		foreach ( $result as $row_num => $rows ) {
			foreach ( $types as $type ) {
				$col = $type['column_name'];
				$new_type = substr($type['php_type'], 0, strpos($type['php_type'], '('));
				if ( !in_array($new_type, array( 'boolean', 'bool', 'integer', 'int', 'float', 'double', 'array', 'object', 'null')) ) continue;
				$converted[$row_num][$col] = settype($converted[$row_num][$col], $new_type);
			}
		}

		return $converted;
	}

	/**
	 * Spaltennamen holen
	 *
	 * @param string $table Tabellenname
	 * @return array
	 */
	public function get_table_information($table) {
		$table_information = array();
		foreach($this->query("SHOW COLUMNS FROM {$this->get_table_name($table)}", false) as $key => $value) {
			$table_information[$key] = array(
				'column_name' => $value['Field'],
				'php_type' => $value['Type'],
				'input_type' => $this->input_type_of($value['Type']),
			);
		}
		return $table_information;
	}

	/**
	 * Tabelle leeren
	 *
	 * @param string $table Tabellennname
	 * @return boolean
	 */
	public function clear_table($table) {
		$sql = 'TRUNCATE TABLE '.$this->get_table_name($table);

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
		$sql = 'OPTIMIZE TABLE ' . $this->get_table_name($table);

		$result = (boolean) $this->query($sql);
		return $result;
	}

	/**
	 * Zuletzt eingefügte ID zurückgeben
	 *
	 * @param string $table not used
	 * @return integer
	 */
	public function last_insert_id($table=null) {
		return mysql_insert_id($this->connection);
	}

	/**
	 * Eindeutigen Bezeichner für eine Tabelle zurückgeben
	 *
	 * @param string $table
	 * @return string
	 */
	public function get_table_name($table) {
		return $this->table_prefix . $table;
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

		mysql_query('SET NAMES "utf8"', $this->connection);

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
	protected function _query_db($sql) {
		$this->_select_db();

		$output = array();
		$aktionen = array('UPDATE','DELETE', 'ALTER ', 'CREATE', 'DROP T', 'TRUNCA', 'REPLAC', 'OPTIMI');

		$abfrage = is_string($sql) ? trim($sql) :  $this->error('Fehlerhafte Daten', $sql);

		$result = mysql_query($abfrage, $this->connection) OR $this->error(mysql_error($this->connection), $sql);

		$abfragetyp = strtoupper(substr($abfrage,0,6));

		if ( ( in_array($abfragetyp,$aktionen) ) ) {
			$output = ( $result )? TRUE: FALSE;
		} elseif ( ($abfragetyp == 'INSERT') ) {
			$output = ( $result )? $this->last_insert_id(): FALSE;
		} else {
			while($row = mysql_fetch_assoc($result)) {
				$output[] = $row;
			}
		}

		return $output;
	}

	/**
	 * Feldinhalte gegen Hackingversuche schützen
	 *
	 * Dies sind nur grundlegende Schutzmaßnahmen
	 *
	 * @param mixed &$var
	 * @return string
	 */
	private function _secureFieldContent(&$var){
		if ( is_array($var) ) {
			$varvalue = var_export($var, TRUE);
			$this->error('Array sollte gespeichert werden.', $varvalue );
		}
		return $var = mysql_real_escape_string($var, $this->connection);
	}

	/**
	 * problematische Feldnamen mit Datenbank-spezifischen Quotes versehen
	 *
	 * @param string &$var
	 * @return string
	 */
	private function _quoteFieldName(&$var) {
		return $var = in_array($var, array('key')) ? "`$var`" : $var;
	}

	/**
	 * Passenden Input-Type ausgeben
	 */
	private function input_type_of($db_type) {
		$input_type = '';

		switch( $db_type ) {
		case 'timestamp':
			$input_type = 'label';
			break;

		case 'tinyint(1)':
			$input_type = 'checkbox';
			break;

		case 'text':
			$input_type = "textarea";
			break;

		default:
			$input_type = "input";
		}

		return $input_type;
	}
}
?>
