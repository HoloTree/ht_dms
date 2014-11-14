<?php
/**
 * Pseudo-cron system to run hourly. Requires external cron-runner.
 *
 * Can only work once an hour requires sending a GET request to <url>/ht-dms-internal-api?action=hourly&public_key=<public_key>
 *
 * Public key defaults to 12345 (the kind of password an idiot would have on his luggage) but can be change via the "ht_dms_lock_key" option.
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\api\internal;


class hourly {

	/**
	 * Run hourly checks if the keys are right (IE the correct public key is set and its been more than 59 minutes and 42 seconds since this last ran. )
	 *
	 * @param array $params
	 */
	public static function act( $params ) {
		if ( self::can_haz( pods_v( 'public_key', $params ) ) ) {
			self::run();
		}

	}

	/**
	 * Args for this action.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public static function args() {

		return array( 'public_key' );

	}

	/**
	 * Run the hourly events.
	 *
	 * @since 0.1.0
	 */
	private static function run() {
		ht_dms_decision_class()->checks();
		ht_dms_notification_class()->send();

		/**
		 * Add additional checks to run hourly.
		 *
		 * @param array $additional checks. Foreach key must be an <em>instance</em> of a class and value must be a method in that class.
		 *
		 * @since 0.1.0
		 */
		$additional_checks = apply_filters( 'ht_dms_additional_hourly_checks', false );
		if ( is_array( $additional_checks ) && ! empty( $additional_checks ) )  {
			foreach( $additional_checks as $class => $method ) {
				if ( is_callable( array( $class, $method ) ) ) {
					call_user_func( array( $class, $method ) );
				}

			}

		}

		//@todo move this before the filter?
		self::lock();

	}

	/**
	 * Check if we can run the hourly events
	 *
	 * @param $public_key
	 *
	 * @return bool
	 */
	private static function can_haz( $public_key ) {
		if( ! is_null( $public_key ) &&  self::check_public_key( $public_key ) && self::check_lock() ) {
			return true;

		}

	}

	/**
	 * Check the public key is correct in the incoming URL
	 *
	 * @param $public_key
	 *
	 * @return bool
	 */
	private static function check_public_key( $public_key ) {
		if ( $public_key == get_option( 'ht_dms_hourly_public_key', 12345 ) ) {
			return true;
		}

	}

	/**
	 * The name of the "lock" transient
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	private static $lock_key = 'ht_dms_lock_key';

	/**
	 * Set the lock key transient to prevent running until it is time.
	 *
	 * @since 0.1.0
	 */
	private static function lock() {
		set_transient( self::$lock_key, true, 3588 );
	}

	/**
	 * Check if time lock is in effect
	 *
	 * @since 0.1.0
	 *
	 * @return bool
	 */
	private static function check_lock() {
		if ( HT_DEV_MODE || false == ( get_transient( self::$lock_key ) ) ) {

			return true;

		}

	}

} 
