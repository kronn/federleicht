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

	// andernfalls der lange Weg...
	$class_list = array(
		'model'=>'fl/mvc',
		'view'=>'fl/mvc',
		'controller'=>'fl/mvc',
		'modul'=>'fl/mvc',

		'dispatcher'=>'fl/dispatch',
		'lang'=>'fl/dispatch',
		'route'=>'fl/dispatch',

		'structures'=>'fl/data',

		'flash'=>'fl/tools',
		'functions'=>'fl/tools',
		'factory'=>'fl/tools',
		'inflector'=>'fl/tools',
	);

	$interfaces = array(
		'data_access'
	);

	$patterns = array(
		'[-_a-z]+_structure'=>'fl/data/structures',
	);

	$path = '';

	if ( in_array($class, $interfaces) ) {
		$path = 'fl/interfaces/';
	} elseif ( isset($class_list[$class]) ) {
		$path = $class_list[$class] . '/';
	} elseif ( isset($patterns) ) {
		foreach ( $patterns as $pattern => $path ) {
			if ( preg_match('/^'.$pattern.'$/', $class) ) {
				break; // $path wird dadurch uebernommen...
			} else {
				$path = '';
			}
		}
	}

	$file = ABSPATH . $path . $class . '.php';

	if ( file_exists($file) ) {
		require_once $file;
		return;
	}

	throw new Exception('Gesuchte Klassendatei konnte nicht eingebunden werden.');
}
