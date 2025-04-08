<?php

namespace Contexis\Events\Utilities;

class SQLHelper {
	public static function build_sql_orderby( $args, $accepted_fields, $default_order = 'ASC' ){
		//First, ORDER BY
		$args = apply_filters('em_object_build_sql_orderby_args', $args, $accepted_fields, $default_order );
		$orderby = self::build_sql_x_by_helper($args['orderby'], $args['order'], $accepted_fields, $default_order);
		return apply_filters('em_object_build_sql_orderby', $orderby, $args, $accepted_fields, $default_order );
	}
	
	/**
	 * Helper for building arrays of fields 
	 * @param mixed $x_by_field
	 * @param mixed $order
	 * @param mixed $accepted_fields
	 * @param string $default_order
	 * @return array
	 */
	protected static function build_sql_x_by_helper($x_by_field, $order, $accepted_fields, $default_order = 'ASC' ){
		$x_by = array();
		if(is_array($x_by_field)){
			//Clean orderby array so we only have accepted values
			foreach( $x_by_field as $key => $field ){
				if( array_key_exists($field, $accepted_fields) ){
					//maybe cases we're given an array where keys are shortcut names e.g. id => event_id - this way will be deprecated at one point
					$x_by[] = $accepted_fields[$field];
				}elseif( in_array($field,$accepted_fields) ){
					$x_by[] = $field;
				}else{
					unset($x_by[$key]);
				}
			}
		}elseif( $x_by_field != '' && array_key_exists($x_by_field, $accepted_fields) ){
			$x_by[] = $accepted_fields[$x_by_field];
		}elseif( $x_by_field != '' && in_array($x_by_field, $accepted_fields) ){
			$x_by[] = $x_by_field;
		}
		//ORDER
		if( $order !== false ){
			foreach($x_by as $i => $field){
				$x_by[$i] .= ' ';
				if(is_array($order)){
					//If order is an array, we'll go through the orderby array and match the order values (in order of array) with orderby values
					if( in_array($order[$i], array('ASC','DESC','asc','desc')) ){
						$x_by[$i] .= $order[$i];
					}else{
						//If orders don't match up, or it's not ASC/DESC, the default events search in EM settings/options page will be used.
						$x_by[$i] .= $default_order;
					}
				}else{
					$x_by[$i] .= ( in_array($order, array('ASC','DESC','asc','desc')) ) ? $order : $default_order;
				}
			}
		}
		return $x_by;
	}
}