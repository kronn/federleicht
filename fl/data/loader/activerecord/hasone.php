<?php
/**
 * has_one-Ladeverhalten für ActiveRecord-Klassen
 */
class fl_data_loader_activerecord_hasone extends fl_data_loader_activerecord implements data_loader {
	public function __construct($class, array $keys, array $data = array(), array $options = array() ) {
		parent::__construct($class, $keys, $data, $options);
	}

	/**
	 * Interface data_loader
	 *
	 * @pattern command
	 */
	public function execute() {
		$ar_class = $this->factory->get_ar_class($this->class_identifier, $this->key_value);
		foreach ( $this->given_data as $key => $value ) {
			$ar_class->set($key, $value);
		}

		return $ar_class;
	}
	/**
	 * Interface data_loader Ende
	 */
}
