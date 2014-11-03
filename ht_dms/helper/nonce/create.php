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

namespace ht_dms\helper\nonce;


class create  {

	/**
	 * Creates a cryptographic token tied to a specific action, user, and window of time.
	 *
	 * Copypasta of WordPress' wp_create_nonce
	 *
	 * @since 2.0.3
	 *
	 * @param string $action Scalar value to add context to the nonce.
	 *
	 * @return string The token.
	 */
	static function wp_create_nonce($action = -1) {
		$user = wp_get_current_user();
		$uid = (int) $user->ID;
		if ( ! $uid ) {
			/** This filter is documented in wp-includes/pluggable.php */
			$uid = apply_filters( 'nonce_user_logged_out', $uid, $action );
		}

		$token = wp_get_session_token();
		$i = wp_nonce_tick();

		$nonce = substr( wp_hash( $i . '|' . $action . '|' . $uid . '|' . $token, 'nonce' ), -12, 10 );

		self::save_nonce( $nonce );

		return $nonce;

	}

	private static function save_nonce( $nonce ) {

		set_transient( $nonce, self::length() );

	}

	private static function length() {

		return apply_filters( 'nonce_life', 599 );

	}
} 
