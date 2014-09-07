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

class notification extends dms{
	function __construct() {

	}

	function create( $to, $subject, $message ) {
		$data = array(
			'to' => $to,
			'subject' => $subject,
			'message' => $message,
		);

		return $this->object()->save( $data );
	}

	function send() {
		$today = date( "d" );
		$time = date( "H:i:s" );
		$from = 'gus@holotree.net';
		$headers = "From: Gus by Holotree <{$from}> \r\n";
		$subject = '?';
		$user_pod = $this->user_pod( );
		$users = $this->all_users();
		$notifications_pod = $this->object();
		foreach( $users as $uID => $email ) {
			$users_pod = $user_pod->fetch( $uID );
			$notification_day = $this->notification_day( $users_pod );
			if ( in_array( $today, $notification_day ) ) {
				$notification_time = $this->notification_time( $user_pod );
				if ( $notification_time > $time - 1 ) {

					$notifications = $this->users_notifications( $notifications_pod, $uID );
					if ( $notifications->total() > 0 ) {
						$message = false;
						while ( $notifications->fetch()  ) {
							$message[] = $notifications->display( 'message' );
							$sent[] = $notifications->id();
						}

						if ( is_array( $message ) ) {
							if ( wp_mail( $email, $subject, implode( $message ), $headers ) ) {
								$notifications->reset();
								foreach( $sent as $id ) {
									$notifications->save( 'sent', 1, $id );

								}

							}

						}

					}
				}
			}

			$user_pod->reset();

		}
	}


	function notification_time( $user_pod ) {

	}

	function notification_days( $user_pod ) {

	}




	private function user_pod() {
		$params = array(
			'expires' => MINUTE_IN_SECONDS,
		);

		return pods( 'user', $params );

	}

	private function all_users(){

		$objects = get_users( array( 'fields' => array( 'ID', 'user_email' ) ) );
		foreach( $objects as $user ) {
			$users[ $user->ID ] = $user->user_email;
		}

		return $users;

	}

	private function users_notifications( $notifications_pod, $uID, $unset_only = true ) {
		$params = array(
			'expires' => MINUTE_IN_SECONDS,
			'where' => 'to.ID = "'. $uID .'" AND t.viewed = 0',
		);
		if ( $unset_only ) {
			$params[ 'where' ] = $params[ 'where' ] . ' AND t.sent = 0 ';
		}

		return $notifications_pod->find( $params );


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

