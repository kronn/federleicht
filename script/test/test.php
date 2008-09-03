<?php
/**
 * System testen
 */

/**
 * Absoluten Pfad setzen, wenn noch nicht vorhanden
 */
if ( !defined('ABSPATH') ) {
	$abspath = realpath( dirname(__FILE__) . '/../../' ) . '/';
	define('ABSPATH', $abspath);
}

/**
 * Tests aufzählen
 */
$tests = array(
	'syntax/test',
	'fl/flTest',
);

/**
 * Vorhandene Tests ausführen
 */
foreach ( $tests as $test ) {
	$filename = ABSPATH . 'test/'.$test.'.php';
	if ( file_exists($filename) ) {
		require_once $filename;
	}
}
