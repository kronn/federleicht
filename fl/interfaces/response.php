<?php
/**
 * Interface fuer Serverseitige Antworten
 *
 * @package federleicht
 * @subpackage base
 */
interface response {
	public function set_type($type);
	public function set_template($outer = null, $inner = null);

	public function set_status($status);
	public function add_header($header, $value);
	public function add_content($content);

	public function set_body($body);
	public function get_body();

	public function flush();
	public function export();
}
