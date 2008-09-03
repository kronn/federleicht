<?php 
/**
 * Syntaxpruefung
 */
class syntax_checker {
	private $register;
	private $count = 0;
	private $error = array();

	public function __construct() {
		$this->register = array();
		$this->path = ( defined('ABSPATH') )?
			ABSPATH: 
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
			if ( ($this->count % 40) === 0 ) {
				echo PHP_EOL;
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
 * Ausgabe von Meldungen
 */
echo PHP_EOL;
$linter->show_errors();
echo PHP_EOL;
$linter->show_stats();
?>