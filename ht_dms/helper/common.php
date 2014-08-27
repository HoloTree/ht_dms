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
		$message_text = $action = $id = false;
		$action =  pods_v( 'dms_action', 'get', false, true );
		$id = intval( pods_v( 'dms_id', 'get', false, true ) );

		//special handling for proposed changes
		if (  $action === 'change-proposed' && false !== ( $pmid = pods_v( 'pmid', 'get', false, true )  ) ) {
			if ( ! $pmid || intval( $pmid  ) === 0 ) {
				holotree_error( );
			}

			$pod = pods( HT_DMS_DECISION_CPT_NAME, $id );
			$pod->save( 'proposed_changes', $pmid  );

			$ui = holotree_dms_ui();

			$link = $ui->output_elements()->action_append( '/f', 'add-consensus', $pmid );
			$link .= '&thengo='.get_permalink( $id );

			$this->message( __( 'Proposed modification created.', 'holotree' ) );
			$this->redirect( $link, true );
			$action = false;

		}
		elseif ( $action === 'add-consensus' ) {
			holotree_consensus( $id );
			$this->redirect( pods_v( 'thengo', 'get', home_url(), true ), __( 'Adding Consensus.', 'holotree' ) );
		}
		elseif( $action === 'new' ) {
			$url = get_permalink( $id );
			$this->redirect( $url, sprintf( __( 'New %s created.', 'holotree'), 'NEED TO DEFINE TYPE' ) );
		}
		elseif ( false !== $action  ) {
			include_once( 'take_action.php' );

			$take_action = take_action::init();

		}

		if ( false !== $action && $id  && $action !== 'changing' ) {
			$output_elements = holotree_dms_ui()->output_elements();

			if ( $action === 'block' || $action === 'unblock' || $action === 'accept' || $action === 'propose-change' || $action === 'accept-change' ) {
				$message_text = $take_action->decision( $action, $id );
				if ( $action === 'propose-change' ) {
					$link = get_permalink( $id );
					$link = $output_elements->action_append( $link, 'changing', $id );

					$this->redirect( $link, sprintf( __( 'Propose a change to %s.', 'holotree'), get_the_title( $id ) ) );
				}
			}
			elseif( $action === 'join-group' || 'approve-pending' || 'reject_pending' ) {
				$message_text = $take_action->group( $action, $id );
			}
			elseif( $action === 'mark-notification' ||  'archive-notification' ) {
				$take_action->notification( $action, $id );
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
		elseif ( 'add-comment' === pods_v( 'dms_action', 'post', false, true )  && !is_null( pods_v( 'dms_id', 'post' ) ) ) {
			include_once( 'take_action.php' );
			$take_action = take_action::init();

			$take_action->comment(  pods_v( 'dms_id', 'post' ) );
		}

		$this->set_message( $message_text );


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
