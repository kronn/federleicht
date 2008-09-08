<?php
/**
 * Inflector-Klasse
 *
 * Bietet Hilfe bei Pluralbildung und Singularbildung
 *
 * @author Matthias Viehweger <kronn@kronn.de>
 * @version 0.1
 */
class fl_inflector {
	/**
	 * Speicher für Ersetzungsregeln
	 */
	var $plural = array();
	var $singular = array();
	var $uncountable = array();
	var $irregular = array();

	/**
	 * Konstruktor
	 */
	function __construct($lang = null) {
		switch ( $lang ) {
		case 'de':
			$this->plural = array(
				'/^([a-z]*)(ung|heit|schaft|ion)$/i'=>'\1\2en',
				'/^([a-z]*)(probe)$/i'=>'\1\2n',
				'/^([a-z]*)([ei])(ld)$/i'=>'\1\2\3er',
				'/^([a-z]*)([auo])(ld|ch|nd)$/i'=>'\1\2e\3er',
				'/^([a-z]*[^aoieukgh])$/i' =>'\1e',
			);

			$this->singular = array(
				'/^([a-z]*)(ung|heit|schaft|ion)en$/i'=>'\1\2',
				'/^([a-z]*)(probe)n$/i'=>'\1\2',
				'/^([a-z]*)([ei])(ld)er$/i'=>'\1\2\3',
				'/^([a-z]*)([auo])e(ld|ch|nd)er$/i'=>'\1\2\3',
				'/^([a-z]*)e/i' => '\1',
			);

			$this->uncountable = array(
				'daten', 
				'bankdaten', 
				'cover',
				'musiktitel',
				'titel',
				'merchandise',
				'poster',
				'kleidung'
			);

			$this->irregular = array(
				'account' => 'accounts',
				'land' => 'laender',
			);
			break;

		case 'en':
		default:
			$this->plural = array(
				'/(quiz)$/i' => '\1zes',
				'/^(ox)$/i' => '\1en',
				'/([m|l])ouse$/i' => '\1ice',
				'/(matr|vert|ind)ix|ex$/i' => '\1ices',
				'/(x|ch|ss|sh)$/i' => '\1es', 
				'/([^aeiouy]|qu)ies$/i' => '\1y', 
				'/([^aeiouy]|qu)y$/i' => '\1ies', 
				'/(hive)$/i' => '\1s', 
				'/(?:([^f])fe|([lr])f)$/i' => '\1\2ves', 
				'/sis$/i' => 'ses', 
				'/([ti])um$/i' => '\1a', 
				'/(buffal|tomat)o$/i' => '\1oes', 
				'/(bu)s$/i' => '\1ses', 
				'/(alias|status)/i'=> '\1es', 
				'/(octop|vir)us$/i'=> '\1i', 
				'/(ax|test)is$/i'=> '\1es', 
				'/s$/i'=> 's', 
				'/$/'=> 's'
			); 

			$this->singular = array ( 
				'/(quiz)zes$/i' => '\\1',
				'/(matr)ices$/i' => '\\1ix',
				'/(vert|ind)ices$/i' => '\\1ex',
				'/^(ox)en/i' => '\\1',
				'/(alias|status)es$/i' => '\\1',
				'/([octop|vir])i$/i' => '\\1us',
				'/(cris|ax|test)es$/i' => '\\1is',
				'/(shoe)s$/i' => '\\1',
				'/(o)es$/i' => '\\1',
				'/(bus)es$/i' => '\\1',
				'/([m|l])ice$/i' => '\\1ouse',
				'/(x|ch|ss|sh)es$/i' => '\\1',
				'/(m)ovies$/i' => '\\1ovie',
				'/(s)eries$/i' => '\\1eries',
				'/([^aeiouy]|qu)ies$/i' => '\\1y',
				'/([lr])ves$/i' => '\\1f',
				'/(tive)s$/i' => '\\1',
				'/(hive)s$/i' => '\\1',
				'/([^f])ves$/i' => '\\1fe',
				'/(^analy)ses$/i' => '\\1sis',
				'/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\\1\\2sis',
				'/([ti])a$/i' => '\\1um',
				'/(n)ews$/i' => '\\1ews',
				'/s$/i' => '',
			);
			
			$this->uncountable = array(
				'equipment', 
				'information', 
				'rice', 
				'money', 
				'species', 
				'series', 
				'fish', 
				'sheep'
			); 

			$this->irregular = array( 
				'person' => 'people', 
				'man' => 'men', 
				'child' => 'children', 
				'sex' => 'sexes', 
				'move' => 'moves'
			);
			break;
		}
	}

	/**
	 * Bildung der Pluralform eines Wortes
	 *
	 * @param string $word
	 * @return string Pluralform
	 */
	function plural($word) {
		$plural = $this->plural;
		$uncountable = $this->uncountable;
		$irregular = $this->irregular;

		$lowercased_word = strtolower($word); 

		foreach ($uncountable as $_uncountable){ 
			if(substr($lowercased_word,(-1*strlen($_uncountable))) == $_uncountable){ 
				return $word; 
			} 
		} 

		foreach ($irregular as $_plural=> $_singular){ 
			if (preg_match('/('.$_plural.')$/i', $word, $arr)) { 
				return preg_replace('/('.$_plural.')$/i', substr($arr[0],0,1).substr($_singular,1), $word); 
			} 
		} 

		foreach ($plural as $rule => $replacement) { 
			if (preg_match($rule, $word)) { 
				return preg_replace($rule, $replacement, $word); 
			} 
		} 

		return false;
	}

	/**
	 * Bildung der Singularform eines Wortes
	 *
	 * @param string $word
	 * @return string Singularform
	 */
	function singular($word) {
		$singular = $this->singular;
		$uncountable = $this->uncountable;
		$irregular = $this->irregular;

		$lowercased_word = strtolower($word);
		foreach ($uncountable as $_uncountable){
			if(substr($lowercased_word,(-1*strlen($_uncountable))) == $_uncountable){
				return $word;
			}
		}

		foreach ($irregular as $_plural=> $_singular){
			if (preg_match('/('.$_singular.')$/i', $word, $arr)) {
				return preg_replace('/('.$_singular.')$/i', substr($arr[0],0,1).substr($_plural,1), $word);
			}
		}

		foreach ($singular as $rule => $replacement) {
			if (preg_match($rule, $word)) {
				return preg_replace($rule, $replacement, $word);
			}
		}

		return $word;
	}
}
