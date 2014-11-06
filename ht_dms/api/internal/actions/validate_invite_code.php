<?php
/**
 * Validate invite code AJAX callback
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\api\internal\actions;


class validate_invite_code {

	public static function act( $params ) {
		$code = pods_v_sanitized( 'code', $params );
		$email = pods_v_sanitized( 'email', $params  );
		if ( ! is_null( $code ) && ht_dms_invite_code( false, $email, false, $code ) ) {
			return 1;

		}

	}

	/**
	 * Args for this action.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public static function args() {

		return array( 'code', 'email' );

	}

} 
