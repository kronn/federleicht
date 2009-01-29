<?php
/**
 * Lazyloading für ActiveRecord-Klassen
 *
 * Basisklasse, die konkreten Relationen werden in Unterklassen definiert
 */
class fl_data_loader_activerecord {
	protected $factory;
	protected $class_identifier;
	protected $given_data;
	protected $key_name;
	protected $key_value;
	protected $options;

	public function __construct($class, array $keys, array $data = array(), array $options = array() ) {
		$this->class_identifier = $class;
		$this->key_name = $keys['key_name'];
		$this->key_value = $keys['key'];
		$this->given_data = $data;
		$this->options = $options;
	}

	public function set_factory(fl_factory $factory) {
		$this->factory = $factory;
	}

	protected function find_data_table() {
		list($modul, $class_name) = $this->factory->parse_class_name($this->class_identifier, fl_factory::AR_CLASSNAME);
		$this->factory->load_class($modul, $class_name);

		$table_name = $this->factory->inflector->plural($class_name);
		$table_prefix = $this->factory->get_data_access()->table_prefix;
		$data_table = fl_data_structures_activerecord::get_table($class_name, $table_name, $table_prefix);

		return $data_table;
	}
}
