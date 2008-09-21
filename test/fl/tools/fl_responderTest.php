<?php
// Call fl_responderTest::main() if this source file is executed directly.
if (!defined('PHPUnit_MAIN_METHOD')) {
	define('PHPUnit_MAIN_METHOD', 'fl_responderTest::main');
}

if ( !defined('ABSPATH') ) {
	$abspath = realpath(dirname(__FILE__) . '/../../../');
	define('ABSPATH', $abspath . '/');
}

require_once 'PHPUnit/Framework.php';
require_once ABSPATH . 'test/fl/test.php';

/**
 * Test class for fl_responder.
 */
class fl_responderTest extends PHPUnit_Framework_TestCase {
	protected $object; // fl_responder
	protected $factory; // Mock: fl_factory
	public static function main() {
		require_once 'PHPUnit/TextUI/TestRunner.php';

		$suite  = new PHPUnit_Framework_TestSuite('fl_responderTest');
		$result = PHPUnit_TextUI_TestRunner::run($suite);
	}
	protected function setUp() {
		/**
		 * TestDouble der Factory erstellen und diese dem Responder übergeben
		 */
		$this->factory = $this->getMock('fl_factory', array('create'));
		$this->object = new fl_responder($this->factory);
	}
	protected function tearDown() {
	}

	/**
	 * Hilfsmethode um komplexe Fixture aufzubauen
	 */
	protected function addSomeResponses() {
		# $this->object->add_response('view');
	}

	public function testAdd_response() {
		$result = $this->object->add_response('http');

		$this->assertType('integer', $result);
	}
	public function testFactoryIsCalled() {
		// erwartetes Verhalten
		// Es wird hier geprüft, dass die Factory-Methode "create" einmal aufgerufen 
		// wird, und zwar mit dem Parameter "mvc_response_http"
		$this->factory->expects($this->once())
			->method('create')
			->with($this->equalTo('mvc_response_http'));

		// sut ausführen
		$this->object->add_response('http');
	}

	/**
	 * @todo Implement testSet_current_response().
	 */
	public function testSet_current_response() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testSet().
	 */
	public function testSet() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testGet().
	 */
	public function testGet() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testSay().
	 */
	public function testSay() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testSet_data().
	 */
	public function testSet_data() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testIs_set().
	 */
	public function testIs_set() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testRemove().
	 */
	public function testRemove() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testCurrent().
	 */
	public function testCurrent() {
		$this->addSomeResponses();
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testKey().
	 */
	public function testKey() {
		$this->addSomeResponses();
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testNext().
	 */
	public function testNext() {
		$this->addSomeResponses();
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testRewind().
	 */
	public function testRewind() {
		$this->addSomeResponses();
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testValid().
	 */
	public function testValid() {
		$this->addSomeResponses();
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}

	public function testImplementsIterator() {
		$this->assertType('Iterator', $this->object);
	}

	public function testImplementsData_wrapper() {
		$this->assertType('data_wrapper', $this->object);
	}

	public function testSetCurrentResponseThrowsException() {
		try {
			$this->object->set_current_response(2);
		} catch ( OutOfBoundsException $e ) {
			return true;
		}

		$this->fail();
	}
}

// Call fl_responderTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == 'fl_responderTest::main') {
	fl_responderTest::main();
}
?>
