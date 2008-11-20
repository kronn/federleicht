<?php
class fl_timer {
	protected $start_time;
	protected $stop_time;

	public function __construct() {
	}

	protected function getmicrotime() {
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}

	public function start() {
		return $this->start_time = $this->getmicrotime();
	}

	public function stop() {
		return $this->stop_time = $this->getmicrotime();
	}

	public function get_time() {
		return $this->stop_time - $this->start_time;
	}

	public function format_time($time) {
		return substr(round($time, 5), 0, 7);
	}
}
