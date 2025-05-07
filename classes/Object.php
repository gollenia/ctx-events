<?php

class EM_Object {
	public array $fields = [];
	/**
	 * @var array Associative array of shortname => property names for this object.
	 */
	//protected $shortnames = array();
	public array $required_fields = [];
	public string $feedback_message = "";
	var array $errors = array();
	
	protected static $context = 'object_type';
	

	


	function to_array(bool $sql_compatible = false) : array {
		$array = [];
		foreach ( $this->fields as $key => $val ) {
			if(!$sql_compatible) {
				$array[$key] = $this->$key;
				continue;
			}

			if ( !empty($this->$key) || $this->$key === 0 || $this->$key === '0' || empty($val['null']) ) {
				$array[$key] = $this->$key;
			} elseif ( $this->$key === null && !empty($val['null']) ) {
				$array[$key] = null;
			}
		}
		return $array;
	}
	

	/**
	 * Function to retreive wpdb types for all fields, or if you supply an assoc array with field names as keys it'll return an equivalent array of wpdb types
	 * @param array $array
	 * @return array:
	 */
	function get_types($array = array()){
		$types = array();
		if( count($array)>0 ){
			//So we look at assoc array and find equivalents
			foreach ($array as $key => $val){
				$types[] = $this->fields[$key]['type'];
			}
		}else{
			//Blank array, let's assume we're getting a standard list of types
			foreach ($this->fields as $field){
				$types[] = $field['type'];
			}
		}
		return apply_filters('em_object_get_types', $types, $this, $array);
	}	
	
	
	
	/**
	 * Returns an array of errors in this object
	 * @return array 
	 */
	function get_errors() {
		return $this->errors;
	}
	
	/**
	 * Adds an error to the object
	 */
	function add_error($errors){
		
		if(empty($errors)) return;

		if(!is_array($errors)) {
			$this->errors[] = $errors;
			return;
		}

		foreach($errors as $error){
			$this->errors[] = $error;
		}
	}

	function add_error_array(array $error) {
		$this->errors[] = $error;
		
	}
}