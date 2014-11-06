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
			'wp_enqueue_scripts' => 'script',
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
	 * @TODO this right
	 */
	function do_api() {
		global $wp_query;

		$action = $wp_query->get( 'action' );

		if ( ! empty( $action )  ) {
			if ( check_ajax_referer( 'ht-dms', 'nonce' ) ) {
				$response = array();
				$response[ $action ] = 'foo';
				$response            = json_encode( $response );
				status_header( 200 );
				wp_send_json( $response );
			}
			else {
				status_header( 550 );
				die( __( 'Access denied.', 'ht-dms' ) );
			}
		}



	}

	/**
	 * Set up the JS for the internal API
	 *
	 * @since 0.1.0
	 */
	function script()  {
		$version = HT_DMS_VERSION;
		if ( HT_DEV_MODE ) {
			$version = rand();
		}

		$handle = 'ht-dms-internal-api';
		wp_enqueue_script( $handle, HT_DMS_ROOT_URL .'js/ht-dms-internal-api.js', array( 'jquery'), $version, true );
		wp_localize_script( $handle, 'htDMSinternalAPIvars', $this->vars() );


	}

	/**
	 * Set up the htDMSinternalAPIvars JS object
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	private function vars() {
		return array(
			'url' => esc_url( home_url( 'ht-dms-internal-api' ) )
		);

	}

} 
