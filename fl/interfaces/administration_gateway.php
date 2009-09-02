<?php
/**
 * Interface, dass Schnittstellen in Models definiert, die
 * unter anderem von Admininstrationsoberflächen genutzt
 * werden können
 *
 * @package federleicht
 * @subpackage integration
 * @version 0.1
 */
interface administration_gateway {
	public function writeable_fields();
	public function readable_fields();
	public function all_fields(array $data);

	public function list_entries();
	public function get_entry($id);
	public function update_entry($id, array $data);
	public function create_entry(array $data);
	public function delete_entry($id);
}
