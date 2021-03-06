<?php
// Call fl_dispatcherTest::main() if this source file is executed directly.
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'fl_dispatcherTest::main');
}

if ( !defined('FL_ABSPATH') ) {
	$abspath = realpath(dirname(__FILE__) . '/../../../');
	define('FL_ABSPATH', $abspath . '/');
}

require_once 'PHPUnit/Framework.php';
require_once FL_ABSPATH . 'test/fl/test.php';
require_once FL_ABSPATH . 'fl/dispatch/dispatcher.php';

/**
 * Test class for fl_dispatcher.
 * Generated by PHPUnit on 2008-09-01 at 18:59:50.
 */
class fl_dispatcherTest extends PHPUnit_Framework_TestCase {
	protected $object;
	public static function main() {
		require_once 'PHPUnit/TextUI/TestRunner.php';

		$suite  = new PHPUnit_Framework_TestSuite('fl_dispatcherTest');
		$result = PHPUnit_TextUI_TestRunner::run($suite);
	}
	protected function setUp() {
		$this->object = $this->create_dispatcher();
	}
	protected function tearDown() {
	}
	protected function create_dispatcher() {
		return new fl_dispatcher( 
			new fl_lang('de', array('de')),
			array('testing')
		);
	}

	/**
	 * @covers fl_dispatcher::__construct
	 * @covers fl_dispatcher::clean_superglobals
	 */
	public function testInstantiation() {
		$dispatcher = $this->create_dispatcher();

		$this->assertType('fl_dispatcher', $dispatcher);
	}

	/**
	 * @cover fl_dispatcher::arrayStripSlashes
	 */
	public function testStripSlashes() {
		$original_string = $string = 'O\'Reilly verkauft Bücher';

		$this->object->arrayStripSlashes($string);

		$this->assertEquals(
			$string,
			stripslashes($original_string)
		);

		$original_array = $array = array(
			'O\'Reilly verkauft Bücher',
			'\’Go\' he said'
		);

		$stripped_array = array();
		foreach( $original_array as $key => $value ) {
			$stripped_array[$key] = stripslashes($value);
		}

		$this->object->arrayStripSlashes($array);

		$this->assertEquals(
			$array,
			$stripped_array
		);

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
		try {
			$this->object->set_default_controller('testcontroller');
		} catch ( Exception $e ) {
			$this->assertContains('veraltet', $e->getMessage());
		}

		try {
			$this->object->get_default_controller();
		} catch ( Exception $e ) {
		}
	}

	/**
	 * @covers fl_dispatcher::analyse
	 */
	public function testLastRouteWinsIfNoneMatched() {
		$route1 = fl_route::get_instance(
			'/test/:action', 'controller=testing,action=route,params=,lang=de', 1
		);
		$route2 = fl_route::get_instance(
			'/pages/:id', 'controller=pages,action=show,params=,lang=de', 2
		);
		$route2->set_modul('info');
		$url = 'http://localhost/info';

		$this->object->add_route($route1);
		$this->object->add_route($route2);
		$result = $this->object->analyse($url);

		$expected = array(
			'controller'=>'pages',
			'action'=>'show',
			'params'=>'',
			'lang'=>'de',
			'query'=>'',
		);

		$this->assertType('fl_route', $result);
		$this->assertEquals($result, $route2);
		$this->assertEquals($expected, $result->get_request());
	}

	/**
	 * @covers fl_dispatcher::analyse
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
			'modul'=>'testing'
		);

		$this->assertType('fl_route', $result);
		$this->assertEquals($result, $route);
		$this->assertEquals($expected, $result->get_request());
	}

	/**
	 * @covers fl_dispatcher::analyse
	 */
	public function testURLAnalysisThrowsException() {
		$route = fl_route::get_instance(
			'/:controller/:action/:params', 'controller=defaultController,action=defaultAction,params=', 255
		);
		$url = 'http://localhost/';

		$this->object->add_route($route);
		try {
			$result = $this->object->analyse($url);
		} catch ( RuntimeException $e ) {
			$this->assertType('RuntimeException', $e);
			return;
		}

		$this->fail();
	}

	/**
	 * @covers fl_dispatcher::analyse
	 */
	public function testModulMayBeLinkedToControllerName() {
		$route = fl_route::get_instance(
			'/test/:action', 'controller=testing,action=route,params=,lang=de', 1
		);
		$route->set_modul('&controller');
		$url = 'http://localhost/test';

		$this->object->add_route($route);
		$result = $this->object->analyse($url);

		$expected = array(
			'controller'=>'testing',
			'action'=>'route',
			'params'=>'',
			'lang'=>'de',
			'query'=>'',
		);

		$this->assertType('fl_route', $result);
		$this->assertEquals($result, $route);
		$this->assertEquals($expected, $result->get_request());
	}
}

// Call fl_dispatcherTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == 'fl_dispatcherTest::main') {
    fl_dispatcherTest::main();
}
?>
