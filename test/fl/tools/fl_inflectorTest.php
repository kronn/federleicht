<?php
// Call fl_inflectorTest::main() if this source file is executed directly.
if (!defined('PHPUnit_MAIN_METHOD')) {
	define('PHPUnit_MAIN_METHOD', 'fl_inflectorTest::main');
}

require_once 'PHPUnit/Framework.php';

require_once '../../../fl/tools/inflector.php';

/**
 * Test class for fl_inflector.
 * Generated by PHPUnit on 2008-04-29 at 21:49:01.
 */
class fl_inflectorTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var	fl_inflector
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

		$suite  = new PHPUnit_Framework_TestSuite('fl_inflectorTest');
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
		$this->object = new fl_inflector;
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
	 * @todo Implement testPlural().
	 */
	public function testPlural() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testSingular().
	 */
	public function testSingular() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}
}

// Call fl_inflectorTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == 'fl_inflectorTest::main') {
	fl_inflectorTest::main();
}
?>
