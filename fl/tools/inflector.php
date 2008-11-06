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
	protected $plural = array();
	protected $singular = array();
	protected $uncountable = array();
	protected $irregular = array();

	protected $msg = array();

	/**
	 * Konstruktor
	 */
	function __construct($lang = null) {
		switch ( $lang ) {
		case 'de':
			$this->plural = array(
				'/^([a-z]*)(ung|heit|schaft|ion)$/i'=>'\1\2en',
				'/^([a-z]*)([ei])(ld)$/i'=>'\1\2\3er',
				'/^([a-z]*)([auo])(ld|ch|nd)$/i'=>'\1\2e\3er',
				'/^([a-z]*[^aoieukgh])$/i' =>'\1e',
			);

			$this->singular = array(
				'/^([a-z]*)(ung|heit|schaft|ion)en$/i'=>'\1\2',
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
				'beat' => 'beats',
				'album' => 'alben',
				'album_titel' => 'alben_titel',
				'genre' => 'genres',
				'hoerprobe' => 'hoerproben'
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

		foreach ($irregular as $_singular=> $_plural){
			if (preg_match('/('.$_singular.')$/i', $word, $arr)) { 
				return preg_replace('/('.$_singular.')$/i', substr($arr[0],0,1).substr($_plural,1), $word); 
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

		foreach ($irregular as $_singular=> $_plural){
			if (preg_match('/('.$_plural.')$/i', $word, $arr)) {
				return preg_replace('/('.$_plural.')$/i', substr($arr[0],0,1).substr($_singular,1), $word);
			}
		}

		foreach ($singular as $rule => $replacement) {
			if (preg_match($rule, $word)) {
				return preg_replace($rule, $replacement, $word);
			}
		}

		return $word;
	}

	function is_singular($word) {
		$has_singular_form = true;
		$has_plural_form = false;

		if ( in_array( strtolower($word), $this->uncountable ) ) {
			$this->msg[] = $word . ' is uncountable';
			return $has_singular_form;
		} else {
			foreach( $this->irregular as $singular => $plural ) {
				if ( stripos($singular, $word) !== false ) {
					$this->msg[] = $word . ' is irregular and singular';
					return $has_singular_form;
				} elseif ( stripos($plural, $word) !== false ) {
					$this->msg[] = $word . ' is irregular and plural';
					return false;
				}
			}

			$can_be_singular = false;
			$can_be_plural = false;

			foreach( $this->singular as $rule => $replacement ) {
				if ( preg_match($rule, $word) ) {
					$this->msg[] = $word . ' can be converted to singular (rule: '.$rule.' => '.$this->singular($word).')';
					$can_be_singular = true;
					break;
				} 
			}

			foreach( $this->plural as $rule => $replacement ) {
				if ( preg_match($rule, $word) ) {
					$this->msg[] = $word . ' can be converted to plural (rule: '.$rule.' => '.$this->plural($word).')';
					$can_be_plural = true;
					break;
				} 
			}

			if ( $can_be_plural and !$can_be_singular ) {
				return $has_singular_form;
			} elseif ( $can_be_singular and !$can_be_plural ) {
				return $has_plural_form;
			} elseif ( $can_be_singular and $can_be_plural ) {
				$might_be_plural = $this->plural($this->singular($word)) === $word;
				$might_be_singular = $this->singular($this->plural($word)) === $word;

				if ( $might_be_plural and !$might_be_singular ) {
					return $has_plural_form;
				} elseif ( $might_be_singular and !$might_be_plural) {
					return $has_singular_form;
				} elseif ( $might_be_plural and $might_be_singular ) {
					try {
						if ( $this->is_singular($this->singular($word)) ) {
							return $has_plural_form;
						} elseif ( ! $this->is_singular($this->plural($word)) ) {
							return $has_singular_form;
						}
					} catch ( RuntimeException $e ) {
						throw new LogicException('Inflector muss überarbeitet werden, um bessere Regeln für "'.$word.'" zu haben');
					}
				}
			} else {
				throw new RuntimeException('Es kann nicht herausgefunden werden, ob "'.$word.'" Singular oder Plural ist.');
			}
		}
	}

	public function __toString() {
		return implode(', ', $this->msg);
	}
}
