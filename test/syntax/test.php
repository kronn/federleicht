<?php 
/**
 * Syntaxpruefung
 */
class syntax_checker {
	private $register;
	private $count = 0;
	private $error = array();
	private $line_length = 60;

	public function __construct() {
		$this->register = array();
		$this->path = ( defined('FL_ABSPATH') )?
			FL_ABSPATH: 
			realpath( dirname(__FILE__) . '/../../' );

		echo 'Syntax-Check aller PHP-Dateien'.PHP_EOL;
	}

	public function find_files($patterns) {
		foreach( $patterns as $pattern ) {
			$files = glob($this->path . $pattern);
			foreach( $files as $file ) {
				$this->register_file($file);
			}
		}
	}

	private function register_file($file) {
		$fi = pathinfo($file);
		$file = $fi['dirname'] . '/' . $fi['basename'];

		if ( strpos($fi['dirname'], 'library') !== FALSE ) {
			$new_file = FALSE;	
		} elseif ( in_array($file, $this->register) ) {
			$new_file = FALSE;
		} else {
			$new_file = TRUE;
		}

		if ( $new_file ) {
			$this->register[] = $file;
		}

		return $new_file;
	}

	public function check_syntax() {
		$total = count($this->register);

		foreach($this->register as $file) {
			$output = array();
			exec(
				'php -l -f "'.$file.'" 2>/dev/null ',
				$output,
				$return
			);
			array_pop($output);
			

			if ( $return > 0 ) {
				echo 'E';
				$this->add_error($output);
			} else {
				echo '.';
			}
			$this->count++;
			if ( ($this->count % $this->line_length) === 0 ) {
				$formatted_count = str_pad($this->count, strlen($total) + 1, ' ', 0);
				echo "$formatted_count / $total".PHP_EOL;
			}
		}
		echo PHP_EOL;
		return $this->count;
	}

	private function add_error($output) {
		foreach ( $output as $line ) {
			$line = trim($line);
			if ( empty($line) ) continue;

			$this->error[] = $line;
		}
	}

	public function show_errors() {
		if ( count($this->error) > 0 ) {
			echo implode(PHP_EOL, $this->error) . PHP_EOL;
		}
	}

	public function show_stats() {
		echo $this->count . ' Dateien wurden ueberprueft.'. PHP_EOL;
		echo count($this->error) . ' Fehler gefunden'.PHP_EOL;
	}

	public function count() {
		return count($this->error);
	}
}

function is_web() {
	if ( ! defined(PHP_SAPI) ) {
		return false;
	}

	return ( ! ( 
		in_array(PHP_SAPI, array(
			'apache', 
			'apache2handler', 
			'cgi'
		)) 
	) )? true: false;
}

if ( is_web() ) {
	echo '<pre>';
}

/**
 * Erzeugung
 */
$linter = new syntax_checker();

/**
 * Suchmuster fuer Dateien
 *
 * @todo die könnte nach möglichkeit auch via RecursiveDirectoryIterator implementiert werden...
 */
$patterns = array();
$patterns[] = '/*.php';
$patterns[] = '/*/*.php';
$patterns[] = '/*/*/*.php';
$patterns[] = '/*/*/*/*.php';
$patterns[] = '/*/*/*/*/*.php';
$patterns[] = '/*/*/*/*/*/*.php';
$patterns[] = '/*/*/*/*/*/*/*.php';
$patterns[] = '/*/*/*/*/*/*/*/*.php';

$linter->find_files($patterns);

/**
 * Durchfuehrung der Syntaxpruefung
 */
$linter->check_syntax();

/**
 * Fehlerstatus für andere Skripte bekanntgeben
 */
if ( $linter->count() > 0 ) {
	$syntax_error_state = true;
}

/**
 * Ausgabe von Meldungen
 */
echo PHP_EOL;
$linter->show_errors();
echo PHP_EOL;
$linter->show_stats();
echo PHP_EOL;

if ( is_web() ) { 
	echo '</pre>';
}
?>
