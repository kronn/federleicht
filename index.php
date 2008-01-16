<?php
/**
 * Federleicht
 *
 * Federleicht ist ein PHP-Framework, dass Entwicklern eine Grundlage zur 
 * schnelleren Anwendungsentwicklung bietet.
 *
 * @package federleicht
 * @subpackage base
 * @author Matthias Viehweger <kronn@kronn.de>
 * @version 0.2
 * @license BSD-License
 */
/**
 * Gesamtzeitmessung
 */
ini_set('precision', '16');
function getmicrotime() {
  list($usec, $sec) = explode(" ", microtime());
  return ((float)$usec + (float)$sec);
}
$start = getmicrotime();

/**
 * Initialisierung für "Web"
 */
require_once('fl/init.php');

/**
 * Federleicht-Objekt holen
 */
require_once 'fl/federleicht.php';

/**
 * Der Name des Objekts wird nur in dieser Datei genannt
 */
$federleicht = new federleicht($_SERVER['REQUEST_URI']);

/**
 * Get fl framework object
 *
 * @return federleicht
 * @deprecated
 */
function get_fl() {
	global $federleicht;

	return $federleicht;
}

function needs($wanted) {
	$fl_framework = get_fl();
	$fl_framework->functions->needs($wanted);

	return TRUE;
}

$federleicht->start();

$federleicht->functions->stop();



/** 
 * Auswertung der Gesamtzeitmessung
 */
$end = getmicrotime();
$federleicht->functions->needs('var_dump');
$shit = new varDumper('Allgemein', 'index.php');
$shit->timer($start, $end, -1);
$shit->say('Anzahl der Datenbankabfragen: ' . ( $federleicht->datamodel->query_count ) . '');
$shit->sv($federleicht->datamodel->allSQL, 'Datenbankabfragen');

$federleicht->functions->stop();
