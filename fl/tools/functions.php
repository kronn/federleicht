<?php
/**
 * Federleicht-Funktionen
 *
 * Die Klasse functions enthält Methoden, die in den meisten
 * anderen Objekten verfügbar sind.
 *
 * @package federleicht
 * @subpackage base
 */
class fl_functions {
	/**
	 * Referenzen auf Objekte
	 */
	public $flash = null;
	public $factory = null;
	protected $logger = null;

	/**
	 * Konstruktor
	 */
	public function __construct() {
		$this->factory = new fl_factory();
		$this->logger = new fl_logger(fl_registry::get_instance()->get('path', 'log') . 'federleicht.log');
		fl_registry::get_instance()->set('logger', $this->logger);
	}

	/**
	 * Datenzugriff setzen
	 *
	 * @param data_access $data_access
	 */
	public function set_data_access(data_access $data_access) {
		$this->factory->set_data_access($data_access);
	}

	/**
	 * Flashnachrichten starten
	 */
	public function start_flash() {
		$this->flash = new fl_flash();
	}
 
	/**
	 * Nachricht in Log schreiben
	 */
	public function log($msg, $with_time = null) {
		return $this->logger->log($msg, $with_time);
	}

	/**
	 * Federleicht anhalten
	 */
	public function stop($message='') {
		ob_flush();
		if ( $message != '' ) $this->log($message);
		$this->log(PHP_EOL, fl_logger::WITHOUT_TIME);
		echo $message;
		exit();
	}
}
?>
