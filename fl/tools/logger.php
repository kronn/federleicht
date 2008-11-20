<?php
class fl_logger {
	const WITHOUT_TIME = false;
	const WITH_TIME = true;

	protected $logfile;

	public function __construct($logfile) {
		$this->logfile = $logfile;
	}

	public function log($msg, $with_time = null ) {
		$msg_string = '';

		if ( $with_time === null) {
			$with_time = self::WITH_TIME;
		}

		if ( $with_time === self::WITH_TIME ) {
			$msg_string .= date('Y-m-d H:i:s') . ' - ';
		}

		$msg_string .= $msg;

		return $this->write_to_log($msg_string);
	}

	protected function write_to_log($msg) {
		return error_log($msg . PHP_EOL, 3, $this->logfile);
	}
}
