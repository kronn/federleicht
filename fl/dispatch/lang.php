<?php
/**
 * Language-Klasse
 *
 * @package federleicht
 * @subpackage base
 */
class fl_dispatch_lang {
	var $default_lang = '';
	var $all = array();
	var $language;

	function __construct($default, $all) {
		$this->default_lang = $default;
		$this->all = $all;
	}

	function set($page) {
		$defaultlang = $this->default_lang;
		$all_langs = $this->all;
		if ( isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) AND !empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) ) { // Sprache aus ACCEPT-Header herauslesen, wenn möglich
			$accept_header = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
			if ( is_array($accept_header) ) {
				array_unique($accept_header);
				foreach( $accept_header as $language ) {
					$language = substr($language, 0, 2);
					if ( in_array($language, $all_langs) ) {
						$defaultlang = $language;
					break(1);
					}
				}
			}
		}
		$temp = explode('/',$page);
		if ( count($temp) >= 1 ) {
			$lang = ( in_array($temp[0], $all_langs) )? array_shift($temp): $defaultlang;
			$page = implode('/',$temp);
		} else {
			$lang = $defaultlang;
			$page = implode('/',$temp);
		}

		if (!defined('LANG') ) {
			define('LANG', $lang);
		}
		$this->language = $lang;

		unset($temp, $lang, $defaultlang, $all_langs, $accept_header);
		return $page;
	}

	function get_lang() {
		return $this->language;
	}
}
