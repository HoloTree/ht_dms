<?php
/**
 * @TODO What this does.
 *
 * @package   @TODO
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\ui\build;

class views {

	function model() {
		include_once( trailingslashit( HT_DMS_UI_DIR ).'build/models.php' );

		return models::init();
	}

	function users_groups( $obj = null, $uID = null, $in = false, $preview = true, $limit = 5  ) {

		return $this->model()->group( $obj, $preview, $in, $uID, $limit, false );

	}

	function public_groups( $obj = null, $in = false, $preview = true, $limit = 5 ) {

		return $this->model( $obj, $preview, $in, false, $limit );

	}

	function assigned_tasks( $obj = null, $uID = null, $in = false, $preview = true , $limit = 5 ) {

		return $this->model()->task( $obj, $preview, $in, $uID, $limit, null );

	}

	function users_organizations( $obj = null, $oID, $uID = null, $preview = true, $limit = 5 ) {

		return $this->model()->organization( $obj, $preview, $oID, $uID, $limit, false );

	}

	/**
	 * Holds the instance of this class.
	 *
	 * @since  0.0.1
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

	/**
	 * Get instance of UI class
	 *
	 * @return 	\holotree\ui
	 *
	 * @since 	0.0.1
	 */
	function ui(){
		$ui = holotree_dms_ui();

		return $ui;

	}
} 
