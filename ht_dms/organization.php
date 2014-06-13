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
