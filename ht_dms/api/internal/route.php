<?php
/**
 * Route Request To Internal API
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\api\internal;

class route implements \Action_Hook_SubscriberInterface {

	/**
	 * Register actions
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public static function get_actions() {
		return array(
			'init' => 'add_endpoints',
			'template_redirect' => 'do_api',
		);

	}

	/**
	 * Add endpoints for the API
	 *
	 * @since 0.1.0
	 */
	function add_endpoints() {
		add_rewrite_tag( '%action%', '^[a-z0-9_\-]+$' );
		add_rewrite_rule( 'ht-dms-internal-api/^[a-z0-9_\-]+$/?', 'index.php?action=$matches[1]', 'top' );
	}

	/**
	 * Main router for internal API.
	 *
	 * Checks permission, and dispatches and returns, or retruns error.
	 *
	 * @since 0.1.0
	 */
	public static function do_api() {

		global $wp_query;

		$action = $wp_query->get( 'action' );
		$access = access::is_internal_api( pods_v ( 'query_vars', $wp_query ), $action  );

		if ( $access ) {
			$status_code = self::check_access( $action );
			$denied = $response = __( 'Access denied.', 'ht-dms' );

			if ( 200 == $status_code  ) {

				$params = self::args( $action );
				$cache_key = self::cache_key( $params, $action );
				if ( false == ( $response = pods_cache_get( $cache_key ) ) ) {
					$response = self::dispatch( $action, $params  );
					pods_cache_set( $cache_key, $response, '', 599 );
				}

			}
			else {
				$response = $denied;

			}

			if ( 550 == $response ) {
				$status_code = $response;
				$response = $denied;
			}

			self::respond( $response, $status_code );

		}

	}

	/**
	 * Send the response
	 *
	 * @access private
	 *
	 * @since 0.1.0
	 *
	 * @param string|array $response Response to send. Will be encoded as JSON if is array.
	 * @param int $status_code Status code to set for the response.
	 *
	 * @return string
	 */
	private static function respond( $response, $status_code ) {
		if ( empty( $response ) ) {
			$status_code = 204;
		}

		status_header( $status_code );
		if ( is_array( $response ) ) {
			wp_send_json( $response );
		}
		else{
			echo $response;
			die();
		}

	}



	/**
	 * Get required args for the action
	 *
	 * @access private
	 *
	 * @since 0.1.0
	 *
	 * @param string $action Action name.
	 *
	 * @return array
	 */
	private static function args( $action ) {
		$class = self::action_class( $action );

		$desired_args = $class::args();
		$params = array();

		foreach( $desired_args as $arg ) {
			$params[ $arg ] = pods_v_sanitized( $arg, 'get', 0, true );
			if ( '' == $params[ $arg ] )  {
				$params[ $arg ] = 0;
			}

		}

		return $params;

	}

	/**
	 * Get a static class object, by action.
	 *
	 * Does not check if class exists. Use only for those allowed by self::action_allowed()
	 *
	 * @access private
	 *
	 * @since 0.1.0
	 *
	 * @param string $action Action name.
	 *
	 * @param $action
	 *
	 * @return object The class object.
	 */
	private static function action_class( $action ) {

		return $class = __NAMESPACE__ . '\\actions\\' . $action;

	}

	/**
	 * Dispatch requests
	 *
	 * @access private
	 *
	 * @since 0.1.0
	 *
	 * @param string $action Action name.
	 * @param array|null $params
	 *
	 * @return mixed The result of the action to return.
	 */
	private function dispatch( $action, $params = null ) {
		$class = self::action_class( $action );

		return $class::act( $params );

	}

	/**
	 * Check if access is allowed and return the status code accordingly.
	 *
	 * @access private
	 *
	 * @since 0.1.0
	 *
	 * @param string $action Action to take
	 *
	 * @return int Status code
	 */
	private static function check_access( $action ) {

		$skip = utility::non_auth_actions();

		if ( ! in_array( $action, $skip  ) || ! HT_DEV_MODE ) {
			if (  ! check_ajax_referer( 'ht-dms', 'nonce' ) ) {
				return 550;
			}

		}

		if ( ! self::action_allowed( $action ) ) {
			return 501;

		}

		return 200;

	}

	/**
	 * Actions to allow via internal API
	 *
	 * @since 0.1.0
	 *
	 * @return array Allowed actions
	 */
	public static function allowed_actions() {
		$key = __CLASS__ . __METHOD__;
		if ( false == ( $actions = get_transient( $key ) ) ) {
			$dir   = trailingslashit( dirname( __FILE__ ) ) . 'actions';
			$files = scandir( $dir );
			foreach ( $files as $file ) {
				$path = pathinfo( $file, PATHINFO_EXTENSION );
				if ( 'php' == $path ) {
					$file      = str_replace( '.php', '', $file );
					$actions[] = $file;
				}

			}

			set_transient( $key, $actions, WEEK_IN_SECONDS );

		}

		/**
		 * Filter allowable actions for internal API
		 *
		 * @since 0.1.0
		 *
		 * @param array $actions Actions to allow
		 *
		 * @return array
		 */
		return apply_filters( 'ht_dms_internal_api_allowed_actions', $actions );
	}

	/**
	 * Check if an action is allowed.
	 *
	 * @access private
	 *
	 * @since 0.1.0
	 *
	 * @param string $action Action name.
	 *
	 * @return bool
	 */
	private static function action_allowed( $action ) {

		return ( in_array( $action, self::allowed_actions() ) );

	}

	/**
	 * Construct cache key, or return false to prevent caching.
	 *
	 * @since 0.1.0
	 *
	 * @param array $params
	 * @param string $action
	 *
	 * @return bool|string
	 */
	private static function cache_key( $params, $action ) {
		if ( ! HT_DEV_MODE && is_array( $params ) && ! apply_filters( 'ht_dms_internal_api_skip_cache', false, $action, $params ) ) {
			$cache_key = array_merge( $params, array( $action )  );
			$cache_key = implode( $cache_key, '=' );

			return $cache_key;
		}

	}



} 
