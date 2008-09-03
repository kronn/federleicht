<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'flTest::main');
}

if ( !defined('ABSPATH') ) {
	$abspath = realpath(dirname(__FILE__) . '/../../');
	define('ABSPATH', $abspath . '/');
}

require_once ABSPATH . 'fl/tools/autoload.php';
$interfaces = array(
	'data_access',
	'data_wrapper'
);
foreach ($interfaces as $interface) {
	require_once ABSPATH . 'fl/interfaces/'. $interface . '.php';
}

require_once ABSPATH . 'test/fl/dispatch/fl_dispatcherTest.php';
require_once ABSPATH . 'test/fl/dispatch/fl_langTest.php';
require_once ABSPATH . 'test/fl/dispatch/fl_routeTest.php';

// require_once ABSPATH . 'test/fl/tools/fl_factoryTest.php';
require_once ABSPATH . 'test/fl/tools/fl_flashTest.php';
// require_once ABSPATH . 'test/fl/tools/fl_functionsTest.php';
require_once ABSPATH . 'test/fl/tools/fl_inflectorTest.php';
require_once ABSPATH . 'test/fl/tools/fl_registryTest.php';
require_once ABSPATH . 'test/fl/tools/fl_responderTest.php';

// @codeCoverageIgnoreStart
class flTest extends PHPUnit_Framework_TestSuite { 
	public static function main() {
		require_once 'PHPUnit/TextUI/TestRunner.php';

		$result = PHPUnit_TextUI_TestRunner::run(self::suite());
	}

	public static function suite() { 
		$suite =  new flTest('Federleicht Framework Tests');

		$suite->addTestSuite('fl_dispatcherTest');
		$suite->addTestSuite('fl_langTest');
		$suite->addTestSuite('fl_routeTest');

		// $suite->addTestSuite('fl_factoryTest');
		$suite->addTestSuite('fl_flashTest');
		// $suite->addTestSuite('fl_functionsTest');
		$suite->addTestSuite('fl_inflectorTest');
		$suite->addTestSuite('fl_registryTest');
		$suite->addTestSuite('fl_responderTest');

		return $suite;
	} 
	protected function setUp() { 
	} 
	protected function tearDown() { 
	}
}	
// @codeCoverageIgnoreEnd

if (PHPUnit_MAIN_METHOD == 'flTest::main') {
    flTest::main();
}
?>
