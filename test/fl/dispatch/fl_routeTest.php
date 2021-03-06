<?php
// Call fl_routeTest::main() if this source file is executed directly.
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'fl_routeTest::main');
}

if ( !defined('FL_ABSPATH') ) {
	$abspath = realpath(dirname(__FILE__) . '/../../../');
	define('FL_ABSPATH', $abspath . '/');
}

require_once 'PHPUnit/Framework.php';

require_once FL_ABSPATH . 'test/fl/test.php';
require_once FL_ABSPATH . 'fl/dispatch/route.php';

/**
 * Test class for fl_route.
 * Generated by PHPUnit on 2008-09-01 at 18:56:47.
 */
class fl_routeTest extends PHPUnit_Framework_TestCase {
	/**
	 * @var    fl_route
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

		$suite  = new PHPUnit_Framework_TestSuite('fl_routeTest');
		$result = PHPUnit_TextUI_TestRunner::run($suite);
	}

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @access protected
	 */
	protected function setUp() {
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @access protected
	 */
	protected function tearDown() {
	}

	public function testEinfacheInstanziierungMitGet_instance() {
		$route = fl_route::get_instance(
			'/:controller/:action/:id',
			'controller=test,action=show,id=',
			1
		);

		$this->assertType('fl_route', $route);
	}
	public function testEinfacheInstanziierungMitGet_instance2() {
		$route = fl_route::get_instance(
			'/:controller/:action/:id',
			'controller=test,action=show,id=',
			1,
			array('action'=>'[a-z]{,4}')
		);

		$this->assertType('fl_route', $route);
	}

	public function testGueltigkeitEinerRouteTestenMitTry_route() {
		$route = fl_route::get_instance('/test', 'controller=test,action=show', 1);
		$url = '/test/view';

		$result = $route->try_route($url);
		$this->assertFalse($result);
	}
	public function testGueltigkeitEinerRouteTestenMitTry_route2() {
		$route = fl_route::get_instance('/indirect/:action', 'controller=test,action=show', 2);
		$url = '/indirect/view';

		$result = $route->try_route($url);
		$this->assertTrue($result);

		$request = $route->get_request();
		$this->assertEquals($request['controller'], 'test');
		$this->assertEquals($request['action'], 'view');
	}
	public function testGueltigkeitEinerRouteTestenMitTry_route3() {
		$route = fl_route::get_instance('/:controller/:action', 'controller=testing,action=show', 3);
		$url = '/test/view';

		$result = $route->try_route($url);
		$this->assertTrue($result);

		$request = $route->get_request();
		$this->assertEquals($request['controller'], 'test');
		$this->assertEquals($request['action'], 'view');
	}

	/**
	 * @todo Implement testGet_request().
	 */
	public function testGet_request() {
			// Remove the following lines when you implement this test.
			$this->markTestIncomplete(
				'This test has not been implemented yet.'
			);
	}

	/**
	 * @todo Implement testGet_partial_regex().
	 */
	public function testGet_partial_regex() {
			// Remove the following lines when you implement this test.
			$this->markTestIncomplete(
				'This test has not been implemented yet.'
			);
	}

	/**
	 * @todo Implement testGet_language_key().
	 */
	public function testGet_language_key() {
			// Remove the following lines when you implement this test.
			$this->markTestIncomplete(
				'This test has not been implemented yet.'
			);
	}

	public function testMake_url() {
		$route = fl_route::get_instance(
			'/city/:action/:lang',
			'controller=cities,action=index,lang=',
			10
		);

		$parts = array(
			'controller'=>'cities',
			'action'=>'show',
			'lang'=>'de'
		);

		$expected = 'city/show/de';
		$result = $route->make_url($parts);

		$this->assertEquals($expected, $result);
	}
	public function testMake_url2() {
		$route = fl_route::get_instance(
			'/city/:action/:lang',
			'controller=cities,action=index,lang=',
			10
		);

		$parts = array(
			'controller'=>'cities',
			'action'=>'',
			'lang'=>''
		);

		$expected = 'city/index/';
		$result = $route->make_url($parts);

		$this->assertEquals($expected, $result);
	}
	public function testMake_url3() {
		$route = fl_route::get_instance(
			'/city/:action/:lang',
			'controller=cities,action=index,lang=',
			10
		);

		$parts = array(
			'controller'=>'cities',
			'action'=>'show',
			'lang'=>''
		);

		$expected = 'city/show/';
		$result = $route->make_url($parts);

		$this->assertEquals($expected, $result);
	}
	public function testMake_url4() {
		$route = fl_route::get_instance(
			'/city/:action/:lang',
			'controller=cities,action=index,lang=',
			10
		);

		$parts = array(
			'action'=>'show',
			'lang'=>'de'
		);

		$expected = 'city/show/de';
		$result = $route->make_url($parts);

		$this->assertEquals($expected, $result);
	}

	/**
	 * @todo Implement testGet_current_url().
	 */
	public function testGet_current_url() {
			// Remove the following lines when you implement this test.
			$this->markTestIncomplete(
				'This test has not been implemented yet.'
			);
	}

	public function testRoutenVergleichenMitCompare_routes() {
		$route1 = fl_route::get_instance('/indirect/:action', 'controller=test,action=show', 2);
		$route2 = fl_route::get_instance('/:controller/:action', 'controller=testing,action=show', 3);

		$result = fl_route::compare_routes($route1, $route2);

		$this->assertEquals($result, -1);
	}

	public function testRoutenSortierenMitUsort() {
		$route0 = fl_route::get_instance('/test', 'controller=test,action=show', 1);
		$route1 = fl_route::get_instance('/indirect/:action', 'controller=test,action=show', 2);
		$route2 = fl_route::get_instance('/:controller/:action', 'controller=testing,action=show', 3);

		$routes = array($route1, $route2, $route0);

		$result = usort($routes, array('fl_route', 'compare_routes'));
	
		$expected = array($route0, $route1, $route2);

		$this->assertTrue($result);
		$this->assertEquals($routes, $expected);
	}
}

// Call fl_routeTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == 'fl_routeTest::main') {
	fl_routeTest::main();
}
?>
