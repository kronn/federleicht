<?php
/**
 * Validierungs-Plugin
 *
 * Die Pruefausdruecke koennen als regulaerer Ausdruck oder ueber ein
 * Schluesselwort bestimmt werden. Die Dokumentation der Funktion set_rule()
 * (ca. ab Zeile 50) listet die unsterstuetzten Schluesselworte und 
 * verfuegbare Optionen auf.
 *
 * @package federleicht
 * @subpackage plugin
 */

/**
 * Validierungsklasse
 */
class validation {
	/**
	 * Klassenvariablen
	 */
	var $string_regex = '- _a-zA-Z\x7f-\xff';

	/**
	 * Instanzvariablen
	 */
	var $error_msg;
	var $rules = array();

	/**
	 * Konstruktor
	 */
	function validation() {
	}

	/**
	 * Fehlermeldungen aus Datenbank holen
	 */
	function get_errormsg($datamodel) {
		$result = $datamodel->retrieve('language', '*', "tld = '".LANG."'");
		foreach( $result as $key => $value) {
			if ( substr($key, 0, 6) != 'error_' ) continue;
			$id = substr($key, 6);

			if ( !$this->rule_exists($id) )	$this->create_rule($id);
			$this->update_rule($id, false, $value);
		}
	}

	/**
	 * Prüfausdruck hinzufügen
	 *
	 * Prüfausdruck wird zur Liste hinzugefügt. Der Rückgabewert zeigt an,
	 * ob das erfolgreich war.
	 *
	 * @param string $id  	ID des zu überprüfenden Datenfeldes
	 * @param string $type	Art der Überprüfung. Gültige Werte sind
	 *                    	- string
	 *                    	- alphanum
	 *                    	- mostchars
	 *                    	- number
	 *                    	- telephone
	 *                    	- not_zero
	 *                    	- email
	 *                    	- text
	 *                    	- regexp
	 * @param string $rule	zusätzliche Überprüfungsregel
	 *                      mögliche Werte sind
	 *                      - optional
	 *                      - multiline
	 *                      - nocase
	 *                      - [eine Mengenangabe nach RegEx-Syntax]
	 *                      - [ein regulärer Ausdruck]
	 * @return boolean
	 */
	function set_rule($id, $type, $rule='') {
		if ( !is_string($id) ) $id = (string) $id;
		if ( !is_string($type) ) $type = (string) $type;
		if ( !is_string($rule) ) $rule = (string) $rule;

		$start = '/^';
		$end = '$/';

		switch ($type) {
		case 'string':
			$regexp = '['.$this->string_regex.']';
			break;

		case 'alphanum':
			$regexp = '['.$this->string_regex.'0-9]';
			break;

		case 'mostchars':
			$regexp = '['.$this->string_regex.'0-9i.,!?#\/\+*]';
			break;

		case 'number':
			$regexp = '[- 0-9]';
			break;

		case 'telephone':
			$regexp = '[- .\/\0-9(),+]';
			break;

		/**
		 * Diese Regel prueft nicht, ob ein Wert nicht Null ist, 
		 * sondern ob er nicht mit Null beginnt.
		 */
		case 'not_zero':
			$regexp = '-?[^0][0-9\.]';
			$rule .= ' optional';
			break;

		case 'email':
			#$regexp = '([A-Za-z0-9](([\w.-][^._-]*){0,61})[A-Za-z0-9])@([A-Za-z0-9]([-A-Za-z0-9]{0,61})?[A-Za-z0-9]\.)+([A-Za-z]{2,6})';
			$regexp = '[a-z0-9]([-_.a-z0-9]{0,64})@([-_.a-z0-9]*).([a-z]{2,6})';
			$rule .= ' nocase';
			break;

		case 'textfield':
		case 'text':
		case 'textarea':
			$regexp = '[^\t]';
			$rule .= ' multiline';
			break;

		case 'regex':
		case 'regexp':
			$regexp = $rule;
			$start = '';
			$end = '';
			$rule = '';
			break;

		default:
			$regexp = '.';
			break;
		}

		/**
		 * Hier wird die Variable $count initialisiert
		 * und auf weitere Regeln eingegangen
		 */
		if ( preg_match('/(\{[,0-9]+\}|[*+?]{1,2})/', $rule) ) {
			$count = $rule;
		} elseif ( in_array($type, array('regexp', 'regex')) ) {
			$count = '';
		} else {
			$count = '+'; 

			$rule .= ' ';
			$rules = explode(' ', $rule);

			if ( in_array('optional',  $rules) ) { 
				$start .= '(?:';
				$end = ')?'. $end;
			}

			if ( in_array('multiline', $rules) ) {
				$end = $end . 'm';
			}

			if ( in_array('nocase', $rules) ) {
				$end = $end . 'i';
			}
		}

		if ( !$this->rule_exists($id) )	$this->create_rule($id);
		$this->update_rule($id, $start . $regexp . $count . $end, false);
		return true;
	}

	/**
	 * Fehlermeldung zu einer Prüfregel hinzufügen
	 *
	 * @param string $id  	ID des zu überprüfenden Datenfeldes
	 * @param string $msg 	Fehlermeldung, falls die Prüfung Eingabefehler 
	 *                    	feststellt
	 * @return boolean
	 */
	function set_msg($id, $msg) {
		if ( !is_string($id) ) $id = (string) $id;
		if ( !is_string($msg) ) $msg = (string) $msg;

		if ( !$this->rule_exists($id) )	$this->create_rule($id);
		$this->update_rule($id, false, $msg);
		return true;
	}

	/**
	 * Formulardaten validieren
	 *
	 * Die Formulardaten werden anhand regulärer Ausdrücke auf Plausibilität 
	 * geprüft.
	 *
	 * @param array &$data Referenz auf Formulardaten
	 * @return array
	 */
	function validate_form(&$data) {
		if ( !is_array($data) AND !($data instanceof ArrayAccess) ) {
			throw new Exception('Daten nicht als Array (oder vergleichbare Klasse) übergeben');
		}

		$rules = $this->get_rules();
		if ( empty($rules) ) {
			throw new Exception('Keine Regeln zur Überprüfung definiert');
		}
		$errors = array();

		foreach( $rules as $field=>$rule ) {
			if (!isset($data[$field])) {
					$value = '';
			} else {
					$value = $data[$field];
			}

			$is_valid = $this->validate_field($field, $value);
			if ( !$is_valid ) {
				$errors[] = $rule['error'];
				# $data['field'] = '';
			}
		}

		return $errors;
	}

	/**
	 * Einzelnen Wert prüfen
	 *
	 * @param string $key
	 * @param string $value
	 * @return boolean
	 */
	function validate_field($key, $value='') {
		$result = true;

		if ( $this->rule_exists($key) ) {
			$rule = $this->get_rule($key);

			$result = true;

			if ( !preg_match($rule['regexp'], $value))  {
				$result = false; 
			}
		}

		return $result;
	}

	/**
	 * Javascript mit Regeln für die Formulardatenvalidierung zurückgeben
	 *
	 * Es wird eine JS mit den Regeln für die Validierung der Formulardaten 
	 * zurückgegeben.
	 * Außerdem wird der HTML-Code für das Einbinden des allgemeinen
	 * Validierungsskripts angefügt.
	 *
	 * @return string
	 */
	function get_js() {
		$form_variables = $this->get_rules();
		if ( empty($form_variables) ) return '';

		$js = "var validationSet = {\n";
		$entries = array();
		foreach ($form_variables as $name => $properties) {
			$entries[] = "\t'".$name."': { 'regexp': ".$properties['regexp'].", 'error': ' ".addslashes($properties['error'])."'}";
		}
		$js .= implode(",\n", $entries) . "\n";
		$js .= "}\n";

		$html = '<script type="text/javascript">'."\n".'// <[CDATA['."\n";
		$html .= $js;
		$html .= '// ]]>'."\n".'</script>'."\n";
		$html .= '<script type="text/javascript" src="/public/js/validation.js"></script>'."\n";
		return $html;
	}

	################### Verwaltungsfunktionen ###########################
	/**
	 * Prüfen, ob Regel existiert
	 *
	 * @param string $id
	 * @return boolean
	 */
	function rule_exists($id) {
		return ( isset($this->rules[$id]) );
	}

	/**
	 * Regel holen
	 *
	 * @param string $id
	 * @return array
	 */
	function get_rule($id) {
		return $this->rules[$id];
	}

	/**
	 * Regelarray erzeugen
	 *
	 * @param string $id
	 */
	function create_rule($id) {
		if ( $this->rule_exists($id) ) return;

		$this->rules[$id] = array(
			'regexp'=>'/.*/',
			'error'=>'Error in '.$id
		);
	}

	/**
	 * Regel aktualisieren
	 */
	function update_rule($id, $regex=false, $msg=false) {
		if ( !$this->rule_exists($id) ) return;

		if ( $regex !== false ) $this->rules[$id]['regexp'] = $regex;
		if ( $msg !== false ) $this->rules[$id]['error'] = $msg;
	}

	/**
	 * Regel entfernen
	 */
	function remove_rule($id) {
		if ( !$this->rule_exists($id) ) return;

		unset ($this->rules[$id]);
	}

	/**
	 * Regeln als Array zurückgeben
	 */
	function get_rules() {
		return $this->rules;
	}
	############### Ende der Verwaltungsfunktionen ######################
}
