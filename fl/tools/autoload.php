<?php
/**
 * Autoload-Funktion fuer Federleicht-Dateien
 *
 * @author Matthias Viehweger
 * @version 0.1
 * @package federleicht
 * @subpackage base
 */
function __autoload($class) {
	// so sollte die Datei benannt sein:
	$file = ABSPATH . str_replace('_', '/', $class) . '.php';

	if ( file_exists($file) ) {
		require_once $file;
		return;
	}

	// Interfaces einbinden 
	$interfaces = array(
		'data_access'
	);

	if ( in_array($class, $interfaces) ) {
		$path = 'fl/interfaces/';
	}

	$file = ABSPATH . $path . $class . '.php';

	if ( file_exists($file) ) {
		require_once $file;
		return;
	}

	throw new Exception('Gesuchte Klassendatei konnte nicht eingebunden werden.');
}
