<?php 
/**
 * Template um Dateien einfach einzubinden (Durchleitung einer verborgenen Datei)
 *
 * @package federleicht
 * @subpackage view
 */
header("Content-length: " . $this->get('length', 'int') );
header("Content-type: " . $this->get('mime', 'string', '', FALSE, 'application/octet-stream') );

require $this->get('file');
