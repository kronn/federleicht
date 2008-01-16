<?php
/**
 * Konfigurationsdateien holen
 *
 * Es werden ini-Dateien verarbeitet
 *
 * @package federleicht
 * @subpackage base
 * @version 0.1
 */
$configfiles = glob( ABSPATH . 'config/*.ini');

if ( empty($configfiles) ) {
	die('Keine Konfigurationsdateien gefunden.');
}

$config = array();

foreach($configfiles as $file) {
	$config += parse_ini_file($file, true);
}

/**
 * Spezielle Behandlung bestimmter Einstellungen
 */
// Konstanten setzen
if ( isset( $config['constants'] ) ) {
	foreach ( $config['constants'] as $key => $value ) {
		define( strtoupper($key), $value );
	}
}

// Sprachenliste in Array umwandeln
if ( isset( $config['lang'] ) ) {
	$config['lang']['all'] = explode( ',', $config['lang']['all'] );
}

// Wenn keine Datenbankkonfiguration angegeben ist und auch nicht
// gesagt wurde, dass keine Datenbank verwendet wird, abbrechen.
if ( !in_array(ABSPATH.'config/database.ini', $configfiles) AND 
	( !defined('NO_DATABASE') OR NO_DATABASE === false ) ) {
	die('Keine Datenbankkonfiguration angegeben.');
}

/**
 * Routen einlesen
 */
require_once ABSPATH . 'config/routes.conf.php';

return $config;
?>
