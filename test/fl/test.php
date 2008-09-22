<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
	define('PHPUnit_MAIN_METHOD', 'fl_test::main');
}

require_once 'PHPUnit/Framework.php';

// @codeCoverageIgnoreStart
class fl_test extends PHPUnit_Framework_TestSuite { 
	public static function main() {
		require_once 'PHPUnit/TextUI/TestRunner.php';

		$result = PHPUnit_TextUI_TestRunner::run(self::suite());
	}

	public static function suite() { 
		$suite =  new self('Federleicht Framework Tests');
		self::loadTestEnvironment();

		$suite->addTestFile(ABSPATH . 'test/fl/dispatch/fl_dispatcherTest.php');
		$suite->addTestFile(ABSPATH . 'test/fl/dispatch/fl_langTest.php');
		$suite->addTestFile(ABSPATH . 'test/fl/dispatch/fl_routeTest.php');

		$suite->addTestFile(ABSPATH . 'test/fl/tools/fl_factoryTest.php');
		$suite->addTestFile(ABSPATH . 'test/fl/tools/fl_flashTest.php');
		$suite->addTestFile(ABSPATH . 'test/fl/tools/fl_functionsTest.php');
		$suite->addTestFile(ABSPATH . 'test/fl/tools/fl_inflectorTest.php');
		$suite->addTestFile(ABSPATH . 'test/fl/tools/fl_registryTest.php');
		$suite->addTestFile(ABSPATH . 'test/fl/tools/fl_responderTest.php');
		$suite->addTestFile(ABSPATH . 'test/fl/tools/fl_converterTest.php');

		// $suite->addTestFile(ABSPATH . 'test/fl/data/structures/activerecordTest.php');
		$suite->addTestFile(ABSPATH . 'test/fl/data/accessTest.php');

		return $suite;
	} 

	public static function loadTestEnvironment() { 
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
	
		fl_registry::getInstance()->set('path', array(
				'lib'=>ABSPATH . 'fl/',
				'app'=>ABSPATH . 'app/',
				'module'=>ABSPATH . 'app/modules/',
				'helper'=>ABSPATH . 'app/helper/',
				'elements'=>ABSPATH . 'app/elements/',
				'layouts'=>ABSPATH . 'app/layouts'
			)
		);

		$null_db = new fl_data_access(array('type'=>'null'));
		fl_registry::getInstance()->set('data_access', $null_db->get_data_source());

		fl_registry::getInstance()->set('helpers', array(
			'validation'
		));
		fl_registry::getInstance()->set('modules', array(
		));
	} 

	protected function setUp() { 
	} 
	protected function tearDown() { 
	}
}	
// @codeCoverageIgnoreEnd

if (PHPUnit_MAIN_METHOD == 'fl_test::main') {
	fl_test::main();
} else {
	fl_test::loadTestEnvironment();
}
?>
