<?php 
/**
 * HTTP-Response
 */
class fl_mvc_response_http implements response {
	protected $type = 'http';
	protected $outer;
	protected $inner;
	protected $status = 200;
	protected $headers = array();
	protected $content = '';

	public function __construct() {
	}

	/**
	 * Methoden des Interface response
	 */
	public function set_type($type) {
		$this->type($type);
	}
	public function set_template($outer = null, $inner = null) {
		$this->outer = $outer;
		$this->inner = $inner;
	}

	public function set_status($status) {
		$this->status = $status;
	}
	public function add_header($header, $value) {
		$this->headers[$header] = $value;
	}
	public function add_content($content) {
		$this->content .= $content;
	}

	public function set_body($body) {
		$this->content = $body;
	}
	public function get_body() {
		return $this->content;
	}

	public function flush() {
		header('HTTP 1.1/'.$this->get_status());
		foreach ( $this->headers as $key => $value ) {
			header("$key: $value");
		}
		echo $this->content;
	}
	public function export() {
		return array(
			'headers'=>$this->headers,
			'body'=>$this->get_body(),
		);
	}
	/**
	 * Methoden des Interface response Ende
	 */

	protected function get_status() {
		return $this->status;
	}
}
