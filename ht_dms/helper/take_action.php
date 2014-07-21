<?php
/**
 * GET/POST Actions for HoloTree DMS
 *
 * @package   @holotree_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\helper;


class take_action {

	function __construct() {
		//add_action( 'init', array( $this, 'do_message' ), 49 );
	}

	/**
	 * Take decision action
	 *
	 * @param $action
	 * @param $id
	 *
	 * @return null|string|void
	 *
	 * @since 0.0.1
	 */
	function decision( $action, $id ) {
		$d = holotree_decision_class();

		if ( $action === 'block' ) {
			$d->block( $id );

		}
		elseif ( $action === 'unblock' ) {
			$d->unblock( $id );


		}
		elseif ( $action === 'accept' ) {
			$d->accept( $id );


		}
		elseif ( $action === 'propose-change' ) {
			//add_action( 'get_header', 'redirect' );
		}
		elseif ( $action === 'accept-change' ) {
			$d->accept_modify( $id );
		}
		else {
			holotree_error( __METHOD__, print_c3( array( $id, $action ) ) );
		}

		$d->reset_cache( $id );

		return $this->message( $action, $id, 'd' );
	}

	/**
	 * Take group action
	 *
	 * @param $action
	 * @param $id
	 *
	 * @return null|string|void
	 *
	 * @since 0.0.1
	 */
	function group( $action, $id ) {
		$g = holotree_group_class();
		$uID = pods_v( 'dms_member_id', 'get', false, true );

		if ( $action === 'join-group' ) {
			$g->add_member( $id, get_current_user_id() );


		}
		elseif ( $action === 'approve-pending' || $action = 'reject-pending' && false !==  $uID  ) {

			if ( $uID !== false ) {
				if ( $action === 'approve-pending' ) {
					$approve = true;
				}
				elseif ( $action === 'reject-pending' ) {
					$approve = false;
				}
				else{
					holotree_error( 'Bad dms_action', __METHOD__ );
				}

				$g->pending( $id, $uID, false, $approve );


			}
		}
		else {
			holotree_error( __METHOD__, print_c3( array( $id, $action ) ) );
		}

		$g->reset_cache( $id );

		return $this->message( $action, $id, 'g' );

	}

	/**
	 * Take task actions
	 *
	 * @param $action
	 * @param $id
	 *
	 * @return null|string|void
	 *
	 * @since 0.0.1
	 */
	function task( $action, $id ) {
		$t = holotree_task_class();

		if ( $action === 'completed' ) {
			$t->completed( $id );
		}
		elseif ( $action === 'add_blocking' ) {
			$t->add_blocking( $id );
		}
		elseif ( $action === 'add_blocker' ) {
			$t->add_blocker( $id );
		}
		else {
			holotree_error( __METHOD__, print_c3( array( $id, $action ) ) );
		}

		$t->reset_cache( $id );

		return $this->message( $action, $id, 't' );

	}

	/**
	 * Take notification action
	 * @param $action
	 * @param $id
	 *
	 * @return null|string|void
	 *
	 * @since 0.0.1
	 */
	function notification( $action, $id ) {
		$n = holotree_notification_class();

		if ( $action === 'mark-notification' ) {


		}
		elseif ( $action = 'archive-notification' ) {

		}
		else {
			holotree_error( __METHOD__, print_c3( array( $id, $action ) ) );
		}

		//@TODO reset?
		return $this->message( $action, $id, 'n' );

	}

	/**
	 * Create comment
	 *
	 * @param $id
	 *
	 * @return null|string|void
	 *
	 * @since 0.0.1
	 */
	function comment( $id ) {
		$content = pods_v( 'dms_comment_text', 'post', false, true );
		if ( false !== $content ) {
			$data = array (
				'comment_post_ID'  => $id,
				'comment_content'  => $content,
				'user_id'          => get_current_user_id(),
				'comment_approved' => 1,
			);
			wp_insert_comment( $data );
		}

		return $this->message( false, false, 'c' );

	}

	/**
	 * Set the title to use for group/ decision
	 *
	 * @param $id
	 * @param $what
	 *
	 * @return string
	 *
	 * @since 0.0.1
	 */
	private function title( $id, $what ) {
		$title = '';

		if ( 'd' === $what || 'g' === $what ) {
			if ( 'd' === $what ) {
				$title = 'the decision';
			}

			if ( 'g' === $what ) {
				$title = 'the group';
			}

			$get_the_title = get_the_title( $id );
			if ( $get_the_title !== ''  ) {
				$title = $get_the_title;
			}

		}

		return $title;

	}

	/**
	 * The message to be returned
	 * @param $action
	 * @param $id
	 * @param $what
	 *
	 * @return null|string|void
	 *
	 * @since 0.0.1
	 */
	function message ( $action, $id, $what ) {
		$text = null;


		//@todo make translation ready
		if ( 'd' === $what ) {
			if ( $action !== 'accept-change' ) {
				$title = $this->title( $id, $what );

				$text = __( sprintf( 'You have %1s %22s', $action, $title ), 'holotree' );
			}
			else {
				$text = __( 'Proposed Modification Accepted', 'holotree' );
			}
		}
		elseif ( 'g' === $what ) {
			$obj = holotree_group( $id );
			$title = $this->title( $id, $what );

			if ( 'join-group'  === $action ) {
				$access = $obj->field( 'open_access' );
				if ( $access == 1 ) {
					$text = __( sprintf( 'You have joined %1s', $title ), 'holotree' );
				}
				else {
					$text = __( sprintf( 'Your request to join %1s is pending', $title ), 'holotree' );
				}
			}
			if ( 'reject-pending' === $action ) {
				$user = get_userdata( $id );
				if ( is_object( $user ) ) {
					$user = $user->display_name;
					$text = __( sprintf( 'You have rejected the membership of %1s', $user ), 'holotree' );
				}
			}

		}
		elseif ( 't' === $what ) {
			$text = __( 'Task Updated.', 'holotree' );

		}
		elseif ( 'n' === $what ) {
			$text = __( 'Message Updated' );

			if ( 'archive-notification' === $action ) {
				$text = __( 'Message Archived', 'holotree' );
			}

		}
		elseif ( 'c' === $what ) {
			$text = __( 'Comment Updated.', 'holotree' );
		}

		if ( ! is_null( $text ) ) {
			return $text;

		}

	}

	/**
	 * Actions that this class should let pass without taking action
	 *
	 * @param 	int ID Current value of dms_id get/post var when this is filter is called.
	 *
	 * @return 	array
	 *
	 * @since 	0.0.1
	 */
	function take_no_action( $id ) {
		$actions = array(
			'changing',
		);
		/**
		 * Add actions to the "whitelist" of allowed values for dms_action that will be allowed to pass without action being taken
		 *
		 * Var ID should correspond to ID of item that triggered the action and can be used to target specific items.
		 *
		 * @param array $actions	Whitelisted actions.
		 * @param int	$id			Current value of dms_id get/post var when this is filter is called.
		 *
		 * @since 0.0.1
		 */
		$actions = apply_filters( 'ht_dms_take_no_action', $actions, $id );

		return $actions;

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
