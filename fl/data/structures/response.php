<?php
/**
 * Datenstruktur für Serverantwort
 *
 * @version 0.1
 * @author Matthias Viehweger <kronn@kronn.de>
 * @package federleicht
 * @subpackage base
 */
class fl_data_structures_response extends fl_data_structures_data {
	protected $http_header;
	protected $data;
	protected $layout;
	protected $subview;
}
