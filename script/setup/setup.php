<?php
/**
 * Setup-Klasse
 *
 * @package federleicht
 * @subpackage script
 */
class fl_setup {
	public $install_message = '';
	public $welcome_message = '';
	public $svn_available = false;

	private $abspath = null;
	private $target_path = null;
	private $appname = null;
	private $svn = null;

	/**
	 * Konstruktor
	 */
	public function __construct($appname = 'Federleicht Application', $target_path = null) {
		$this->abspath = realpath(dirname(__FILE__) . '/../') . '/';
		$this->target_path = ( $target_path === null )? $this->abspath: $target_path;
		$this->appname = $appname;
		
		if ( $this->svn_available = $this->check_for_svn() ) {
			$this->svn = null; # Object: SVN-Client
		}

		$this->add_message(
			'install', 
			<<<EOT
Das Framework muss noch in das Verzeichnis fl/ ausgecheckt werden.

Am besten ist es, zuerst die Verzeichnisstruktur in das Projekt-Repository
zu importieren und dann dem Verzeichnis fl/ einen Verweis auf das
Federleicht-Repository hinzuzufügen.

URL des Frameworks: http://svn2.assembla.com/svn/federleicht
EOT
		);
	}

	/**
	 * Verzeichnisse erstellen
	 */
	public function create_dirs() {
		$dirs = array(
			'app',
			'cache',
			'config',
			'db',
			'fl',
			'log',
			'public',
			'script',
			'test'
		);

		foreach ( $dirs as $dir ) {
			$dirname = $this->target_path . $dir;

			if ( is_dir( $dirname ) ) {
			} else {
				mkdir( $dirname, 0755 );
			}
		}
	}

	/**
	 * Framework-Code auschecken
	 */
	public function checkout_federleicht() {
	}

	/**
	 * Beispieldateien auschecken
	 */
	public function checkout_config_samples() {
	}

	/**
	 * Messges erweitern
	 *
	 * @param string $type
	 * @param string $message
	 */
	private function add_message($type, $message) {
		$type = $type . '_message';
		$this->$type .= $message;
	}

	/**
	 * Prüfen, ob Subversion-Zugriff möglich ist
	 *
	 * @return boolean
	 */
	private function check_for_svn() {
		return false;
	}
}
