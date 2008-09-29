<?php
// Call fl_converterTest::main() if this source file is executed directly.
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'fl_converterTest::main');
}

if ( !defined('ABSPATH') ) {
	$abspath = realpath(dirname(__FILE__) . '/../../');
	define('ABSPATH', $abspath . '/');
}

require_once 'PHPUnit/Framework.php';
require_once ABSPATH . 'test/fl/test.php';

/**
 * Test class for fl_converter.
 * Generated by PHPUnit on 2008-09-10 at 19:26:01.
 */
class fl_converterTest extends PHPUnit_Framework_TestCase {
	public static function main() {
		require_once 'PHPUnit/TextUI/TestRunner.php';

		$suite  = new PHPUnit_Framework_TestSuite('fl_converterTest');
		$result = PHPUnit_TextUI_TestRunner::run($suite);
	}

	public function testStringToArray() {
		$string = '1=eins,2=zwei,3=drei';
		$array = array(
			'1'=>'eins',
			'2'=>'zwei',
			'3'=>'drei'
		);
		$result = fl_converter::string_to_array($string);

		$this->assertEquals($array, $result);
	}
	public function testArrayToDropdownArray() {
		$input = array(
			'1'=>'eins',
			'2'=>'zwei',
			'3'=>'drei'
		);
		$expected = array(
			array('id'=>'1', 'name'=>'eins'),
			array('id'=>'2', 'name'=>'zwei'),
			array('id'=>'3', 'name'=>'drei')
		);
		$result = fl_converter::array_to_dropdown_array($input);

		$this->assertEquals($expected, $result);
	}

	public function testStringToDropdownArray() {
		$string = '1=eins,2=zwei,3=drei';
		$result = fl_converter::string_to_dropdown_array($string);
		$expected = array(
			array('id'=>'1', 'name'=>'eins'),
			array('id'=>'2', 'name'=>'zwei'),
			array('id'=>'3', 'name'=>'drei')
		);

		$this->assertType('array', $result);
		$this->assertEquals($expected, $result);
	}
	public function testStringToDropdownArray2() {
		$string = 'eins,zwei,drei';
		$result = fl_converter::string_to_dropdown_array($string);
		$expected = array(
			array('id'=>'eins', 'name'=>'eins'),
			array('id'=>'zwei', 'name'=>'zwei'),
			array('id'=>'drei', 'name'=>'drei')
		);

		$this->assertType('array', $result);
		$this->assertEquals($expected, $result);
	}
	public function testDropdownArrayToString() {
		$input = array(
			array('id'=>'1', 'name'=>'eins'),
			array('id'=>'2', 'name'=>'zwei'),
			array('id'=>'3', 'name'=>'drei')
		);
		$expected = '1=eins,2=zwei,3=drei';
		$result = fl_converter::dropdown_array_to_string($input);

		$this->assertEquals($expected, $result);
	}

	public function testDropdownArrayToArray() {
		$input = array(
			array('id'=>'1', 'name'=>'eins'),
			array('id'=>'2', 'name'=>'zwei'),
			array('id'=>'3', 'name'=>'drei')
		);
		$array = array(
			'1'=>'eins',
			'2'=>'zwei',
			'3'=>'drei'
		);

		$result = fl_converter::dropdown_array_to_array($input);

		$this->assertEquals($array, $result);
	}
}

// Call fl_converterTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == 'fl_converterTest::main') {
    fl_converterTest::main();
}
?>