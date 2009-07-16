<?php 
class text {
	public function __construct() {
	}
	
	public function truncate($text, $length = 30, $ending = '...') {
		if ( strlen($text) > ($length + strlen($ending)) ) {
			$truncated = substr($text, 0, $length) . $ending;
		} else {
			$truncated = $text;
		}

		return $truncated;
	}
}
?>
