<?php
/**
 * Interface fuer Datenzugriffe
 *
 * Es muessen die grundlegenden CRUD-Methoden implementiert werden.
 *
 * @author Matthias Viehweger <kronn@kronn.de>
 * @version 0.2
 * @package federleicht
 * @subpackage base
 */
interface data_access {
	public function create();
	public function retrieve();
	public function update();
	public function del();
}
