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
	/**
	 * The internal API's name
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	public static $name = 'ht-dms-internal-api';

	/**
	 * The car we store the action name in
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	public static $action = 'action';

	/**
	 * Set filters
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public static function get_filters() {
		return array(
			'restricted_site_access_is_restricted' => 'lift_login_restriction'
		);
	}

	/**
	 * Check if current request can use internal API.
	 *
	 * Note: This intentional does not search for query_vars, so it can return false quicker in its primary use. Please do not "enhance" this to do that.
	 *
	 * @since 0.1.0
	 * @param array|null $query_vars Query vars from current wp_query object. If is null, and $check_get is false, method will return false. If $check_get is true, is null, $_GET vars are used instead
	 * @param null|string $action Optional. Current action. If null, it will be found.
	 * @param bool $check_get Optional. If false, the default, $query_vars are searched.
	 *
	 * @return bool
	 */
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

		if ( self::$name == $name && ! empty( $action )  ) {
			return true;
		}

	}

	/**
	 * Allows actions that do not require authentication to bypass login restrictions
	 *
	 * @uses 'restricted_site_access_is_restricted' filter
	 *
	 * @param $is_restricted
	 *
	 * @return bool
	 */
	public static function lift_login_restriction( $is_restricted ) {

		if ( self::is_internal_api( null, null, true ) && in_array( pods_v_sanitized( self::$action), self::non_auth_actions() ) ) {
			$is_restricted = false;
		}

		return $is_restricted;

	}

	/**
	 * Returns array of actions that do not require authentication
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public static function non_auth_actions() {

		/**
		 * Set which actions <em>do not</em> require authentication.
		 *
		 * Note: must return an array. To set no actions pass an empty array.
		 *
		 * @since 0.1.0
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
