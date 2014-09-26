<?php
/**
 * Create and send notifications
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms;

class notification extends \ht_dms\dms\dms implements \Hook_SubscriberInterface {

	/**
	 * Set actions
	 *
	 * @since 0.0.3
	 *
	 * @return array
	 */
	public static function get_actions() {

		return array(

		);
	}

	/**
	 * Set filters
	 *
	 * @since 0.0.3
	 *
	 * @return array
	 */
	public  static function get_filters() {
		$type = self::$type;

		return array(
			 "pods_api_pre_save_pod_item_{$type}" => array(  'to_id', 5 ),
		);

	}

	/**
	 * The length to cache Pods Objects
	 *
	 * Default is 85321 seconds ~ 1 Day
	 *
	 * @var int
	 *
	 * @since 0.0.3
	 */
	static public $cache_length = 601;

	/**
	 * Cache mode for Pods Objects
	 *
	 * object|transient|site-transient
	 * @var string
	 *
	 * @since 0.0.3
	 */
	static public $cache_mode = 'cache';

	/**
	 * Name of Pod this class acts on primarily
	 *
	 * @var string
	 *
	 * @since 0.0.3
	 */
	public static $type = HT_DMS_NOTIFICATION_POD_NAME;

	/**
	 * Create a new notification
	 *
	 * @param 	int 	$to 		ID of user to send to
	 * @param 	string 	$subject	Subject of message
	 * @param 	string	$message	Message.
	 *
	 * @return 	int
	 *
	 * @since 0.0.3
	 */
	function create( $to, $subject, $message ) {
		$data = array(
			'to' => $to,
			'subject' => $subject,
			'message' => $message,
			'to_id' => $to,
		);

		return $this->object()->save( $data );

	}

	/**
	 * Send unsent notifications for those users who it's time to send notifications to.
	 *
	 * @todo hook to cron
	 *
	 * @since 0.0.3
	 */
	function send() {
		$from = 'gus@holotree.net';
		$headers = "From: Gus by Holotree <{$from}> \r\n";
		$subject = __( sprintf( 'HoloTree Notifications for %1s', date("l") ), 'ht_dms' );


		$the_messages = $this->prepare_send();
		if ( is_array( $the_messages ) ) {
			foreach ( $the_messages as $uID => $messages ) {

				$ids = wp_list_pluck( $messages, 'id' );

				$message = $this->prepare_message( $messages );

				if ( is_string( $message ) ) {
					$to  = $this->get_user_email( $uID );

					$sent = wp_mail( $to, $subject, $message, $headers );
					//pods_error( print_c3( $sent  ) );
					//if ( $sent ) {

						$pods = $this->pod();
						foreach( $ids as $id ) {
							$this->mark_sent( $id, $pods );
						}

					//}

				}


			}
		}

	}

	/**
	 * Prepare messages to send
	 *
	 * Get's all messages that need to be sent now.
	 *
	 * @return bool|array
	 *
	 * @since 0.0.3
	 */
	function prepare_send() {

		$send = false;
		$notifications = $this->get_unsent();

		$send_to_users = $this->get_users_to_send_to();
		ht_dms_automatic_notifications_class()->create_summaries( $send_to_users );

		if ( is_array( $send_to_users ) ) {
			foreach ( $notifications as $notification ) {

				if ( in_array( pods_v( 'to_id', $notification ), $send_to_users ) ) {
					$send[ (string) pods_v( 'to_id', $notification ) ] = array ( $notification->id =>
																					 array (
																						 'message' => $notification->message,
																						 'subject' => $notification->subject,
																						 'id'      => $notification->id,
																					 ),
					);
				}
			}
		}
		else {
			$send = false;
			//@todo need a better response when can't send.
		}


		return $send;

	}

	/**
	 * Get all unsent messages.
	 *
	 * @return array|bool
	 *
	 * @since 0.0.3
	 */
	function get_unsent() {
		$notifications = false;
		$params = array(
			'limit' => -1,
			'where' => 't.viewed = 0 AND t.sent = 0',

		);
		$notifications_pod = $this->pod( $params );

		if ( is_object( $notifications_pod ) && $notifications_pod->total() > 0 ) {
			$notifications = $notifications_pod->rows;
		}

		return $notifications;

	}

	/**
	 * Get IDs of users to send to to this hour.
	 *
	 * @return array|bool
	 *
	 * @since 0.0.3
	 */
	function get_users_to_send_to() {
		$today = date( "N" );
		$time = date( "H:i:s" );
		$send_to = false;

		$pods = $this->user_pod();
		while ( $pods->fetch() ) {
			$uID = $pods->id();
			$notification_day = $this->notification_day( $pods );
			if ( in_array( $today, $notification_day ) ) {
				$notification_time = $this->notification_time( $pods );
				if ( $notification_time > $time - 1 ) {
					$send_to[] = $uID;
				}

			}

		}

		return $send_to;

	}

	/**
	 * Get a user's email address.
	 *
	 * @param int 	$uID User ID to get address for.
	 *
	 * @return string|bool
	 */
	function get_user_email( $uID ) {
		$pods = $this->user_pod();
		if ( is_object( $pods ) ) {
			$rows = $pods->rows;
			if ( is_array( $rows ) ) {
				$row = pods_v( $uID, $rows );

				return pods_v( 'user_email', $row );

			}

		}

	}

	/**
	 * Take all messages to be sent and
	 *
	 * @param $messages
	 *
	 * @return string
	 */
	function prepare_message( $messages ) {
		$out = false;
		foreach( $messages as $message  ) {
			$out[] = sprintf( '<li><div class="message-subject">%1s</div><div class="message-text">%2s</div></li>',
				pods_v( 'subject', $message ),
				pods_v( 'message', $message )
			);

		}

		if ( is_array( $out ) ) {
			$out = sprintf( '<ul>%1s</ul>', esc_html( implode( $out ) ) );
			$out = sprintf( '<p>%1s</p>', $this->messsage_lead() ) . $out;
			return $out;

		}

	}

	/**
	 * A lead to preeced notifications in the message that gets sent.
	 *
	 * @return string
	 */
	function message_lead() {
		/**
		 * Set text to preeced notifications in the message that gets sent.
		 *
		 * @param string $lead The text
		 *
		 * @since 0.0.3
		 */

		return apply_filters( 'ht_dms_message_lead', '' );

	}

	/**
	 * Get a user's preferred time to receive notifications
	 *
	 * @param obj|Pods $user_pod The user's pod
	 *
	 * @return string
	 *
	 * @since 0.0.3
	 */
	function notification_time( $user_pod ) {

		if ( is_pod( $user_pod ) ) {

			return $user_pod->field( 'notification_time' );

		}


	}

	/**
	 * Get a user's preferred days to receive notifications
	 *
	 * @param obj|Pods $user_pod The user's pod
	 *
	 * @return string
	 *
	 * @since 0.0.3
	 */
	function notification_day( $user_pod ) {

		return $user_pod->field( 'notification_days' );

	}

	/**
	 * Mark a notification sent
	 *
	 * @param 	int 		$id 	Notification ID
	 * @param 	obj|Pods	$pods	Notification Pods object
	 *
	 * @return int					Notification ID
	 */
	function mark_sent( $id, $pods ) {

		return $pods->save( 'sent', 1, $id );

	}

	/**
	 * Notification Pods object
	 *
	 * @todo Go back to established pattern, accept this break, or change all to this.
	 *
	 * @param 	null|array 	$params	Optional. Pods::find() params or notification ID
	 *
	 * @return 	bool|Pods			Pods object.
	 *
	 * @access private
	 *
	 * @since 0.0.3
	 */
	private function pod( $params = null ) {
		if ( ! isset( $params[ 'expires' ] ) ) {
			$params[ 'expires' ] = self::$cache_length;
		}
		if ( ! isset( $params[ 'cache_mode' ] ) ) {
			$params[ 'cache_mode' ] = self::$cache_mode;
		}

		return pods( HT_DMS_NOTIFICATION_POD_NAME, $params );

	}

	/**
	 * Get the users Pod
	 *
	 * @todo should this be in DMS class?
	 * @todo params?
	 *
	 * @return bool|Pods
	 *
	 * @since 0.0.3
	 */
	private function user_pod() {
		$params = array(
			'limit' => -1,
			'expires' => 10*61,
		);

		return pods( 'user', $params );

	}

	/**
	 * Sets to_id field
	 *
	 * @uses "pods_api_pre_save_pod_item_{$notification}" filter (set in common class
	 *
	 * @todo needed?
	 *
	 * @param $save
	 *
	 * @since 0.0.3
	 */
	function to_id( $save ) {
		foreach( array( 'to_id', 'to' ) as $field ) {
			if ( ! isset( $save[ 'fields' ][ $field ][ 'value' ] ) ) {

				return $save;

			}

		}

		$save[ 'fields' ][ 'to_id' ][ 'value' ] = (int) $save[ 'fields' ][ 'to' ][ 'value' ];

		return $save;

	}

	/**
	 * Mark a notification as viewed
	 *
	 * @param      $id
	 * @param null $obj
	 *
	 * @return int
	 *
	 * @since 0.0.3
	 */
	function viewed( $id, $obj = null, $value = null ) {
		$obj = $this->null_object( $obj, $id );
		$field = 'viewed';

		if ( is_null( $value ) ) {
			return $obj->display( $field );

		}

		return $obj->save( $field, $value );

	}

	/**
	 * Set the name of the Pod
	 *
	 * @param 	string 	$type
	 *
	 * @since 0.0.3
	 */
	function set_type( ) {

		return self::$type;

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
	 * @since  0.0.3
	 * @access public
	 * @return object
	 */
	public static function init() {

		if ( !self::$instance )
			self::$instance = new notification();

		return self::$instance;

	}


}
