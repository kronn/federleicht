<?php
/**
 * Typconverter
 *
 * @author Matthias Viehweger
 * @version 0.1
 * @package federleicht
 * @subpackage tools
 */
class fl_converter {
	/**
	 * String zu Array umwandeln
	 *
	 * @param string $string
	 * @return array
	 */
	public static function string_to_array($string) {
		$array = array();

		foreach( self::string_to_dropdown_array($string) as $entry ) {
			$array[$entry['id']] = $entry['name'];
		}

		return $array;
	}

	/**
	 * Dropdown-String zu Array machen
	 *
	 * @param string $string
	 * @return array
	 */
	public static function string_to_dropdown_array($string) {
		$array = array();

		foreach( explode(',', $string) as $keyvaluepair ) {
			if ( strpos( $keyvaluepair, '=' ) === false ) {
				$id = $name = $keyvaluepair;
			} else {
				list($id, $name) = explode('=', $keyvaluepair, 2);
			}

			$array[] = array(
				'id'=>$id,
				'name'=>$name
			);
		}

		return $array;
	}

	/**
	 * Dropdown-Array zu String machen
	 *
	 * @param array $data
	 * @return string
	 */
	public static function dropdown_array_to_string($data) {
		foreach( $data as $value) {
			$string[] = $value['id'].'='.$value['name'];
		}

		$string = implode(',', $string);

		return $string;
	}
}
