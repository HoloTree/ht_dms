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

	function models() {
		include_once( trailingslashit( HT_DMS_UI_DIR ).'build/models.php' );

		return models::init();
	}

	function type_view( $type, $args ) {

		return $this->ui()->view_loaders()->type_view( $type, $args );

	}

	function users_groups( $obj = null, $uID = null, $oID = null, $limit = 5, $return = 'template'  ) {
		$args = array(
			'obj' 		=> $obj,
			'mine' 		=> $uID,
			'in'		=> $oID,
			'limit' 	=> $limit,
			'preview' 	=> true,
			'return'	=> $return,
		);

		return $this->models()->group( $args );

	}

	function public_groups( $obj = null, $oID = null, $limit = 5, $return = 'template'  ) {

		$args = array(
			'obj' 		=> $obj,
			'in'		=> $oID,
			'limit' 	=> $limit,
			'preview' 	=> true,
			'return'	=> $return,
		);

		return $this->models()->group( $args );

	}


	function assigned_tasks( $obj = null, $uID = null, $oID = null, $limit = 5, $return = 'template'  ) {

		$args = array(
			'obj' 		=> $obj,
			'mine' 		=> $uID,
			'in'		=> $oID,
			'limit' 	=> $limit,
			'preview' 	=> true,
			'return'	=> $return,
		);

		return $this->models()->task( $args );

	}

	function users_organizations( $obj = null, $uID = null, $limit = 5, $return = 'template'  ) {

		$args = array(
			'obj' 		=> $obj,
			'mine' 		=> $uID,
			'limit' 	=> $limit,
			'preview' 	=> true,
			'return'	=> $return,
		);

		return $this->models()->organization( $args );

	}

	function _decisions_tasks( $obj, $id, $limit = 5, $return = 'template'  ) {

		$in = array(
			'id' 	=> $id,
			'what' 	=> HT_DMS_DECISION_CPT_NAME,
			'return'	=> $return,
		);

		$args = array(
			'obj' 		=> $obj,
			'in'		=> $in,
			'limit' 	=> $limit,
			'preview' 	=> true,
			'return'	=> $return,
		);

		return $this->models()->task( $args );

	}

	function decisions_tasks( $obj = null, $id, $limit = 5, $return = 'template'  ) {
		$params[ 'where' ] = 'decision.ID = "'.$id.'"';
		$params[ 'limit'] = $limit;
		$obj = pods( HT_DMS_TASK_CT_NAME, $params );

		if ( $obj->total() > 0 ) {
			$view_loaders = holotree_dms_ui()->view_loaders();
			$view =  holotree_dms_ui()->models()->path( 'task', true  );

			return $view_loaders->magic_template( $view, $obj );

		}

		return __( 'This decision has no tasks', 'holotree' );

	}

	function organization( $obj = null, $id, $return = 'template'  ) {

		$args = array(
			'obj' 		=> $obj,
			'id'		=> $id,
			'preview' 	=> false,
			'return'	=> $return,
		);

		return $this->models()->organization( $args );
		
	}

	function group( $obj = null, $id, $return = 'template'  ) {

		$args = array(
			'obj' 		=> $obj,
			'id'		=> $id,
			'preview' 	=> false,
			'return'	=> $return,
		);

		return $this->models()->group( $args );

	}

	function decision( $obj = null, $id, $return = 'template'  ) {

		$args = array(
			'obj' 		=> $obj,
			'id'		=> $id,
			'preview' 	=> false,
			'return'	=> $return,
		);

		return $this->models()->decision( $args );

	}

	function task( $obj = null, $id, $return = 'template'  ) {

		$args = array(
			'obj' 		=> $obj,
			'id'		=> $id,
			'preview' 	=> false,
			'return'	=> $return,
		);

		return $this->models()->task( $args );

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

	function notifications() {
		return '@TODO === this:(';
	}

	/**
	 * View proposed modifications to current decision.
	 *
	 * @param 	int     		$id		ID of decision to see proposed changes of.
	 * @param 	Pods|obj|null 	$obj	Optional. Single Pods Object
	 *
	 * @return string					Decision view.
	 *
	 * @since 0.0.2
	 */
	function proposed_modifications( $id, $obj = null ) {
		$decision_class = holotree_decision_class();
		$obj = $decision_class->null_object( $obj, $id );
		if ( $decision_class->has_proposed_modification( $id, $obj, true ) ) {

			$ids = $decision_class->proposed_modifications( $id, $obj, true );

			if ( is_array( $ids ) ) {

				foreach ( $ids as $id ) {
					$pObj = holotree_decision( $id );
					$proposed_changes[ $id ] = $this->decision( $pObj, $id );
				}

				if ( isset( $proposed_changes ) && is_array( $proposed_changes ) ) {
					$proposed_changes = implode( '<br>', $proposed_changes );

					return $proposed_changes;
				}
			}
		}
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
