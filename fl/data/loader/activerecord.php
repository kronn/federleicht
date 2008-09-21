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
}
