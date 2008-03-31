<?php
/**
 * Allgemeines Datenobjekt
 */
class data extends data_structure {
	public function  __construct() {
		trigger_error('veraltete Datenstruktur', E_USER_NOTICE);
		parent::__construct();
	}
}

?>
