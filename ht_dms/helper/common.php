<?php
/**
 * Common tools for HoloTree DMS
 *
 * Was main DMS class in old setup.
 *
 * @TODO LOOSE THIS CLASS?
 *
 * @package   @holotree_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\helper;

class common {

	function __construct() {
		add_action( 'plugins_loaded', array( $this, 'dms' ) );
		// Loads frontend scripts and styles
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		//reset transients on group or decison update
		//add_action( 'pods_api_post_save_pod_item_decision', array( $this, 'reset_cache'), 11, 3 );
		//add_action( 'pods_api_post_save_pod_item_group', array( $this, 'reset_cache'), 11, 3 );

		add_action( 'init', array( $this, 'dms_actions'), 35 );
		add_action( 'init', array( $this, 'redirect'), 30 );

		//on update make sure to publish item/ decisions have consensus
		add_action( "pods_api_post_save_pod_item", array( $this, 'post_edit' ), 25, 3 );

		add_action( 'ht_before_ht', array( $this, 'message' ) );

	}
	/**
	 * Initializes the Holo_Tree_DMS() class
	 *
	 * Checks for an existing Holo_Tree_DMS() instance
	 * and if it doesn't find one, creates it.
	 */
	public static function init() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new dms();
		}

		return $instance;
	}

	function enqueue_scripts() {
		if ( !is_admin() ) {
			wp_enqueue_style( 'HoloTree-DMS', plugins_url( 'css/HT-DMS.css', __FILE__ ) );
			wp_enqueue_script( 'HoloTree-DMS', plugins_url( 'js/HT-DMS.js', __FILE__), array( 'jquery'), false, true );
			wp_enqueue_script( 'jquery' );
			$var = (string) HT_FOUNDATION;
			wp_localize_script( 'HoloTree-DMS', 'htFoundation', $var );

			if ( HT_FOUNDATION === false ) {
				$jquery_ui_components = array ( 'jquery-ui-core', 'jquery-ui-accordion', 'jquery-ui-tabs' );

				$jquery_ui_components = apply_filters( 'ht_dms_jquery_ui_components', $jquery_ui_components );


				foreach ( $jquery_ui_components as $componet ) {
					wp_enqueue_script( $componet );
				}
			}
		}

	}

	function clear_dms_cache( $object = true ) {
		$cache_groups = array(
			'decision',
			'group',
			'task',
		);
		holotree_cache_clear( $name, null, $object );

	}

	//@TODO REMOVE THIS.
	function big_delete() {
		$this->clear_dms_cache( false );
		$this->clear_dms_cache( );
		pods_cache_clear( true );

	}

	/**
	 * A message to be outputted before dms content
	 *
	 * @var bool|string
	 *
	 * @since 0.0.1
	 */
	static $message_text = false;

	/**
	 * Callback for action that runs at top of ht content
	 *
	 * @uses self::$message_text
	 *
	 * @return false|string Return self::$message_text if it isn't false.
	 *
	 * @since 0.0.1
	 */
	function message( ) {
		//if ( self::$message_text !== false ) {
			return holotree_dms_ui()->elements()->alert( self::$message_text, 'success' );

		//}

	}


	function dms_actions() {
		$action = $id = false;
		$action =  pods_v( 'dms_action', 'get', false, true );
		$id = intval( pods_v( 'dms_id', 'get', false, true ) );

		if ( false !== $action  ) {
			include_once( 'take_action.php' );

			$take_action = take_action::init();

		}

		if ( false !== $action && $id ) {


			if ( $action === 'block' || $action === 'unblock' || $action === 'accept' || $action === 'propose-change' || $action === 'accept-change' ) {
				self::$message_text = $take_action->decision( $action, $id );
			}
			elseif( $action === 'join-group' || 'approve-pending' || 'reject_pending' ) {
				self::$message_text = $take_action->group( $action, $id );
			}
			elseif( $action === 'mark-notification' ||  'archive-notification' ) {
				$take_action->notification( $action, $id );
			}
			elseif ( $action === 'clear-dms-cache' ) {
				$this->big_delete();
			}
			elseif ( $action === 'task-updated' ) {
				self::$message_text = __( 'Task Updated.', 'holotree' );
			}
			elseif( $action === 'change-proposed' ) {
				self::$message_text = __( 'Change Proposed', 'holotree' );
			}
			elseif ( $action === 'add-blocker' || 'add-blocking' || 'completed' ) {
				//@todo still needed?
			}
			elseif( in_array( $action, $take_action->take_no_action()  ) ) {
				//don't do anything.
			}
			else {
				holotree_error('dms_actions error', print_c3( array( $id, $action ) ) );
			}
		}
		elseif ( 'add-comment' === pods_v( 'dms_action', 'post', false, true )  && !is_null( pods_v( 'dms_id', 'post' ) ) ) {
			include_once( 'take_action.php' );
			$take_action = take_action::init();

			$take_action->comment(  pods_v( 'dms_id', 'post' ) );
		}


	}



	function redirect() {

		$change = pods_v( 'dms_action', 'get' );
		if ( $change === 'propose-change' ) {
			$id = intval( pods_v( 'dms_id', 'get' ) );
			wp_redirect( get_permalink( $id ) .'&dms_action=changing'  );
			exit;
		}

	}

	function notification( $uID, $message, $subject, $email = true ) {
		if ( $this->user_exists( $uID ) ) {
			$notification_pref = get_user_meta( $uID, 'notification_prefrence' );
			if ( $email === true && in_array( 'email', $notification_pref ) ) {
				$user = get_userdata( $uID );
				wp_mail( $user->user_email, $subject, $message );
			}
			//add dashboard message as well.
		}
	}

	/**
	 * After a new decision or group is created set its status to publish and if is a decision create consensus. On any save ensure decision has a consensus.
	 *
	 * @param $pieces
	 *
	 * @uses 'pods_api_post_save_pod_item' hook
	 *
	 * @return mixed
	 *
	 * @since 0.0.1
	 */
	function post_edit( $pieces, $is_new_item, $id ) {
		remove_action( "pods_api_post_save_pod_item", array( $this, 'post_edit' ) );
		if  ( $is_new_item && ( $pieces['params']->pod === HT_DMS_DECISION_CPT_NAME || $pieces['params']->pod === HT_DMS_GROUP_CPT_NAME  ) )  {

			$new_post = array ();
			$new_post[ 'ID' ] = $id;
			$new_post[ 'post_status' ] = 'publish';
			wp_update_post( $new_post );


		}

		if ( $pieces['params']->pod === HT_DMS_DECISION_CPT_NAME ) {

			holotree_consensus( $id );

		}

		add_action( "pods_api_post_save_pod_item", array( $this, 'post_edit' ), 25, 3 );

	}

	/**
	 * Add consensus to decision if it does not exist
	 *
	 * @param	$dID ID of decision.
	 *
	 * @since	0.0.1
	 */
	function add_consensus(  $dID   ) {


		holotree_consensus( $dID );


	}

	/**
	 * Set group or decision facilitator.
	 *
	 * Note: While $dID and $gID are both optional args. One must be set.
	 *
	 * @TODO Better handing of both $dID and $gID being set.
	 *
	 * @param 	int|null 	$uID 	Optional. User to add or remove from facilitators. Defaults to current user.
	 * @param 	int|null	$dID 	Optional. Decision to add facilitator to.
	 * @param 	int|null	$gID 	Optional. Group to add faciliator to.
	 * @param 	bool		$add 	If true facilitator is added, if false is removed.
	 *
	 * @returns int			$id		ID of decison/ group updated
	 *
	 * @since	0.0.1
	 */
	function set_facilitator( $uID = null, $gID = null, $dID = null, $add = true ) {
		if ( is_null( $gID ) && is_null( $dID) ) {
			holotree_error( 'You must specify group or decisionID in', __METHOD__ );
			return false;
		}
		else {
			$uID = $this->null_user( $uID );
			if ( $this->user_exists( $uID ) ) {
				if ( !is_null( $gID ) ) {
					$pod_name = HT_DMS_GROUP_CPT_NAME;
					$item = $gID;
					$class = HoloTree_DMS_Group::init();
				}
				elseif ( !is_null( $dID ) ) {
					$pod_name = HT_DMS_DECISION_CPT_NAME;
					$item = $dID;
					$class = HoloTree_DMS_Decision::init();
				}
				else {
					//??
				}

				$obj = pods( $pod_name, $item );
				if ( $add ) {
					$facilitators = $obj->field( 'facilitators.ID' );
					$facilitators[] = $uID;
					$id = $obj->save( 'facilitators', $facilitators );
					if ( is_int( $id ) ) {
						$class->reset_cache( $id );
						return $id;
					}
				}
				else {
					$facilitators = $obj->field( 'facilitators.ID' );
					if ( is_array( $facilitators ) ) {
						if ( ( $key = array_search( $uID, $facilitators ) ) !== FALSE ) {
							unset( $facilitators[ $key ] );
						}
						$id = $obj->save( 'facilitators', $facilitators );
						if ( is_int( $id ) ) {
							$class->reset_cache( $id );
							return $id;
						}
					}
				}



			}
		}
	}

	/**
	 * Check if a user is facilitator for a given decision or group.
	 *
	 *  Note: While $dID and $gID are both optional args. One must be set.
	 *
	 * @param 	int|null 	$uID 	Optional. User to cechk. Defaults to current user.
	 * @param 	int|null	$dID 	Optional. Decision to check in.
	 * @param 	int|null	$gID 	Optional. Group to to check in.
	 *
	 * @return 	bool				True if user is facilitator, false if not
	 *
	 * @since 	0.0.1
	 */
	function is_facilitator( $uID = null, $gID = null, $dID = null ) {
		$uID = $this->null_user( $uID );

		if ( $this->user_exists( $uID ) ) {
			if ( is_null( $gID ) && is_null( $dID ) ) {
				holotree_error( __METHOD__ . 'Requires that either group or decision ID be specified.' );
			}

			if ( !is_null( $gID ) ) {
				$pod_name = HT_DMS_GROUP_CPT_NAME;
				$item = $gID;
			}
			elseif ( !is_null( $dID ) ) {
				$pod_name = HT_DMS_DECISION_CPT_NAME;
				$item = $dID;

			}
			else {
				//??
			}
			$obj = pods( $pod_name, $item );
			$facilitators = $obj->field( 'facilitators.ID' );
			if ( is_array( $facilitators ) ) {
				if ( in_array( $uID, $facilitators ) ) {
					return true;
				}

			}

		}

	}

	function user_exists( $id ) {
		if ( get_user_by( 'id', $id ) !== false ) {
			return true;

		}

	}

}