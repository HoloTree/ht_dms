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

class common  {

	function __construct() {
		add_action( 'init', array( $this, 'font_awesome' ) );
		add_action( 'init', array( $this, 'handlebars' ) );
		add_action( 'init', array( $this, 'dms_actions' ), 35 );
		add_action( 'pods_api_post_save_pod_item', array( $this,'post_edit' ),  25, 3 );
		//add_action( 'ht_dms_before_output', array( $this, 'message' ) );

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
		if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return;
		}


		$action =  pods_v_sanitized( 'dms_action', 'get', false, true );
		$id = intval( pods_v_sanitized( 'dms_id', 'get', false, true ) );

		if ( in_array( $action, array_keys( ht_dms_ui()->view_loaders()->special_views() ) ) ) {
			return;
		}

		if ( $action == 'new' && $id ) {
			pods_redirect( get_permalink( $id ) );
		}

		if ( 'add-comment' === pods_v( 'dms_action', 'post', false, true )  && ! is_null( pods_v( 'dms_id', 'post' ) ) ) {
			if ( ! wp_verify_nonce( pods_v_sanitized( 'ht_dms_nonce', 'post' ), 'ht_dms_comment_nonce' ) ) {
				return false;
			}

			$content = pods_v_sanitized( 'dms_comment_text', 'post', false, true );
			if ( false !== $content ) {
				$data = array (
					'comment_post_ID'  => $id,
					'comment_content'  => $content,
					'user_id'          => get_current_user_id(),
					'comment_approved' => 1,
				);
				wp_insert_comment( $data );
			}

			return;

		}

		if ( !isset( $_GET ) || empty( $_GET ) ) {
			return;
		}

		if ( ! $action || $action === 'propose-change' || $action === 'changing' ) {
			return;
		}

		if ( $action == 'accept-invite' && ! is_null( $uID = pods_v_sanitized( 'user' ) ) && ! is_null( $type = ( pods_v_sanitized( 'type' ) ) ) ) {
			$group = true;
			if ( $type === 'organization' ) {
				$group = false;
			}

			ht_dms_membership_class()->accept_internal_invite( $id, $uID, null, $group );
			pods_redirect( get_permalink( $id ) );
		}

		$verify = ht_dms_verify_action_nonce();
		if ( ! $verify ) {
			ht_dms_error( __( 'Alert! Alert! Security Check Failed!', 'ht_dms' ) );
		}

		//special handling for proposed changes
		if (  $action === 'change-proposed' && false !== ( $pmid = pods_v( 'pmid', 'get', false, true )  ) ) {
			if ( ! $pmid || intval( $pmid  ) === 0 ) {
				ht_dms_error( );
			}

			$pod = pods( HT_DMS_DECISION_POD_NAME, $id );
			$pod->save( 'proposed_changes', $pmid  );


			ht_dms_consensus( $pmid );

			$link = get_permalink( $id );
			pods_redirect( $link );

			return;

		}


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
			$destination = ht_dms_ui()->output_elements()->action_append( $destination, array ( 'var' => 'dms_message', 'value' => true ) );
		}
		pods_redirect( $destination );
		
	}


	/**
	 * After a new decision or group is created set its status to publish and if is a decision create consensus_ui. On any save ensure decision has a consensus_ui.
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
		if  ( $is_new_item && isset( $pieces['params']->pod ) && ( $pieces['params']->pod === HT_DMS_DECISION_POD_NAME || $pieces['params']->pod === HT_DMS_GROUP_POD_NAME  ) )  {

			$new_post = array ();
			$new_post[ 'ID' ] = $id;
			$new_post[ 'post_status' ] = 'publish';
			wp_update_post( $new_post );


		}

		if ( 'changing' !== pods_v( 'dms_action', 'get', false, true ) ) {

		}
		elseif (  ! isset( $_GET[ 'pmid '] ) ||  ! isset( $_GET[ 'thengo' ]  ) ) {

			if ( is_object( $pieces[ 'params' ] ) && isset( $pieces[ 'params' ]->pod ) && $pieces[ 'params' ]->pod === HT_DMS_DECISION_POD_NAME ) {

				ht_dms_consensus( $id );

			}
		}

		add_action( "pods_api_post_save_pod_item", array( $this, 'post_edit' ), 25, 3 );

		return $pieces;

	}

	/**
	 * Add consensus_ui to decision if it does not exist
	 *
	 * @param	$dID ID of decision.
	 *
	 * @since	0.0.1
	 */
	function add_consensus(  $dID   ) {


		ht_dms_consensus( $dID );


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
			ht_dms_error( 'You must specify group or decisionID in', __METHOD__ );
			return false;
		}
		else {
			$uID = $this->null_user( $uID );
			if ( $this->user_exists( $uID ) ) {
				if ( !is_null( $gID ) ) {
					$pod_name = HT_DMS_GROUP_POD_NAME;
					$item = $gID;
					$class = ht_dms_group_class();
				}
				elseif ( !is_null( $dID ) ) {
					$pod_name = HT_DMS_DECISION_POD_NAME;
					$item = $dID;
					$class = ht_dms_decision_class();
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
				ht_dms_error( __METHOD__ . 'Requires that either group or decision ID be specified.' );
			}

			if ( !is_null( $gID ) ) {
				$pod_name = HT_DMS_GROUP_POD_NAME;
				$item = $gID;
			}
			elseif ( !is_null( $dID ) ) {
				$pod_name = HT_DMS_DECISION_POD_NAME;
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
	 * Convert null value for user ID to current user ID.
	 *
	 * @param 	int|null $uID	Optional. A user ID.
	 *
	 * @return 	int				Same as input or current user ID if input is null.
	 *
	 * @since 	0.0.1
	 */
	function null_user( $uID ) {

		return ht_dms_null_user( $uID );

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

			if ( isset( $pieces[ 'fields' ][ $field ][ 'value' ]) ) {
				$datum                 = $pieces[ 'fields' ][ $field ][ 'value' ];
				$data[ $id ][ $field ] = $datum;
			}
		}

		$type = $pieces['params']->pod;

		if ( $type === HT_DMS_DECISION_POD_NAME ) {
			$data[ 'status' ] = ht_dms_decision_class()->status( $id );
		}

		if ( $type === HT_DMS_TASK_POD_NAME ) {
			if ( isset ( $data[ $id ][ 'decision' ] ) ) {
				$dID = reset( $data[ $id ][ 'decision' ] );
				$data[ $id ][ 'decision' ] = $dID;
			}
		}

		if ( $type === HT_DMS_GROUP_POD_NAME ) {
			$gID = $id;
		}
		else {
			if ( isset ( $data[ $id ][ 'group' ] ) ) {
				$gID = reset( $data[ $id ][ 'group' ] );
				$data[ $id ][ 'group' ] = $gID;
			}
		}

		if ( $type === HT_DMS_ORGANIZATION_POD_NAME ) {
			$oID = $id;
		}
		else {
			if ( isset ( $data[ $id ][ 'organization' ] ) ) {
				$oID                           = reset( $data[ $id ] );
				$data[ $id ][ 'organization' ] = $oID;
			}
		}

		$type = ht_dms_prefix_remover( $type );

		do_action( 'ht_dms_update', $id, $type, $new, $data, $gID, $oID );

		if ( $new ) {
			$action = "ht_dms_new_{$type}";
			do_action( $action, $id, $data, $gID, $oID );
		}
		else {
			$action = "ht_dms_update_{$type}";
			do_action( $action, $id, $data, $gID, $oID );
		}


		return $pieces;

	}

	function font_awesome() {
		include_once( trailingslashit( HT_DMS_ROOT_DIR ) .'inc/cdn_script.php' );


		new \ht_dms_cdn_script(
			'//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css',
			trailingslashit( HT_DMS_ROOT_URL ) .'css/font-awesome.min.css',
			'font-awesome',
			true
		);
	}

	function handlebars() {

		include_once( trailingslashit( HT_DMS_ROOT_DIR ) .'inc/cdn_script.php' );


		new \ht_dms_cdn_script(
			'//cdnjs.cloudflare.com/ajax/libs/handlebars.js/2.0.0/handlebars.min.js',
			trailingslashit( HT_DMS_ROOT_URL ) .'js/handlebars.min.js',
			'handlebars',
			false
		);
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
