<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Base class for models
 *
 * @author Max Tsiplyakov <makstsiplyakov@gmail.com>
 */
class PMAE_Model_Record extends PMXE_Model {
	/**
	 * Initialize model
	 * @param array[optional] $data Array of record data to initialize object with
	 */
	public function __construct($data = array()) {
		parent::__construct();
		if (! is_array($data)) {
			// Translators: %1$s is the class name, %2$s is the method name.
			throw new Exception(sprintf(esc_html__('Array expected as parameter for %1$s::%2$s', 'wp-all-export-csv-excel-xml-for-acf'),
				esc_html(get_class($this)),
				esc_html(__METHOD__)
			));
		}
		$data and $this->set($data);
	}
	
	/**
	 * @see PMAE_Model::getBy()
	 * @return PMAE_Model_Record
	 */
	public function getBy($field = NULL, $value = NULL) {
		if (is_null($field)) {
			// Translators: %1$s is the class name, %2$s is the method name.
			throw new Exception(sprintf(esc_html__('Field parameter is expected at %1$s::%2$s', 'wp-all-export-csv-excel-xml-for-acf'),
				esc_html(get_class($this)),
				esc_html(__METHOD__)
			));
		}
		$sql = "SELECT * FROM $this->table WHERE " . $this->buildWhere($field, $value);
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- buildWhere handles escaping
		$result = $this->wpdb->get_row($sql, ARRAY_A);
		if (is_array($result)) {
			foreach ($result as $k => $v) {
				if (is_serialized($v)) {
					$result[$k] = maybe_unserialize($v);
				}
			}
			$this->exchangeArray($result);
		} else {
			$this->clear();
		}
		return $this;
	}
	

	
	/**
	 * Saves currently set object data as database record
	 * @return PMAE_Model_Record
	 */
	public function insert() {
		if ($this->wpdb->insert($this->table, $this->toArray(TRUE))) {
			if (isset($this->auto_increment)) {
				$this[$this->primary[0]] = $this->wpdb->insert_id;
			}
			return $this;
		} else {
			throw new Exception(esc_html($this->wpdb->last_error));
		}
	}
	/**
	 * Update record in database
	 * @return PMAE_Model_Record
	 */
	public function update() {
		$record = $this->toArray(TRUE);
		$this->wpdb->update($this->table, $record, array_intersect_key($record, array_flip($this->primary)));
		if ($this->wpdb->last_error) {
			throw new Exception(esc_html($this->wpdb->last_error));
		}
		return $this;
	}
	
	/**
	 * Delete record form database
	 * @return PMAE_Model_Record
	 */
	public function delete() {
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- buildWhere handles escaping
		if ($this->wpdb->query("DELETE FROM $this->table WHERE " . $this->buildWhere(array_intersect_key($this->toArray(TRUE), array_flip($this->primary))))) {
			return $this;
		} else {
			throw new Exception(esc_html($this->wpdb->last_error));
		}
	}
	/**
	 * Insert or Update the record
	 * WARNING: function doesn't check actual record presents in database, it simply tries to insert if no primary key specified and update otherwise
	 * @return PMAE_Model_Record
	 */
	public function save() {
		if (array_intersect_key($this->toArray(TRUE), array_flip($this->primary))) {
			$this->update();
		} else {
			$this->insert();
		}
		return $this;
	}
	
	/**
	 * Set record data
	 * When 1st parameter is an array, it expected to be an associative array of field => value pairs 
	 * If 2 parameters are set, first one is expected to be a field name and second - it's value
	 * 
	 * @param string|array $field
	 * @param mixed[optional] $value
	 * @return PMAE_Model_Record
	 */
	public function set($field, $value = NULL) {
		if (is_array($field) and ( ! is_null($value) or 0 == count($field))) {
			// Translators: %1$s is the class name.
			throw new Exception(sprintf(esc_html__('%1$s::set method expects either a non-empty associative array as the only parameter or a field name and its value as two separate parameters.', 'wp-all-export-csv-excel-xml-for-acf'),
				esc_html(__CLASS__)
			));
		}
		if (is_array($field)) {
			$this->exchangeArray(array_merge($this->toArray(), $field));
		} else {
			$this[$field] = $value;
		}
		
		return $this;
	}
	
	/**
	 * Magic method to resolved object-like request to record values in format $obj->%FIELD_NAME%
	 * @param string $field
	 * @return mixed
	 */
	public function __get($field) {
		if ( ! $this->offsetExists($field)) {
			// Translators: %s is the field name.
			throw new Exception(sprintf(esc_html__('Undefined field %s.', 'wp-all-export-csv-excel-xml-for-acf'), esc_html($field)));
		}
		return $this[$field];
	}
	/**
	 * Magic method to assign values to record fields in format $obj->%FIELD_NAME = value
	 * @param string $field
	 * @param mixed $value
	 */
	public function __set($field, $value) {
		$this[$field] = $value;
	}
	/**
	 * Magic method to check wether some record fields are set
	 * @param string $field
	 * @return bool
	 */
	public function __isset($field) {
		return $this->offsetExists($field);
	}
	/**
	 * Magic method to unset record fields
	 * @param string $field
	 */
	public function __unset($field) {
		$this->offsetUnset($field);
	}
	
}