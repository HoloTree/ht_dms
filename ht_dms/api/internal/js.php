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


class js implements \Action_Hook_SubscriberInterface {

	/**
	 * Register actions
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public static function get_actions() {
		return array(
			'wp_enqueue_scripts' => 'script',
		);

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
		wp_enqueue_script( $handle, HT_DMS_ROOT_URL .'js/ht-dms-internal-api.js', array( 'jquery'), $version, false );
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
			'url' => esc_url( home_url( 'ht-dms-internal-api' ) ),
			'id' => get_queried_object_id(),
			'nonce' => wp_create_nonce( 'ht-dms' ),
			'type' => ht_dms_prefix_remover( get_post_type() ),
		);

	}

} 
