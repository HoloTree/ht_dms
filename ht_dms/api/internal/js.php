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
	public static function script()  {
		$version = HT_DMS_VERSION;
		if ( HT_DEV_MODE ) {
			$version = rand();
		}

		$handle = 'ht-dms-internal-api';
		wp_enqueue_script( $handle, HT_DMS_ROOT_URL .'js/ht-dms-internal-api.js', array( 'jquery'), $version, false );
		wp_localize_script( $handle, 'htDMSinternalAPIvars', self::vars() );


	}

	/**
	 * Set up the htDMSinternalAPIvars JS object
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	private static function vars() {
		return array(
			'url' => esc_url( home_url( 'ht-dms-internal-api' ) ),
			'id' => get_queried_object_id(),
			'nonce' => wp_create_nonce( access::$nonce_action ),
			'type' => ht_dms_prefix_remover( get_post_type() ),
			'messages' => self::messages(),

		);

	}

	public static function messages( $message = null ) {
		$messages = array(
			'noItems' => __( 'No items found.', 'ht-dms' ),
			'showNew' => __( 'Show New Messages Only', 'ht-dms' ),
			'showAll' => __( 'Show All Messages', 'ht-dms' ),
			'inviteCodeFail' => __( 'Your invite code is not valid.', 'ht-dms' ),
			'inviteCodeSuccess' => __( 'Your invite code is valid.', 'ht-dms' ),
			'inviteCodeChecking' => __( 'Checking code now', 'ht-dms' ),
			'success' => __( 'Success', 'ht-dms' ),
		);

		$messages = apply_filters( 'ht_dms_intenral_api_messages', $messages );

		if ( ! is_null( $message ) && ! is_null( $return_message = pods_v( $message, $messages ) ) ) {
			return $return_message;
		}

		return $messages;

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
	 * @return js|object
	 */
	public static function init() {

		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;

	}

} 
