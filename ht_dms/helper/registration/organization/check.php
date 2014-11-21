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


	public static  function  get_filters() {
		return array( 'json_authentication_errors' => array( 'run_checks', 99, 1 ) );

	}

	public function run_checks( $pass ) {
		if ( is_null( $pass ) ) {
			$code = \ht_dms\api\internal\route::get_post_param( 'invite' );
			if ( is_string( $code ) ) {
				$verify = new verify( $code );
				if ( $verify->check() ) {
					add_filter( 'pods_json_api_access_pods_add_item', '__return_true' );

					return null;
				}

			}

		}

		return new \WP_Error( 'ht-dms-invite-code-fail', __( 'Invite code for organization not validated.', 'ht-dms') );

	}


} 
