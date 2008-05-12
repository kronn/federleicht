<?php
// Call fl_functionsTest::main() if this source file is executed directly.
if (!defined('PHPUnit_MAIN_METHOD')) {
	define('PHPUnit_MAIN_METHOD', 'fl_functionsTest::main');
}

require_once 'PHPUnit/Framework.php';

require_once '../../../fl/tools/functions.php';

/**
 * Test class for fl_functions.
 * Generated by PHPUnit on 2008-04-29 at 21:48:02.
 */
class fl_functionsTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var	fl_functions
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

		$suite  = new PHPUnit_Framework_TestSuite('fl_functionsTest');
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
		$this->object = new fl_functions;
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
	 * @todo Implement testStart_flash().
	 */
	public function testStart_flash() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testStop().
	 */
	public function testStop() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}
}

// Call fl_functionsTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == 'fl_functionsTest::main') {
	fl_functionsTest::main();
}
?>
