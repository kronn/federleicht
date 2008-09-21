<?php
/**
 * Absoluten Pfad setzen, wenn noch nicht vorhanden
 */
if ( !defined('ABSPATH') ) {
	$abspath = realpath( dirname(__FILE__) . '/../../' ) . '/';
	define('ABSPATH', $abspath);
}

if (count($unittests) > 0 and !defined('PHPUnit_MAIN_METHOD')) {
	define('PHPUnit_MAIN_METHOD', 'complete_test::main');
}

/**
 * Vorhandene Tests ausführen
 */
foreach ( array_merge($testscripts, $unittests) as $test ) {
	$filename = ABSPATH . 'test/'.$test.'/test.php';
	if ( file_exists($filename) ) {
		require_once $filename;
	}
}

if ( isset($syntax_error_state) and $syntax_error_state == true ) exit();
if ( count($unittests) == 0 ) exit();

require_once 'PHPUnit/Framework.php';

// @codeCoverageIgnoreStart
class complete_test extends PHPUnit_Framework_TestSuite { 
	public static function main($tests = array()) {
		require_once 'PHPUnit/TextUI/TestRunner.php';
		$result = PHPUnit_TextUI_TestRunner::run(self::suite($tests));
	}
	public static function suite(array $tests = array()) { 
		$suite =  new self('Complete Test');

		foreach ( $tests as $test ) {
			$suite->addTestSuite($test.'_test');
		}
		// $suite->addTestSuite('fl_factoryTest');

		return $suite;
	} 
	protected function setUp() { 
	} 
	protected function tearDown() { 
	}
}	
// @codeCoverageIgnoreEnd

if (PHPUnit_MAIN_METHOD == 'complete_test::main') {
    complete_test::main($unittests);
}
?>
