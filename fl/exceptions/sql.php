<?php
class SqlException extends FederleichtException {
	protected $data;
	protected $table;

	public function getDetails() {
		return <<<DETAILS
{$this->getQuery()}

<pre>
{$this->getParams()}
</pre>
DETAILS;
	}

	public function getParams() {
		$params = '';
		$params .= 'Tabelle: ' .$this->table . PHP_EOL;
		$params .= 'Daten der Anfrage: ' . PHP_EOL;
		$params .= var_export($this->data, true);

		return $params;
	}

	public function getQuery() {
		$sql = parent::getDetails();

		$factory = new fl_factory();

		$err = $factory->get_helper('var_analyze', 'data-access', 'Fehler');
		$err->silent();
		$err->sql($sql, 'Datenbankabfrage, die zu Fehler gefuehrt hat');

		$query = stripslashes(
			implode(PHP_EOL, $err->return_output_cache())
		);

		return $query;
	}

	public function setTable($table) {
		$this->table = (string) $table;
	}

	public function setData($data) {
		$this->data = (array) $data;
	}
}
