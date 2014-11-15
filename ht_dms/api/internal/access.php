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

namespace ht_dms\api\internal;


class access implements \Filter_Hook_SubscriberInterface {

	public static $name = 'ht-dms-internal-api';
	public static $action = 'action';

	
	public static function get_filters() {
		return array(
			'restricted_site_access_is_restricted' => 'lift_login_restriction'
		);
	}

	public static function is_internal_api( $query_vars, $action = null, $check_get = false ) {
		if ( ! $check_get && is_null( $query_vars ) ) {
			return false;
		}
		if ( $check_get && is_null( $query_vars ) ) {
			if ( is_null( $action ) ) {
				$action = pods_v_sanitized( self::$action );
			}

			$name = pods_v_sanitized( 0, 'url' );
		}
		else {
			if ( is_null( $action ) ) {
				$action = pods_v( self::$action, $query_vars );
			}

			$name = pods_v( 'name', $query_vars );
		}

		if (  self::$name == $name && ! empty( $action )  ) {
			return true;
		}

	}

	public static function lift_login_restriction( $is_restricted ) {

		if ( self::is_internal_api( null, null, true ) ) {
			$is_restricted = false;
		}
		return $is_restricted;

	}

	public static function non_auth_actions() {
		/**
		 * Set which actions <em>do not</em> require authentication.
		 *
		 * @param array $actions_to_skip
		 */
		$actions_to_skip = apply_filters( 'ht_dms_internal_api_skip_authentication', array( 'hourly' ) );

		if ( is_array( $actions_to_skip ) ) {
			$actions_to_skip = array();
		}

		return $actions_to_skip;

	}

	/**
	 * Holds the instance of this class.
	 *
	 * @since  0.1.0
	 * @access private
	 * @var    object
	 */
	private static $instance;

	/**
	 * Returns an instance of this class.
	 *
	 * @since  0.1.0
	 * @access public
	 *
	 * @return access|object
	 */
	public static function init() {

		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

} 
