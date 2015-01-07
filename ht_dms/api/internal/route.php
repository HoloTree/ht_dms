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

		if ( ! strpos( $_SERVER[ 'REQUEST_URI'], 'ht-dms-internal-api' || ! access::verify_referer() ) ) {
			return;
		}

		global $wp_query;

		$action = $wp_query->get( 'action' );
		$access = access::is_internal_api( pods_v ( 'query_vars', $wp_query ), $action  );

		if ( $access ) {
			if ( ! defined( 'HT_DMS_DOING_INTERNAL_API' ) ) {
				define( 'HT_DMS_DOING_INTERNAL_API', true );
			}
			
			$status_code = access::check_access( $action );
			$denied = $response = __( 'Access denied.', 'ht-dms' );

			if ( 200 == $status_code  ) {

				$params = self::args( $action );
				$cache_key = self::cache_key( $params, $action );
				if ( HT_DEV_MODE || false == ( $response = pods_cache_get( $cache_key ) ) ) {
					$response = self::dispatch( $action, $params  );

					if ( ! is_null( $json = pods_v( 'json', $response ) ) && $json === json_encode( array( 0 ) ) ) {
						$status_code = '404';
						$response = js::messages( 'noItems' );
						pods_cache_clear( $cache_key );
					}
					else {
						pods_cache_set( $cache_key, $response, '', 599 );
					}

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

		self::headers( $status_code );
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
		$method = 'get';

		if ( method_exists( $class, 'method' ) ) {
			$method = $class::method();
		}


		foreach( $desired_args as $arg ) {
			if ( $method === 'get' ) {
				$params[ $arg ] = pods_v_sanitized( $arg, 'get', 0, true );
			} else {
				$params[ $arg ] = self::get_post_param( $arg );
			}
			if ( '' == $params[ $arg ] )  {
				$params[ $arg ] = 0;
			}

		}

		return $params;

	}

	/**
	 * Stores the decoded post data
	 *
	 * @since 0.1.0
	 * @access private
	 * @var array
	 */
	private static $post_data;

	/**
	 * Get an argument from a POST request.
	 *
	 * NOTE: This method intentionally only works when POST data is JSON.
	 *
	 * @since 0.1.0
	 *
	 * @access private
	 * @param string $arg Argument to retrieve
 	 *
	 * @return mixed
	 */
	public static function get_post_param( $arg )  {
		if ( ! self::$post_data) {
			global $HTTP_RAW_POST_DATA;
			self::$post_data = pods_sanitize( json_decode( $HTTP_RAW_POST_DATA ) );
		}

		if ( ! is_null( $value = pods_v_sanitized( $arg, self::$post_data ) ) ) {
			return $value;

		}

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
	private static function dispatch( $action, $params = null ) {
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
		if ( ! HT_DEV_MODE && is_array( $params ) && !( in_array( $action, array( 'new_organization_code', 'reload_consensus', 'reload_membership' ) ) ) && ! apply_filters( 'ht_dms_internal_api_skip_cache', false, $action, $params ) ) {
			global $cuID;
			$cache_key = array_merge( $params, array( $action, $cuID  )  );
			$cache_key = implode( $cache_key, '=' );

			return $cache_key;
		}

	}

	/**
	 * Send Headers
	 *
	 *
	 * @todo implement
	 *
	 * @see https://github.com/HoloTree/ht_dms/issues/117
	 * @see https://github.com/HoloTree/ht_dms/issues/130
	 *
	 * @since 0.1.0
	 *
	 * @param int $status_code
	 * @param int $expires
	 */
	private static function headers( $status_code, $expires = 59 ) {

		status_header( $status_code );
		if ( 200 == $status_code ) {
			header( 'Pragma: private' );
			header( 'Cache-Control: maxage=' . $expires );
			header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + $expires ) . ' GMT' );
		}

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
	 * @return route|object
	 */
	public static function init() {

		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;

	}



} 
