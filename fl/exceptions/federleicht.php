<?php
class FederleichtException extends Exception {
	protected $details;

	public function __construct($msg = '', $details = null, $code = 0) {
		$this->details = $details;
		parent::__construct($msg, $code);
	}

	public function getDetails() {
		return $this->details;
	}
}
