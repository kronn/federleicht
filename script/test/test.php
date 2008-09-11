<?php
/**
 * Tests aufzählen
 *
 * Testskripte sind einfache PHP-Dateien im Ordner test 
 * (z.B. /test/syntax/test.php)
 *
 * Unittests sind Testsuiten, die auf PHPUnit aufbauen.
 */
$testscripts = array(
	'syntax',
);

$unittests = array(
	'fl',
	'app',
);


require_once 'runner.php';
