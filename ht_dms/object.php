<?php
/**
 * Abstract class for loading Pods Object
 *
 * @package   @holotree_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

//namespace ht_dms;

abstract class object {

	/**
	 * The name of the Pod
	 *
	 * Must be set when extending this class using $this->set_type()
	 *
	 * @var string
	 *
	 * @since 0.0.1
	 */
	static public $type;

	/**
	 * The length to cache Pods Objects
	 *
	 * Default is 85321 seconds ~ 1 Day
	 *
	 * @var int
	 *
	 * @since 0.0.1
	 */
	static public $cache_length = 85321;

	/**
	 * Cache mode for Pods Objects
	 *
	 * object|transient|site-transient
	 * @var string
	 *
	 * @since 0.0.1
	 */
	static public $cache_mode = 'object';

	/**
	 * Set the name of the CPT
	 *
	 * @param 	string 	$type
	 *
	 * @since 0.0.1
	 */
	function set_type( $type ) {

		self::$type = $type;

	}

	/**
	 * Get value of self::$type
	 *
	 * @param 	bool 	$lower	Optional. If true, the default, value is returned in lower cases
	 *
	 * @return 	string			The name of the current CPT.
	 *
	 * @since 0.0.1
	 */
	function get_type( $lower = true ) {

		if ( $lower ) {

			return strtolower( self::$type );

		}

		return self::$type;

	}

	/**
	 * Set length to cache Pods Objects
	 *
	 * @param 	int	$length	Time in seconds to cache.
	 *
	 * @since 0.0.1
	 */
	function set_cache_length( $length ) {

		self::$cache_length = $length;

	}

	/**
	 * Set cache mode for Pods Objects
	 *
	 * @param 	string 	$type	object|transient|site-transient
	 *
	 * @since 0.0.1
	 */
	function set_cache_mode( $type ) {

		self::$cache_mode = $type;

	}


	/**
	 * Object of this CPT
	 *
	 * @param 	bool 			$cache	Optional. Whether to attempt to get cached object or not.
	 * @param 	null|array|int 	$params	Optional. Either the item ID, or pods::find() params
	 *
	 * @return 	bool|mixed|null|Pods|void
	 *
	 * @since 0.0.1
	 */
	function object( $cache = true, $params = null ) {
		$id = null;

		if ( ! is_array( $params ) && (int) $params > 0 ) {
			$id = $params;
			$params = null;
		}

		return pods( self::$type, $params );
		//return $this->get_pods_object( $id, $params, $cache );

	}

	/**
	 * Get a Pods object For ht_dms content types
	 *
	 * @param 	null 				$id
	 * @param 	null|array|int 	$params	Optional. Either the item ID, or pods::find() params.
	 * @param 	bool 			$cache	Optional. Whether to attempt to get cached object or not.
	 *
	 * @return 	bool|mixed|null|\Pods|void
	 *
	 * @since	0.0.1
	 */
	private function get_pods_object( $id = null, $params = null, $cache = true ) {

		if ( ! $cache ) {
			return self::build_pods_object( self::$type, $id, $params );
		}

		$key_group = self::key_group( self::$type, $id );
		$key = $group = null;
		if ( $key_group !== false ) {
			//sets $key and $group
			extract( $key_group );
		}
		if ( ! is_null( $key )  ) {
			$value = pods_view_get( $key, self::$cache_mode, $group );
			if ( ! $value ) {
				$value = self::build_pods_object( self::$type, $id, $params );

				pods_view_set( $key, $value, self::$cache_length, self::$cache_mode, $group );

			}

			return $value;

		}
		else {

			return self::build_pods_object( self::$type, $id, $params );

		}

	}

	/**
	 * Build Pods object
	 *
	 * @param 	string  $pod_name	Name of content type decision|group|task|notification|organization
	 * @param 	null 	$id			Optional. ID of item. Not used if params isset.
	 * @param 	null 	$params		Optional.pods::find() params
	 *
	 * @return 	bool|\Pods
	 *
	 * @since	0.0.1
	 */
	private function build_pods_object( $pod_name, $id = null, $params = null ) {
		if ( is_null( $params ) ) {
			if ( is_null( $id ) ) {
				$obj = pods( $pod_name );
			}
			else {
				$obj = pods( $pod_name, $id );
			}
		}
		else {
			$obj = pods( $pod_name, $params );
		}

		return $obj;

	}

	/**
	 * Build key group for caching
	 *
	 * @param 	string	$name	Name of content type.
	 * @param 	int		$id		ID of item.
	 *
	 * @return 	array|bool
	 *
	 * @since	0.0.1
	 */
	private function key_group( $name, $id ) {
		$prefix = apply_filters( 'ht_dms_cache_prefix', 'ht_dms_') ;
		if ( is_array( $id ) && isset( $id[0] ) ) {
			$id = intval( $id[0] );
		}

		if ( is_array( $id ) ) {
			return false;
		}

		if ( !is_null( $id ) ) {
			$key = $prefix.$name.'_'.$id;
		}
		else {
			$key = $prefix.$name;
		}

		$group = $prefix.$name;

		$key_group = array( 'key' => $key, 'group' => $group );

		return $key_group;

	}


	/**
	 * Checks that a supplied Pods object is valid and if not rebuilds it.
	 *
	 * @param 	obj|null 		$obj			Optional. Object to check.
	 * @param 	null|array|int 	$params_or_id	Optional. Either the item ID, or pods::find() params
	 *
	 * @return bool|mixed|null|Pods|void
	 *
	 * @since 0.0.1
	 */
	function null_object( $obj = null, $params_or_id = null ) {

		if ( $this->check_obj( $obj ) ) {
			if ( ! is_null( $params_or_id ) ) {
				if ( !is_array( $params_or_id ) ) {
					$params_or_id = (int) $params_or_id;
					if ( self::$type !== HT_DMS_TASK_CT_NAME ) {
						$params_or_id = array ( 'where' => 't.id = " ' . $params_or_id . ' "' );

					}
					else {
						$params_or_id = array ( 'where' => 't.term_id = " ' . $params_or_id . ' "' );
					}
				}
				$obj = $obj->find( $params_or_id );
			}

			return $obj;

		}

		else {

			$obj = $this->object( true );

			return $obj;

		}

	}

	/**
	 * Shorthand wrapper for $this->null_object
	 *
	 * @TODO REMOVE
	 *
	 */
	function null_obj( $obj = null, $params_or_id = null ) {
		return $this->null_object( $obj, $params_or_id );
	}

	/**
	 * Validates a Pods object to ensure it is the correct one to use.
	 *
	 * @param obj 		$obj	Object to check.
	 * @param int|null	$id
	 *
	 * @return bool
	 *
	 * @since 0.0.1
	 */
	function check_obj( $obj ) {
		if ( is_object( $obj ) && is_pod ( $obj ) && $obj->pod_data['name'] === self::$type ) {

				return true;

		}

	}

} 
