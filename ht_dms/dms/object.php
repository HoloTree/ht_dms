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

namespace ht_dms\dms;

abstract class object {


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
	 * cache|transient|site-transient
	 * @var string
	 *
	 * @since 0.0.1
	 */
	static public $cache_mode = 'cache';

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

		$type = $this->set_type();

		if ( $lower ) {

			$type = strtolower( $type );

		}

		return $type;

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
	 * @param 	bool 			$cache	Optional. Whether to use cache or not.
	 * @param 	null|array|int 	$params	Optional. Either the item ID, or pods::find() params
	 *
	 * @return 	bool|mixed|null|\pods|void
	 *
	 * @since 0.0.1
	 */
	function object( $cache = true, $params_or_id = null ) {

		if ( is_int( $params_or_id ) || intval( $params_or_id ) > 1 || !is_array( $params_or_id ) ) {
			if ( $this->get_type() !== HT_DMS_TASK_POD_NAME ) {
				$params[ 'where' ] = 't.id = "' . $params_or_id . '"';
			}
			else {
				$params[ 'where' ] = 't.term_id = "' . $params_or_id . '"';
			}

		}
		else {
			$params = $params_or_id;
		}

		if ( $cache ) {
			$params[ 'cache_mode' ] = self::$cache_mode;
			$params[ 'expires' ] 	= self::$cache_length;
		}


		$obj = pods( $this->get_type(), $params );

		if ( $this->check_obj( $obj ) )  {

			return $obj;

		}
		else {
			ht_dms_error();
		}


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

		if ( ! $this->check_obj( $obj ) ) {

			$obj = $this->object( true, $params_or_id );

		}

		return $obj;

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
		if ( is_object( $obj ) && is_pod ( $obj ) && $obj->pod_data['name'] === $this->get_type() ) {

				return true;

		}

	}

} 
