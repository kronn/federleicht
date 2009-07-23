<?php
/**
 * vereinfachte Datenausgabe
 *
 * @author Matthias Viehweger <kronn@kronn.de>
 * @version 0.1
 * @license MIT
 */
class output {
	function output() {
	}

	/**
	 * Daten zeilenweise ausgeben
	 *
	 * @param array $data
	 */
	function lines($data) {
		foreach( $data as $value ) {
			echo $value . PHP_EOL;
		}
	}

	/**
	 * Array als Tabelle ausgeben
	 *
	 * @param array $data
	 * @return string
	 */
	function table($data) {
		$new_row = '</tr><tr>';

		$keys = array_keys( $data[0] );

		$html = array();
		$html[] = '<table><tr>';

		foreach ( $keys as $value ) {
			$html[] = '<th>'.$value.'</th>';
		}
		$html[] = $new_row;

		foreach ( $data as $row ) {
			foreach ( $row as $key=>$value ) {
				$html[] = '<td>'.$value.'</td>';
			}

			$html[] = $new_row;
		}

		$html[] = '<td colspan="'.count($keys).'">'.count($data).' Datensätze</td>';

		$html[] = '</tr></table>';

		return $html;
	}
}
