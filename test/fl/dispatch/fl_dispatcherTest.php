<?php
// Call fl_dispatcherTest::main() if this source file is executed directly.
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'fl_dispatcherTest::main');
}

if ( !defined('ABSPATH') ) {
	$abspath = realpath(dirname(__FILE__) . '/../../../');
	define('ABSPATH', $abspath . '/');
}

require_once 'PHPUnit/Framework.php';

require_once ABSPATH . 'fl/dispatch/dispatcher.php';

/**
 * Test class for fl_dispatcher.
 * Generated by PHPUnit on 2008-09-01 at 18:59:50.
 */
class fl_dispatcherTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    fl_dispatcher
	 * @access protected
	 */
	protected $object;

	/**
	 * Runs the test methods of this class.
	 *
	 * @access public
	 * @static
	 */
	public static function main() {
		require_once 'PHPUnit/TextUI/TestRunner.php';

		$suite  = new PHPUnit_Framework_TestSuite('fl_dispatcherTest');
		$result = PHPUnit_TextUI_TestRunner::run($suite);
	}

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @access protected
	 */
	protected function setUp() {
		$this->object = new fl_dispatcher( 
			new fl_lang('de', array('de')),
			array('testing')
		);
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
	 * @covers fl_dispatcher::add_route
	 */
	public function testRoutesCanBeAdded() {
		$route = new fl_route('/');
		$this->assertTrue($this->object->add_route($route));
	}

	/**
	 * @covers fl_dispatcher::set_default_controller
	 * @covers fl_dispatcher::get_default_controller
	 */
	public function testSetAndGetDefaultController() {
		$testcontroller = 'testcontroller';

		$this->object->set_default_controller($testcontroller);
		$this->assertEquals(
			$this->object->get_default_controller(),
			$testcontroller
		);
	}

	/**
	 * @todo Implement testURLsCanBeAnalysed().
	 */
	public function testURLsCanBeAnalysed() {
		$route = fl_route::get_instance(
			'/test/:action', 'controller=testing,action=route,params=,lang=de', 1
		);
		$url = 'http://localhost/test';

		$this->object->add_route($route);
		$result = $this->object->analyse($url);

		$expected = array(
			'controller'=>'testing',
			'action'=>'route',
			'params'=>'',
			'lang'=>'de',
			'query'=>'',
			'modul'=>'testing',
		);

		$this->assertType('array', $result);
		$this->assertEquals($result, $expected);

		// Remove the following lines when you implement this test.
		$this->markTestIncomplete( 'This test has not been fully implemented yet.');
	}
}

// Call fl_dispatcherTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == 'fl_dispatcherTest::main') {
    fl_dispatcherTest::main();
}
?>
