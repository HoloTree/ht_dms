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
		return false;

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
