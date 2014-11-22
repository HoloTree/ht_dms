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

namespace ht_dms\helper\registration\organization;

class check implements \Filter_Hook_SubscriberInterface  {

	/**
	 * Register the filter for this class
	 *
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public static  function  get_filters() {
		return array( 'json_authentication_errors' => array( 'run_checks', 99, 1 ) );

	}

	/**
	 * Check if code is valid and allow use of endpoint if so.
	 *
	 * @uses json_authentication_errors filter
	 *
	 * @since 0.1.0
	 *
	 * @param $pass
	 *
	 * @return null|\WP_Error|\WP_JSON_ResponseInterface
	 */
	public function run_checks( $pass ) {
		if ( is_null( $pass ) ) {
			$code = \ht_dms\api\internal\route::get_post_param( 'invite' );
			if ( is_string( $code ) ) {
				$verify = new verify( $code );
				if ( $verify->check() ) {
					add_filter( 'pods_json_api_access_pods_add_item', '__return_true' );
					crud::delete( $code );
					return null;
				}

			}

		}

		add_filter( 'pods_json_api_access_pods_add_item', '__return_false' );
		return $this->error();

	}

	/**
	 * Error to return if code fail.
	 *
	 * @since 0.1.0
	 *
	 * @return \WP_Error|\WP_JSON_ResponseInterface
	 */
	private function error() {
		$message = __( 'Invite code for organization not validated.', 'ht-dms' );
		$response = json_ensure_response( json_encode( array( 'message' => $message ) ) );
		$response->set_status( 500 );
		$response->header( 'Location', json_url( '/pods/' . HT_DMS_ORGANIZATION_POD_NAME ) );

		return $response;

	}


} 
