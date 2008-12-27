<?php
/**
 * Interface fuer Datenzugriffe auf eine Datenquelle
 *
 * Es muessen die grundlegenden CRUD-Methoden implementiert werden.
 * $target kann dabei z.B. eine Tabellenbezeichnung, eine XPath-Angabe
 * oder auch ein Pfad im Dateisystem sein.
 *
 * Die Funkion query dient zur direkten Abfrage der Datenquelle, um so 
 * nicht geplante Möglichkeiten zu nutzen (Datenbank optimieren, ...)
 *
 * @author Matthias Viehweger <kronn@kronn.de>
 * @version 0.3
 * @package federleicht
 * @subpackage base
 */
interface data_source_access /* extends data_access */ {
	public function create($target, array $data);
	public function retrieve($target, $fields='*', $conditions='', $order='', $limit='');
	public function update($target, array $data, $id);
	public function del($target, $id);
	public function query($query);
}
