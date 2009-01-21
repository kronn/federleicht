<?php
/**
 * has_many-Ladeklasse für ActiveRecord-Klassen
 *
 * @pattern lazyload
 */
class fl_data_loader_activerecord_hasmany extends fl_data_loader_activerecord implements data_loader {
	public function __construct($class, array $keys, array $data = array(), array $options = array() ) {
		parent::__construct($class, $keys, $data, $options);
	}

	/**
	 * Interface data_loader
	 *
	 * @pattern command
	 * @return array
	 */
	public function execute() {
		$objects = array();

		$limit = isset($this->options['limit'])? $this->options['limit']: '';
		$data_table = $this->factory->get_ar_class($this->class_identifier)->get_table();

		$many_results = $this->factory->get_data_access()->retrieve(
			$data_table, '*', "$this->key_name = '$this->key_value'", 'id ASC', $limit
		);

		foreach ( $many_results as $key => $result ) {
			$ar_class = $this->factory->get_ar_class($this->class_identifier, $result['id'], $result);
			foreach ( $this->given_data as $key => $value ) {
				$ar_class->set($key, $value);
			}

			$objects[$result['id']] = $ar_class;
		}

		return $objects;
	}
	/**
	 * Interface data_loader Ende
	 */
}
