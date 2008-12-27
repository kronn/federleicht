<?php
/**
 * Autoload-Funktion fuer Federleicht-Dateien
 *
 * @author Matthias Viehweger
 * @version 0.2
 * @package federleicht
 * @subpackage base
 */
function __autoload($class) {
	if ( file_exists( $file = ABSPATH . str_replace('_', '/', $class) . '.php' ) ) {
		require $file;
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
		if ( file_exists($file = ABSPATH.$path.str_replace('_', '/', $class).'.php') ) {
			require $file;
			return;
		} elseif ( file_exists($file = ABSPATH . $path . $class . '.php') ) {
			require $file;
			return;
		}
	}

	throw new Exception('Gesuchte Klassendatei "'.$class.'" konnte nicht eingebunden werden.');
}
