<?php 
/**
 * Template um Dateien einfach herunterladbar zu machen
 *
 * @package federleicht
 * @subpackage view
 */
header("Content-length: " . $this->get('length', 'int') );
header("Content-type: " . $this->get('mime', 'string', '', FALSE, 'application/octet-stream') );
header("Content-Disposition: attachment; filename=" . $this->get('filename', 'string', '', FALSE, 'download.file') );

$this->get_sub_view(); 
