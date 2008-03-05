<?php
/**
 * Interface fuer Datenzugriffe
 *
 * Es muessen die grundlegenden CRUD-Methoden implementiert werden.
 * $target kann dabei z.B. eine Tabellenbezeichnung, eine XPath-Angabe
 * oder auch ein Pfad im Dateisystem sein.
 *
 * @author Matthias Viehweger <kronn@kronn.de>
 * @version 0.1
 * @package federleicht
 * @subpackage base
 */
interface data_accessor {
	public function create($target, array $data);
	public function retrieve($target, $fields, $conditions, $order, $limit );
	public function update($target, array $data, $id);
	public function del($target, $id);
}
