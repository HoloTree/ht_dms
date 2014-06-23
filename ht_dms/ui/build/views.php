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

	function users_groups( $obj = null, $uID = null, $in = false, $limit = 5  ) {

		return $this->model()->group( $obj, true, $in, $uID, $limit, false );

	}

	function public_groups( $obj = null, $in = false, $limit = 5 ) {

		return $this->model()->group( $obj, true, $in, false, $limit );

	}

	function assigned_tasks( $obj = null, $uID = null, $in = false, $limit = 5 ) {

		return $this->model()->task( $obj, true, $in, $uID, $limit, null );

	}

	function users_organizations( $obj = null, $uID = null, $limit = 5 ) {

		return $this->model()->organization( $obj, true, false, $uID, $limit, false );

	}

	function organization( $obj = null, $id ) {

		return $this->model()->organization( $obj, (int) $id );
		
	}

	function group( $obj = null, $id ) {

		return $this->model()->group( $obj, (int) $id );

	}

	function decision( $obj = null, $id ) {

		return $this->model()->decision( $obj, (int) $id );

	}

	function task( $obj = null, $id ) {

		return $this->model()->task( $obj, (int) $id );

	}

	function docs(  $obj = null, $which = false, $id = false ) {

		return __( 'Docs functionality not yet implemented.', 'holotree' );

	}

	function action_buttons( $obj, $id, $what ) {

		if ( $what === 'task' || $what === HT_DMS_TASK_CT_NAME ) {

			return $this->ui()->build_elements()->task_actions( $id, $obj );

		}
		else {

			return $this->ui()->build_elements()->action_buttons( $what, $id, $obj );

		}

	}

	function decisions_tasks( $tObj, $id ) {
		$in[ 'what' ] = HT_DMS_DECISION_CPT_NAME;
		$in[ 'ID' ] = $id;

		return $this->model()->task( $tObj, true, $in );

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
