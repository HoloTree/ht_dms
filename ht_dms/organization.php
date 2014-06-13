<?php
/**
 * HoloTree DMS Organization Management
 *
 * @package   @holotree_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms;


class organization extends dms {

	function __construct() {

	}



	/**
	 * Loop to get values of all fields in CPT.
	 *
	 * Implement in inherited class.
	 *
	 * @param 	int|null 	$id
	 * @param 	obj		$obj
	 * @param	bool	$all	Optional. Whether to to return all fields or selected fields.
	 *
	 * @return 	bool
	 *
	 * @since 	0.0.1
	 */
	function field_loop( $id = null, $obj ) {
		if ( is_null( $id ) ) {
			if ( $obj->total() > 0 ) {
				while ( $obj->fetch() ) {
					$fields = $obj->fields();
					foreach ( $fields as $key => $value ) {
						$organization[ $key ] = $obj->field( $key );
						$organizations[ $obj->id() ] = $organization;
					}
				}

				return $organizations;

			}
		}
		else {
			$fields = $this->fields_to_loop( $obj, true );
			$fields[ 'ID' ] = null;
			$fields[ 'id' ] = null;
			$fields[ 'post_title' ] = null;
			$fields[ 'post_author' ] = null;
			foreach ( $fields as $key => $value ) {
				$organization[ $key ] = $obj->field( $key );
			}

			if ( !is_null( $id ) ) {
				$organizations = array();
				$organizations[ $obj->id() ] = $organization;
				return $organizations;
			}
			else {

				return $organization;

			}

		}

	}

	/**
	 * Set which Pod for this class
	 *
	 * @return string
	 *
	 * @since  0.0.1
	 */
	function set_type() {

		return HT_DMS_ORGANIZATION_NAME;

	}

	/**
	 * Holds the instance of this class.
	 *
	 *
	 * @access private
	 * @var    object
	 */
	private static $instance;


	/**
	 * Returns the instance.
	 *
	 * @since  0.0.1
	 * @access public
	 * @return object
	 */
	public static function init() {

		if ( !self::$instance )
			self::$instance = new self;

		return self::$instance;

	}

} 
