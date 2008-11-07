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
	function __construct($definitions) {
		if ( is_string($definitions) ) {
			trigger_error(
				'Parameter wird nicht mehr als Sprachhinweis verwendet, Definitionen via config/inflector.php erstellen', 
				E_USE_NOTICE
			);
		}

		$this->plural = $definitions['plural'];
		$this->singular = $definitions['singular'];
		$this->uncountable = $definitions['uncountable'];
		$this->irregular = $definitions['irregular'];
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
