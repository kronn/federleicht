<?php
// Call fl_factoryTest::main() if this source file is executed directly.
if (!defined('PHPUnit_MAIN_METHOD')) {
	define('PHPUnit_MAIN_METHOD', 'fl_factoryTest::main');
}

if ( !defined('ABSPATH') ) {
	$abspath = realpath(dirname(__FILE__) . '/../../../');
	define('ABSPATH', $abspath . '/');
}

require_once 'PHPUnit/Framework.php';

require_once ABSPATH . 'fl/data/structures.php';
require_once ABSPATH . 'fl/data/structures/data.php';
require_once ABSPATH . 'fl/tools/factory.php';

/**
 * Test class for fl_factory.
 * Generated by PHPUnit on 2008-04-29 at 21:46:54.
 */
class fl_factoryTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var	fl_factory
	 * @access protected
	 */
	protected $object;

	/**
	 * Runs the test methods of this class.
	 *
	 * @access public
	 * @static
	 */
	public static function main()
	{
		require_once 'PHPUnit/TextUI/TestRunner.php';

		$suite  = new PHPUnit_Framework_TestSuite('fl_factoryTest');
		$result = PHPUnit_TextUI_TestRunner::run($suite);
	}

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @access protected
	 */
	protected function setUp()
	{
		$this->object = new fl_factory;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @access protected
	 */
	protected function tearDown()
	{
	}

	/**
	 * @todo Implement testSet_data_access().
	 */
	public function testSet_data_access() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testCreate().
	 */
	public function testCreate() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testGet_model().
	 */
	public function testGet_model() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testGet_class().
	 */
	public function testGet_class() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testGet_ar_class().
	 */
	public function testGet_ar_class() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testGet_structure().
	 */
	public function testGet_structure() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testGet_helper().
	 */
	public function testGet_helper() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testLoad_structure().
	 */
	public function testLoad_structure() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testLoad_class().
	 */
	public function testLoad_class() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testLoad_helper().
	 */
	public function testLoad_helper() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testLoad_module().
	 */
	public function testLoad_module() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testIs_structure().
	 */
	public function testIs_structure() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}
}

// Call fl_factoryTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == 'fl_factoryTest::main') {
	fl_factoryTest::main();
}
?>
