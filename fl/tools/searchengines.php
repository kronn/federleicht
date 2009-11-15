<?php
/**
 * Analyse der Referrer-Header auf Suchbegriffe die bei Suchmaschinen eingegeben wurden
 *
 * @package federleicht
 * @subpackage helper
 */
class fl_searchengines {
	private $engine_data = array();
	private $min_laenge_suchwoerter = 3;

	public $searchwords = array();
	public $searchphrase = '';

	public function __construct() {
		$this->referer = isset( $_SERVER['HTTP_REFERER'] ) ?  trim( $_SERVER['HTTP_REFERER'] ) : '';
		$this->engine_data = $this->get_search_engine_data();
	}

	public function analyze() {
		if ( !empty($this->referer) ) {
			list($this->searchwords, $this->searchphrase) = $this->analyze_referer();
		}
	}

	private function get_search_engine_data() {
		$search_engines = array(
			'Abacho' => array(
				'name'		=> 'Abacho',
				'needle'	=> 'abacho.com',
				'query_var'	=> 'q',
				'icon'		=> 'abacho.png'
			),
			'Alexa' => array(
				'name'		=> 'Alexa',
				'needle'	=> 'alexa',
				'query_var'	=> 'q',
				'icon'		=> 'alexa.png'
			),
			'AllTheWeb' => array(
				'name'		=> 'AllTheWeb',
				'needle'	=> 'alltheweb.com',
				'query_var'	=> 'q',
				'icon'		=> 'alltheweb.png'
			),
			'Altavista' => array(
				'name'		=> 'Altavista',
				'needle'	=> 'altavista',
				'query_var'	=> 'q',
				'icon'		=> 'altavista.png'
			),
			'AOL (DE)' => array(
				'name'		=> 'AOL',
				'needle'	=> 'suche.aol',
				'query_var'	=> 'q',
				'icon'		=> 'aol.png'
			),
			'AOL (DE Nr2)' => array(
				'name'		=> 'AOL',
				'needle'	=> 'sucheaol.aol',
				'query_var'	=> 'q',
				'icon'		=> 'aol.png'
			),
			'AOL' => array(
				'name'		=> 'AOL',
				'needle'	=> 'search.aol',
				'query_var'	=> 'query',
				'icon'		=> 'aol.png'
			),
			'Ask Jeeves' => array(
				'name'		=> 'Ask Jeeves',
				'needle'	=> 'ask.com',
				'query_var'	=> 'q',
				'icon'		=> 'askjeeves.png'
			),
			'AT:Search' => array(
				'name'		=> 'AT:Search',
				'needle'	=> 'atsearch.at',
				'query_var'	=> 'qs',
				'icon'		=> 'search_engine.png'
			),
			'Baidu' => array(
				'name'		=> 'Baidu',
				'needle'	=> 'baidu.com',
				'query_var'	=> 'word',
				'icon'		=> 'baidu.png'
			),
			'Bluewin' => array(
				'name'		=> 'Bluewin',
				'needle'	=> 'search.bluewin.ch',
				'query_var'	=> 'qry',
				'icon'		=> 'search_engine.png'
			),
			'dir.com' => array(
				'name'		=> 'dir.com',
				'needle'	=> 'dir.com',
				'query_var'	=> 'req',
				'icon'		=> 'search_engine.png'
			),
			'DMOZ' => array(
				'name'		=> 'DMOZ',
				'needle'	=> 'dmoz.org',
				'query_var'	=> 'search',
				'icon'		=> 'dmoz.png'
			),
			'Exalead' => array(
				'name'		=> 'Exalead',
				'needle'	=> 'exalead',
				'query_var'	=> 'q',
				'icon'		=> 'exalead.png'
			),
			'Fireball' => array(
				'name'		=> 'Fireball',
				'needle'	=> 'fireball.de',
				'query_var'	=> 'query',
				'icon'		=> 'fireball.png'
			),
			'Freenet' => array(
				'name'		=> 'suche.freenet.de',
				'needle'	=> 'freenet',
				'query_var'	=> 'query',
				'icon'		=> 'freenet.png'
			),
			'Gigablast' => array(
				'name'		=> 'Gigablast',
				'needle'	=> 'gigablast.com',
				'query_var'	=> 'q',
				'icon'		=> 'gigablast.png'
			),
			'Google Images' => array(
				'name'		=> 'Google Images',
				'needle'	=> 'images.google',
				'query_var'	=> 'q',
				'icon'		=> 'google.png'
			),
			'Google Cache' => array(
				'name'		=> 'Google Cache',
				'needle'	=> '216.239.',
				'query_var'	=> 'q',
				'icon'		=> 'google.png'
			),
			'Google Cache' => array(
				'name'		=> 'Google Cache',
				'needle'	=> '209.85.',
				'query_var'	=> 'q',
				'icon'		=> 'google.png'
			),
			'Google Cache' => array(
				'name'		=> 'Google Cache',
				'needle'	=> '72.14.',
				'query_var'	=> 'q',
				'icon'		=> 'google.png'
			),
			'Google Cache' => array(
				'name'		=> 'Google Cache',
				'needle'	=> '66.102.',
				'query_var'	=> 'q',
				'icon'		=> 'google.png'
			),
			'Google Cache' => array(
				'name'		=> 'Google Cache',
				'needle'	=> '64.233.',
				'query_var'	=> 'q',
				'icon'		=> 'google.png'
			),
			'Google' => array(
				'name'		=> 'Google',
				'needle'	=> 'google',
				'query_var'	=> 'q',
				'icon'		=> 'google.png'
			),
			'HotBot' => array(
				'name'		=> 'HotBot',
				'needle'	=> 'hotbot.com',
				'query_var'	=> 'query',
				'icon'		=> 'hotbot.png'
			),
			'IlTrovatore' => array(
				'name'		=> 'IlTrovatore',
				'needle'	=> 'iltrovatore.it',
				'query_var'	=> 'q',
				'icon'		=> 'search_engine.png'
			),
			'Kvasir' => array(
				'name'		=> 'Kvasir',
				'needle'	=> 'kvasir.no',
				'query_var'	=> 'q',
				'icon'		=> 'search_engine.png'
			),
			'Live Search' => array(
				'name'		=> 'Live Search',
				'needle'	=> 'search.live.com',
				'query_var'	=> 'q',
				'icon'		=> 'livesearch.png'
			),
			'LookSmart' => array(
				'name'		=> 'LookSmart',
				'needle'	=> 'search.looksmart.com',
				'query_var'	=> 'qt',
				'icon'		=> 'looksmart.png'
			),
			'Lycos' => array(
				'name'		=> 'Lycos',
				'needle'	=> 'lycos',
				'query_var'	=> 'query',
				'icon'		=> 'lycos.png'
			),
			'Mirago' => array(
				'name'		=> 'Mirago',
				'needle'	=> 'mirago',
				'query_var'	=> 'qry',
				'icon'		=> 'mirago.png'
			),
			'MSN' => array(
				'name'		=> 'MSN',
				'needle'	=> 'msn',
				'query_var'	=> 'q',
				'icon'		=> 'msn.png'
			),
			'My Web Search' => array(
				'name'		=> 'My Web Search',
				'needle'	=> 'mywebsearch.com',
				'query_var'	=> 'searchfor',
				'icon'		=> 'search_engine.png'
			),
			'Naver' => array(
				'name'		=> 'Naver',
				'needle'	=> 'search.naver.com',
				'query_var'	=> 'query',
				'icon'		=> 'naver.png'
			),
			'Neomo' => array(
				'name'		=> 'Neomo',
				'needle'	=> 'search.neomo',
				'query_var'	=> 'q',
				'icon'		=> 'neomo.png'
			),
			'Netscape (DE)' => array(
				'name'		=> 'Netscape',
				'needle'	=> 'search.netscape.de',
				'query_var'	=> 'q',
				'icon'		=> 'netscape.png'
			),
			'Netscape' => array(
				'name'		=> 'Netscape',
				'needle'	=> 'search.netscape.com',
				'query_var'	=> 'query',
				'icon'		=> 'netscape.png'
			),
			'Overture' => array(
				'name'		=> 'Overture',
				'needle'	=> 'overture.com',
				'query_var'	=> 'Keywords',
				'icon'		=> 'overture.png'
			),
			'Quepasa' => array(
				'name'		=> 'Quepasa',
				'needle'	=> 'quepasa.com',
				'query_var'	=> 'q',
				'icon'		=> 'quepasa.png'
			),
			'search.ch' => array(
				'name'		=> 'search.ch',
				'needle'	=> 'search.ch',
				'query_var'	=> 'q',
				'icon'		=> 'search.ch.png'
			),
			'Search.com' => array(
				'name'		=> 'Search.com',
				'needle'	=> 'search.com',
				'query_var'	=> 'q',
				'icon'		=> 'search.com.png'
			),
			'Seekport' => array(
				'name'		=> 'Seekport',
				'needle'	=> 'seekport',
				'query_var'	=> 'query',
				'icon'		=> 'seekport.png'
			),
			'T-Online' => array(
				'name'		=> 'T-Online',
				'needle'	=> 'brisbane.t-online.de',
				'query_var'	=> 'q',
				'icon'		=> 't-online.png'
			),
			'T-Online' => array(
				'name'		=> 'T-Online',
				'needle'	=> 'suche.t-online.de',
				'query_var'	=> 'q',
				'icon'		=> 't-online.png'
			),
			'Teoma' => array(
				'name'		=> 'Teoma',
				'needle'	=> 'teoma.com',
				'query_var'	=> 'q',
				'icon'		=> 'teoma.png'
			),
			'Yahoo!' => array(
				'name'		=> 'Yahoo!',
				'needle'	=> 'yahoo',
				'query_var'	=> 'p',
				'icon'		=> 'yahoo.png'
			),
			'Vienna Online: Finder' => array(
				'name'		=> 'Vienna Online: Finder',
				'needle'	=> 'finder.vienna.at',
				'query_var'	=> 'query',
				'icon'		=> 'search_engine.png'
			),
			'Walhello' => array(
				'name'		=> 'Walhello',
				'needle'	=> 'walhello',
				'query_var'	=> 'key',
				'icon'		=> 'search_engine.png'
			),
			'Web.de' => array(
				'name'		=> 'Web.de',
				'needle'	=> 'suche.web.de',
				'query_var'	=> 'su',
				'icon'		=> 'web.de.png'
			),
			'Wikipedia' => array(
				'name'		=> 'Wikipedia',
				'needle'	=> 'wikipedia.org',
				'query_var'	=> 'search',
				'icon'		=> 'wikipedia.png'
			),
			'WiseNut' => array(
				'name'		=> 'WiseNut',
				'needle'	=> 'wisenut',
				'query_var'	=> 'q',
				'icon'		=> 'wisenut.png'
			)
		);

		return $search_engines;
	}

	private function format_keywords( $string ) {
		if( extension_loaded( 'mbstring' ) ) {
			$detected_encoding = mb_detect_encoding( $string, 'UTF-8,ISO-8859-1,ASCII,JIS,EUC-JP,SJIS' );
			$detected_encoding = $detected_encoding == TRUE ? $detected_encoding : 'UTF-8';
			$lowered_string = mb_strtolower( $string, $detected_encoding );
			if( $detected_encoding == TRUE && strtolower( $detected_encoding ) != 'utf-8' ) {
				$lowered_string = mb_convert_encoding( $lowered_string, 'UTF-8', $detected_encoding );
			}
		} else {
			$lowered_string = utf8_encode( strtolower( utf8_decode( $string ) ) );
		}

		return ( $lowered_string == TRUE ) ? $lowered_string : $string;
	}

	private function analyze_query($search_engine, $query) {
		parse_str( $query, $query_vars );
		$suchphrase = '';

		if( isset( $query_vars[$search_engine['query_var']] ) ) {
			$suchphrase = trim( stripslashes( $query_vars[$search_engine['query_var']] ) );

			// Cache. Bsp nach parse_str ( == urldecode): cache:t7avrie9tosj:www.google.com/ wort1 wort2
			if( substr( $suchphrase, 0, 6 ) == 'cache:') {
				$suchphrase = substr( $suchphrase, strpos( $suchphrase, ' ' ));
				$suchphrase = trim( $suchphrase );
			}

			if( empty( $suchphrase )
				OR ! preg_match( '/[\d\w]/', $suchphrase )
				OR ( strlen($suchphrase) < $this->min_laenge_suchwoerter )
			) {
				$suchphrase = '';
			} else {
				$suchphrase = $this->format_keywords( $suchphrase );
			}
		}

		return $suchphrase;
	}
	private function find_search_engine($host) {
		foreach( $this->engine_data as $search_engine ) {
			if ( strpos($host, $search_engine['needle'] ) === false ) continue;

			return $search_engine;
		}
		return false;
	}

	private function analyze_referer() {
		$url_data = parse_url($this->referer);
		$suchwoerter_array = array();
		$suchphrase = ''; 

		if ( $search_engine = $this->find_search_engine($url_data['host']) ) {
			$query =  isset( $url_data['query'] )? $url_data['query']: '';
			$suchphrase = $this->analyze_query($search_engine, $query);

			if ( preg_match_all('/(((\'|")?[^,]+\\3)|[^+, ]+)/', $suchphrase, $suche_matches ) ) {
				foreach ( $suche_matches[0] as $match ) {
					$match = trim($match);
					if ( empty( $match ) ) continue;
					if ( ! preg_match( '/[\d\w]/', $match ) ) continue;
					if ( strlen($match) < $this->min_laenge_suchwoerter ) continue;

					$suchwoerter_array[] = $match;
				}
			}
		}

		return array(
			$suchwoerter_array,
			$suchphrase
		);
	}
}
