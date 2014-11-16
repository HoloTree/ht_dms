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
			$status_code = access::check_access( $action );
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

			if ( 550 == $response || $response == $denied ) {
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
			global $cuID;
			$cache_key = array_merge( $params, array( $action, $cuID  )  );
			$cache_key = implode( $cache_key, '=' );

			return $cache_key;
		}

	}



} 
