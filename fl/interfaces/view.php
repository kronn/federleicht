<?php
/**
 * View-Schnittstelle
 *
 * Verarbeitung der vom Controller erstellten Response und
 * Erzeugung des Response-Content, ggf. mit einem Template-
 * system.
 *
 * @author Matthias Viehweger <kronn@kronn.de>
 * @version 0.1
 * @package federleicht
 * @subpackage base
 */
interface view {
	public function execute($response);
}
