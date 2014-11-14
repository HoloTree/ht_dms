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
	function do_api() {
		global $wp_query;

		$action = $wp_query->get( 'action' );

		if ( ! empty( $action )  ) {
			$status_code = $this->check_access( $action );

			if ( 200 == $status_code  ) {
				$cache_key = implode( $_GET, '=' );
				if ( ! $response = pods_cache_get( $cache_key )  ) {
					$params = $this->args( $action );
					$response = $this->dispatch( $action, $params  );
					pods_cache_set( $cache_key, $response );
				}

			}
			else {
				$response = __( 'Access denied.', 'ht-dms' );
			}

			$this->respond( $response, $status_code );

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
	private function respond( $response, $status_code ) {
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
	private function args( $action ) {
		$class = $this->action_class( $action );

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
	 * Does not check if class exists. Use only for those allowed by $this->action_allowed()
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
	private function action_class( $action ) {

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
		$class = $this->action_class( $action );

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
	private function check_access( $action ) {
		if ( ! HT_DEV_MODE ) {
			if ( ! check_ajax_referer( 'ht-dms', 'nonce' ) ) {
				return 550;
			}
		}

		if ( ! $this->action_allowed( $action ) ) {
			return 501;

		}

		return 200;

	}

	/**
	 * Actions to allow via internal API
	 *
	 * @access private
	 *
	 * @since 0.1.0
	 *
	 * @return array Allowed actions
	 */
	private function allowed_actions() {
		$dir =  trailingslashit( dirname( __FILE__ ) ) . 'actions';
		$files = scandir( $dir  );
		foreach ( $files as $file  ) {
			$path = pathinfo( $file, PATHINFO_EXTENSION );
			if ( 'php' == $path ) {
				$file = str_replace( '.php', '', $file );
				$actions[] = $file;
			}
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
	private function action_allowed( $action ) {

		return ( in_array( $action, $this->allowed_actions() ) );

	}



} 
