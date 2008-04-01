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
	if ( file_exists( $file = ABSPATH . str_replace('_', '/', $class) . '.php' ) ) {
		require_once $file;
		return;
	}

	// Andernfalls der lange Weg...
	$paths = array();
	$paths[] = 'fl/';
	$paths[] = 'fl/interfaces/';
	$paths[] = 'fl/dispatch/';
	$paths[] = 'fl/tools/';
	$paths[] = 'fl/mvc/';

	if ( strpos($class, 'fl_') === 0 ) {
		$class = substr($class, 3);
	}

	foreach ( $paths as $path ) {
		$file = ABSPATH . $path . str_replace('_', '/', $class) . '.php';

		if ( file_exists($file) ) {
			require_once $file;
			return;
		} else {

		}
	}

	throw new Exception('Gesuchte Klassendatei konnte nicht eingebunden werden.');
}
