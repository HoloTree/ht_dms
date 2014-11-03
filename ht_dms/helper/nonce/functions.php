<?php
/**
 * Replace built in WordPress nonce create/check with true (use only once) nonces.
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

if ( ! function_exists( 'wp_verify_nonce' ) ) :
	/**
	 * Check nonce.
	 *
	 * @param string $nonce Nonce that was used in the form to verify
	 * @param string|int $action Should give context to what is taking place and be the same when nonce was created.
	 *
	 * @return bool Whether the nonce check passed or failed.
	 */
	function wp_verify_nonce( $nonce, $action  = -1 ) {

		return ht_dms\helper\nonce\check::verify( $nonce, $action );

	}
endif;

if ( ! function_exists( 'wp_create_nonce' ) ) {
	/**
	 * Create nonce
	 *
	 * @param string $action Scalar value to add context to the nonce.
	 *
	 * @return string The token.
	 */
	function wp_create_nonce( $action = -1 ) {

		return ht_dms\helper\nonce\create::wp_create_nonce( $action );

	}
}
