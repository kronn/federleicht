<?php
/**
 * Fehlerbehandlung
 *
 * Eine generelle Klasse zur Fehlerhandlung, zum Analysieren und
 * zum Debuggen. Im Kern ist es nur ein besseres var_dump
 *
 * Es ist besser, über PECL XDebug zu installieren 
 * ( http://www.xdebug.com/ ).
 *
 * @package federleicht
 * @subpackage helper
 * @author Matthias Viehweger <kronn@kronn.de>
 * @version 0.1.6
 * @copyright MIT-Style
 */
/**
 * Klasse zur Fehlerbehandlung
 */
class var_analyze {
	var $object = '';
	var $method = '';

	var $is_silent = FALSE;
	var $messages;

	function var_analyze($object='Objekt', $method='Methode') {
		$this->object = $object;
		$this->method = $method;
		$this->messages = array();
	}

	/**
	 * Wrapperfunktion um Ausgabe zu steuern
	 *
	 * Die Ausgabe der Funktionsaufrufe wird abhängig von der Instanzvariablen
	 * is_silent in eine Variable umgeleitet oder direkt ausgegeben.
	 *
	 * @param string $output
	 */
	function output($output) {
		if ( $this->is_silent ) {
			$this->messages[] = $output;
		} else {
			echo $output;
		}
	}

	/**
	 * Zwischenspeicher ausgeben
	 */
	function flush_output_cache() {
		$msgs = $this->messages;
		foreach( $msgs as $key=>$msg ) {
			echo $msg;
			unset($this->messages[$key]);
		}
	} 

	function return_output_cache() {
		return $this->messages;	
	}

	/**
	 * HTML-Ausgabe unterbinden
	 */
	function silent() {
		$this->is_silent = TRUE;
	}

	/**
	 * Ausgabe von Meldungen ermöglichen
	 */
	function verbose() {
		$this->is_silent = FALSE;
	}

	/**
	 * Eine Variable ausgeben
	 *
	 * Der Inhalt der &uuml;bergebenen Variable wird ausgegeben und
	 * ein ggf. zus&auml;tzlich &uuml;bergebener Infotext wird davorgeschrieben.
	 *
	 * Wenn die Variable ein Objekt ist, werden Informationen &uuml;ber die
	 * zugrundeliegende Klasse zur&uuml;ckgegeben.
	 *
	 * @param mixed $var
	 * @param string $text optional, Standardwert ist "Variableninhalte"
	 */
	function sv($var, $text='Variableninhalte') {
		if ( !isset($var) ) return;

		$html = '<pre style="overflow:auto">';

		if ( is_object($var) ) {
			$object = ( $text == 'Variableninhalte' )? 'Objekt': 'Objekt '.$text;

			$html .= '<b>'.$this->object . ' - ' . $this->method . ' - '.$object.'</b>'."\n";
			$html .= "\tDas ".$object." ist Instanz der Klasse <i>".get_class($var)."</i>\n";

			$html .= '<blockquote><b>' . $object . ' - Variablen</b>'."\n";
			#$html .= var_export( get_class_vars(get_class($var)), TRUE );
			$html .= var_export( get_object_vars($var), TRUE );
			$html .= "\n";

			$html .= '<b>' .$object. ' - Methoden</b>'."\n";
			$methods = get_class_methods($var);
			sort($methods);
			foreach( $methods as $method ) {
				$html .= "  - ".$method."\n";
			}
			$html .= '</blockquote>'."\n";

		} else {
			$var = ( is_string($var) )? htmlspecialchars($var): $var;

			$html .= '<b>'.$this->object . ' - ' . $this->method . ' - '.$text.'</b>'."\n";
			$html .= var_export( $var, TRUE );

		}

		$html .='</pre>';
		$this->output($html);
	}

	/**
	 * einfache Textausgabe
	 */
	function say($text) {
		$html = '<pre><i>'.$text.'</i></pre>';
		$this->output($html);
	}

	/**
	 * Variable analysieren und inklusive einiger Eigenschaften ausgeben
	 */
	function analyze($var, $text = 'Variableninhalte') {
		$this->sv($var, $text);
		if (is_bool($var)) $props[] = 'boolean';
		if (is_null($var)) $props[] = 'null';
		if (is_string($var)) $props[] = 'string';
		if (is_numeric($var)) $props[] = 'numeric';
		if (is_float($var)) $props[] = 'float';
		if (is_object($var)) $props[] = 'object';
		if (is_array($var)) $props[] = 'array';
		$html = '<blockquote><pre><b>Eigenschaften:</b> ';
		$html .= implode(', ', $props);
		$html .= '</pre></blockquote>';
		$this->output($html);
	}

	/**
	 * über ein Array iterieren
	 */
	function iterate($var, $text='Variableninhalte') {
		if ( is_array($var) ) {
			foreach( $var as $key=>$value ) {
				$this->analyze($value, $text . ' - ' . $key);
			}
		}
	}

	/**
	 * Eine Variable ausgeben und Skript beenden
	 *
	 * @param mixed $var
	 * @param string $text optionaler Parameter.
	 */
	function svaq($var, $text = 'Variableninhalte') {
		$this->sv($var, $text);
		$this->stop();
	}

	/**
	 * SQL ausgeben
	 */
	function sql($var, $text = 'SQL') {
		$eol_after = array(
			'BETWEEN',
			'AND',
			'OR'
		);
		$eol_before = array(
			'SELECT',
			'INSERT',
			'UPDATE',
			'VALUES',
			'INNER',
			'LEFT',
			'RIGHT',
			'IFNULL',
			'WHERE',
			'FROM',
			'LIMIT',
			'GROUP',
			'HAVING'
		);

		$var = stripslashes($var);

		$var = preg_replace(
			'@ ('.implode('|', $eol_after).') @', 
			' $1 '."\n", 
			preg_replace(
				'@('.implode('|', $eol_before).') @',
				"\n".'$1 ', 
				$var
			)
		);

		$this->sv($var, $text);
	}

	/**
	 * Superglobale $_FILES ausgeben
	 */
	function files() {
		$this->sv($_FILES, 'Superglobale $_FILES - enth&auml;lt Uploaddaten');
	}

	/**
	 * Superglobale $_POST ausgeben
	 */
	function post() {
		$this->sv($_POST, 'Superglobale $_POST - enth&auml;lt Formulardaten');
	}

	/**
	 * Superglobale $GLOBALS ausgeben
	 */
	function globals() {
		$this->sv($GLOBALS, 'Superglobale $GLOBALS - enth&auml;lt alle Variablen');
	}

	/**
	 * Superglobale $_SESSION ausgeben
	 */
	function session() {
		$this->sv($_SESSION, 'Superglobale $_SESSION - enth&auml;lt Sessiondaten');
	}

	/**
	 * Superglobale $_COOKIE ausgeben
	 */
	function cookies() {
		$this->sv($_COOKIE, 'Superglobale $_COOKIE - enth&auml;lt Cookiedaten');
	}

	/**
	 * Zeitmessungen
	 *
	 * Eine Beispielfunktion für die Zeiterfassung ist beigefügt, sie muss im Bedarfsfall an den Anfang der
	 * index.php geschrieben werden.
	 *
	 * Die Einbindung des ErrorHandler und der Aufruf der Funktion timer sollte direkt nach der Ermittlung der
	 * Endzeit (höhö) geschehen. Je nach Anwendungsfall kann dies in vollständig in der index.php geschehen.
	 *
	 * Man sollte sicherstellen, dass die Genauigkeit des float-Datentyps richtig eingestellt ist. Mit der Standard-
	 * Einstellung "precision = 12" ist man nur bis 0.01 Sekunden genau.
	 * In der php.ini sollte man also
	 *     precision = 16
	 * einstellen
	 *
	 * @param float $start    Startzeit als Microtime
	 * @param float $end      Endzeit (höhö) als Microtime
	 * @param int   $interval Aktualisierungsintervall in Sekunden
	 */
	function timer($start, $end, $interval) {
		/*
		ini_set('precision', '16');
		function getmicrotime() {
			list($usec, $sec) = explode(" ", microtime());
			return ((float)$usec + (float)$sec);
		}
		$start = getmicrotime();
			# Code
		$end = getmicrotime();
		*/
		if ( $interval > 0 ) {
			$interval = $interval * 1000;
		} elseif ($interval == 0) {
			unset($_SESSION['test']);
			return;
		}

		if ( !isset($_SESSION['test']['time']) ) $_SESSION['test']['time'] = 0;
		if ( !isset($_SESSION['test']['count']) ) $_SESSION['test']['count'] = 0;

		$time = $this->_format_number($end - $start);
		$_SESSION['test']['time'] += $time;
		$avg = $this->_format_number($_SESSION['test']['time'] / ++$_SESSION['test']['count']);
		$html = '<pre>';
		$html .= 'Zeit f&uuml;r aktuellen Durchlauf: '.$time.' Sekunden (Nr. '.$_SESSION['test']['count'].")\n";
		$html .= 'Durchschnittliche Zeit: '.$avg." Sekunden";
		$html .= '</pre>';
		$html .= '
		<script type="text/javascript">
		// <[CDATA[
		function addEvent(elm, evType, fn, useCapture) {
			if (elm.addEventListener) {
				elm.addEventListener(evType, fn, useCapture);
				return true;
			} else if (elm.attachEvent) {
				var r = elm.attachEvent("on" + evType, fn);
				return r;
			} else {
				elm["on" + evType] = fn;
			}
		}

		function reloadtimer() {
			window.setTimeout("window.location.reload()", '.$interval.');
		}';

		if ( $interval > 0 ) {
			$html .= '		addEvent(window, "load", reloadtimer, false);';
		}

		$html .= '
		// ]]>
		</script>';

		$this->output($html);
	}


	/**
	 * Skript anhalten
	 */
	function stop() {
		exit();
	}

	function _format_number($number) {
		return str_pad(substr(round($number, 5), 0, 7), 7, '0');
	}
}
