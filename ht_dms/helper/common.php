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
		// Loads frontend scripts and styles
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 25 );

		add_action( 'init', array( $this, 'dms_actions'), 35 );

		//on update make sure to publish item/ decisions have consensus
		add_action( "pods_api_post_save_pod_item", array( $this, 'post_edit' ), 25, 3 );

		add_action( 'ht_before_ht', array( $this, 'message' ) );

		add_action( "pods_api_post_save_pod_item", array( $this, 'update_actions' ), 99, 3 );

		$notification = HT_DMS_NOTIFICATION_NAME;
		add_filter( "pods_api_pre_save_pod_item_{$notification}", array( ht_dms_notification_class(), 'to_id' ), 5 );

		$ajax_callbacks = holotree_dms_ui()->ajax_callbacks();
		add_action( 'wp_ajax_ht_dms_reload_consensus', array( $ajax_callbacks, 'reload_consensus' ) );
		add_action( 'wp_ajax_nopriv_ht_dms_reload_consensus', '__return_false' );

		add_action( 'wp_ajax_ht_dms_notification', array( $ajax_callbacks, 'load_notification' ) );
		add_action( 'wp_ajax_nopriv_ht_dms_notification', '__return_false' );

		add_action( 'wp_ajax_ht_dms_update_decision_status', array( $ajax_callbacks, 'update_decision_status' ) );
		add_action( 'wp_ajax_nopriv_ht_dms_update_decision_status', '__return_false' );



		add_filter( 'ht_dms_paginated_views_template_output', array( holotree_dms_ui()->views(), 'after_notification_preview' ), 10, 2 );

	}


	function enqueue_scripts() {
		if ( ! is_admin() ) {
			//wp_enqueue_style( 'HoloTree-DMS', plugins_url( 'css/HT-DMS.css', __FILE__ ) );
			//wp_enqueue_script( 'HoloTree-DMS', plugins_url( 'js/HT-DMS.js', __FILE__), array( 'jquery'), false, true );
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

		wp_enqueue_script( 'pods' );
		wp_enqueue_style( 'pods-select2' );
		wp_enqueue_script( 'pods-select2' );
		wp_enqueue_style( 'pods-form' );


	}

	/**
	 * Clear view cache or the whole cache.
	 *
	 * @param bool     $view_cache
	 * @param bool $full
	 *
	 * @since 0.0.2
	 */
	function clear_dms_cache( $view_cache, $full = false ) {
		$clear = false;
		//@TODO Can we target only DMS related?
		if ( $full ) {
			pods_cache_clear();
			pods_transient_clear();
			$clear = true;
		}
		elseif( $view_cache && ! $full ) {
			foreach ( array( 'cache', 'transient') as $mode  ) {
				pods_view_clear( true, $mode, 'ht_dms_front_end_views'  );
			}
			$clear = true;
		}


		if ( $clear ) {
			/**
			 * Fires after DMS cache is cleared via this method.
			 *
			 * Note: Cache can be cleared via other means.
			 *
			 * @since 0.0.1
			 */
			do_action( 'ht_dms_post_clear_cache' );
		}

	}

	//@TODO REMOVE THIS.
	function big_delete() {
		$this->clear_dms_cache( false );
		$this->clear_dms_cache( );
		pods_cache_clear( true );

	}


	/**
	 * Callback for action that runs at top of ht content
	 *
	 * @uses $message_text
	 *
	 * @return false|string Return $message_text if it isn't false.
	 *
	 * @since 0.0.1
	 */
	function message( ) {
		//if ( $message_text !== false ) {



		//}

	}

	function set_message( $message ) {
		update_option( 'ht_dms_action_message', $message );
	}


	function dms_actions() {

		$output_elements = holotree_dms_ui()->output_elements();
		$message_text = $action = $id = false;
		$action =  pods_v( 'dms_action', 'get', false, true );
		$id = intval( pods_v( 'dms_id', 'get', false, true ) );

		if ( in_array( $action, array_keys( holotree_dms_ui()->view_loaders()->special_views() ) ) ) {
			return;
		}

		if ( 'add-comment' === pods_v( 'dms_action', 'post', false, true )  && ! is_null( pods_v( 'dms_id', 'post' ) ) ) {
			include_once( 'take_action.php' );
			$take_action = take_action::init();

			$take_action->comment(  pods_v( 'dms_id', 'post' ) );
		}

		if ( ! $action || $action === 'propose-change' || $action === 'changing' ) {
			return;
		}

		if( $action == 'accept-change' ) {

			$id = holotree_decision_class()->make_modification( $id );
			holotree_consensus_class()->create( $id );
			pods_redirect( get_permalink( $id ) );
			return;
		}

		//special handling for proposed changes
		if (  $action === 'change-proposed' && false !== ( $pmid = pods_v( 'pmid', 'get', false, true )  ) ) {
			if ( ! $pmid || intval( $pmid  ) === 0 ) {
				holotree_error( );
			}

			$pod = pods( HT_DMS_DECISION_CPT_NAME, $id );
			$pod->save( 'proposed_changes', $pmid  );


			holotree_consensus( $pmid );

			$link = get_permalink( $id );
			pods_redirect( $link );

			return;

		}

		if ( $action === 'add-consensus' ) {
			holotree_consensus( $id );
			$this->redirect( pods_v( 'thengo', 'get', ht_dms_home(), true ), __( 'Adding Consensus.', 'holotree' ) );
		}
		elseif( $action === 'new' ) {
			$url = get_permalink( $id );
			if ( pods_v( 'task' ) ) {
				$url = get_term_link( $id, HT_DMS_TASK_CT_NAME );
			}

			$this->redirect( $url, sprintf( __( 'New %s created.', 'holotree'), 'NEED TO DEFINE TYPE' ) );
			return;
		}

		//end special actions

		if ( false !== $action  ) {
			include_once( 'take_action.php' );

			$take_action = take_action::init();

		}

		if ( false !== $action && $id && $action !== 'changing' ) {

			if ( in_array( $action, array( 'join-group', 'approve-pending', 'reject-pending' ) ) ) {
				$take_action->group( $action, $id );
				return;
			}

			if ( $action === 'block' || $action === 'unblock' || $action === 'accept' || $action === 'propose-change' || $action === 'accept-change' || 'respond' ) {
				return;

				//$message_text = $take_action->decision( $action, $id );
				if ( $action === 'propose-change' ) {
					$link = get_permalink( $id );
					$link = $output_elements->action_append( $link, 'changing', $id );

					$this->redirect( $link, sprintf( __( 'Propose a change to %s.', 'holotree'), get_the_title( $id ) ) );
					return;
				}else {
					if ( $action === 'respond' ) {
						//handled in view loader
						return;
					}
					$take_action->decision( $action, $id );
				}


			}
			elseif( $action === 'join-group' || 'approve-pending' || 'reject_pending' ) {

				$message_text = $take_action->group( $action, $id );
				return;
			}
			elseif( $action === 'mark-notification' ||  'archive-notification' ) {
				$take_action->notification( $action, $id );
				return;
			}
			elseif ( $action === 'clear-dms-cache' ) {
				$this->clear_dms_cache( null, false );
			}
			elseif ( $action === 'task-updated' ) {
				$message_text = __( 'Task Updated.', 'holotree' );
			}
			elseif( $action === 'change-proposed' ) {
				$message_text = __( 'Change Proposed', 'holotree' );
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




		$this->set_message( $message_text );

		$link = get_permalink( $id );
		if ( ! $link ) {
			$link = get_term_link( $id );
			if ( ! $link ) {
				$link = ht_dms_home();
			}
		}

		$this->redirect( $link );

	}

	/**
	 * Redirect with option to flag for alert output
	 *
	 * @param string    $destination 	URL to redirect to.
	 * @param bool 		$alert			Option to add get var to trigger alert message.
	 *
	 * @since 0.0.2
	 */
	function redirect( $destination, $message = false ) {
		if ( $message  ) {
			$destination = holotree_dms_ui()->output_elements()->action_append( $destination, array ( 'var' => 'dms_message', 'value' => true ) );
		}
		pods_redirect( $destination );
		
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
		if  ( $is_new_item && isset( $pieces['params']->pod ) && ( $pieces['params']->pod === HT_DMS_DECISION_CPT_NAME || $pieces['params']->pod === HT_DMS_GROUP_CPT_NAME  ) )  {

			$new_post = array ();
			$new_post[ 'ID' ] = $id;
			$new_post[ 'post_status' ] = 'publish';
			wp_update_post( $new_post );


		}

		if ( 'changing' !== pods_v( 'dms_action', 'get', false, true ) ) {

		}
		elseif (  ! isset( $_GET[ 'pmid '] ) ||  ! isset( $_GET[ 'thengo' ]  ) ) {

			if ( is_object( $pieces[ 'params' ] ) && isset( $pieces[ 'params' ]->pod ) && $pieces[ 'params' ]->pod === HT_DMS_DECISION_CPT_NAME ) {

				holotree_consensus( $id );

			}
		}

		add_action( "pods_api_post_save_pod_item", array( $this, 'post_edit' ), 25, 3 );

		return $pieces;

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

	/**
	 * Check if a user exists
	 * @param $id
	 *
	 * @return bool
	 */
	function user_exists( $uID ) {
		if ( get_user_by( 'id', $uID )  ) {
			return true;

		}

	}

	/**
	 * Pass null or user ID, returns same ID or current user ID if null
	 *
	 * @param null $uID
	 *
	 * @return int|null
	 */
	function null_user( $uID = null ) {

		if ( is_null( $uID ) ) {
			$uID = get_current_user_id();

		}

		return $uID;

	}

	/**
	 * Creates the new/update {$type} actions
	 *
	 *
	 * @uses pods_api_post_save_pod_item action
	 *
	 * @param $pieces
	 * @param $new
	 * @param $id
	 *
	 * @return array
	 *
	 * @since 0.0.3
	 */
	function update_actions( $pieces, $new, $id ) {

		$data = $dID = $gID = $oID = null;
		foreach( $pieces[ 'fields_active' ] as $field ) {
			$data[ $id ][ $field ] =  $pieces[ 'fields' ][ $field][ 'value'];
		}

		$type = $pieces['params']->pod;

		if ( $type === HT_DMS_TASK_CT_NAME ) {
			if ( isset ( $data[ $id ][ 'decision' ] ) ) {
				$dID = reset( $data[ $id ][ 'decision' ] );
				$data[ $id ][ 'decision' ] = $dID;
			}
		}

		if ( $type === HT_DMS_GROUP_CPT_NAME ) {
			$gID = $id;
		}
		else {
			if ( isset ( $data[ $id ][ 'group' ] ) ) {
				$gID = reset( $data[ $id ][ 'group' ] );
				$data[ $id ][ 'group' ] = $gID;
			}
		}

		if ( $type === HT_DMS_ORGANIZATION_NAME ) {
			$oID = $id;
		}
		else {
			if ( isset ( $data[ $id ][ 'organization' ] ) ) {
				$oID                           = reset( $data[ $id ][ 'organization' ] );
				$data[ $id ][ 'organization' ] = $oID;
			}
		}

		do_action( 'ht_dms_update', $id, $type, $new, $data, $gID, $oID );

		if ( $new ) {
			do_action( "ht_dms_new_{$type}", $id, $data, $gID, $oID );
		}else {
			do_action( "ht_dms_update_{$type}", $id, $data, $gID, $oID );
		}


		return $pieces;

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

}
