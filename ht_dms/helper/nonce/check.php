<?php
/**
 * Creates a true nonce
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\helper\nonce;

class check {

	/**
	 * Verify the nonce and clear it from allowable nonces.
	 *
	 * @since 0.1.0
	 *
	 * @param string $nonce
	 * @param int $action
	 *
	 * @return bool
	 */
	public static function verify( $nonce, $action = -1 ) {
		if ( self::check_if_used( $nonce ) ) {
			self::clear( $nonce );
		}

		return self::wp_verify_nonce( $nonce, $action );

	}

	/**
	 * Check if a nonce has been used yet.
	 *
	 * Returns true if not, false if it has.
	 *
	 * @since 0.1.0
	 *
	 * @param string $nonce
	 *
	 * @return bool
	 */
	private static function check_if_used( $nonce ) {

		return get_transient( $nonce );

	}

	/**
	 * Clear a nonce to prevent it from beign used again.
	 *
	 * @since 0.1.0
	 *
	 * @param string $nonce
	 */
	private static function clear( $nonce ) {

		delete_transient( $nonce );

	}

	/**
	 * Verify that correct nonce was used with time limit.
	 *
	 * The user is given an amount of time to use the token, so therefore, since the
	 * UID and $action remain the same, the independent variable is the time.
	 *
	 * Copypasta of WordPress' wp_verify_nonce
	 *
	 * @since 0.1.0
	 *
	 * @param string $nonce Nonce that was used in the form to verify
	 * @param string|int $action Should give context to what is taking place and be the same when nonce was created.
	 *
	 * @return bool Whether the nonce check passed or failed.
	 */
	private static function wp_verify_nonce( $nonce, $action = -1 ) {
		$user = wp_get_current_user();
		$uid = (int) $user->ID;
		if ( ! $uid ) {
			/**
			 * Filter whether the user who generated the nonce is logged out.
			 *
			 * @since 3.5.0
			 *
			 * @param int    $uid    ID of the nonce-owning user.
			 * @param string $action The nonce action.
			 */
			$uid = apply_filters( 'nonce_user_logged_out', $uid, $action );
		}

		if ( empty( $nonce ) ) {
			return false;
		}

		$token = wp_get_session_token();
		$i = wp_nonce_tick();

		// Nonce generated 0-12 hours ago
		$expected = substr( wp_hash( $i . '|' . $action . '|' . $uid . '|' . $token, 'nonce'), -12, 10 );
		if ( hash_equals( $expected, $nonce ) ) {
			return 1;
		}

		// Nonce generated 12-24 hours ago
		$expected = substr( wp_hash( ( $i - 1 ) . '|' . $action . '|' . $uid . '|' . $token, 'nonce' ), -12, 10 );
		if ( hash_equals( $expected, $nonce ) ) {
			return 2;
		}

		// Invalid nonce
		return false;
	}

} 
