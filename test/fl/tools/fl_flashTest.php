<?php
// Call fl_flashTest::main() if this source file is executed directly.
if (!defined('PHPUnit_MAIN_METHOD')) {
	define('PHPUnit_MAIN_METHOD', 'fl_flashTest::main');
}

if ( !defined('ABSPATH') ) {
	$abspath = realpath(dirname(__FILE__) . '/../../../');
	define('ABSPATH', $abspath . '/');
}

require_once 'PHPUnit/Framework.php';

require_once ABSPATH . 'fl/tools/flash.php';

/**
 * Test class for fl_flash.
 * Generated by PHPUnit on 2008-04-29 at 21:47:22.
 */
class fl_flashTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var	fl_flash
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

		$suite  = new PHPUnit_Framework_TestSuite('fl_flashTest');
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
		$this->object = new fl_flash;
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
	 * @todo Implement test__destruct().
	 */
	public function test__destruct() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testSet_default_type().
	 */
	public function testSet_default_type() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testSet_default_namespace().
	 */
	public function testSet_default_namespace() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testAdd_message().
	 */
	public function testAdd_message() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testGet_messages().
	 */
	public function testGet_messages() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testClear_messages().
	 */
	public function testClear_messages() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testSave_messages().
	 */
	public function testSave_messages() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}
}

// Call fl_flashTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == 'fl_flashTest::main') {
	fl_flashTest::main();
}
?>
