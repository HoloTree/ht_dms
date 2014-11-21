<?php
/**
 * Hash codes for organization invites
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\helper\registration\organization;


class hash {

	/**
	 * @since 0.1.0
	 *
	 * @var string hash method to use
	 */
	private static $hash_mode = 'md5';

	/**
	 * Hash code from user email and ID
	 *
	 * @since 0.1.0
	 *
	 * @param string $email Email of user.
	 * @param int $uID ID of user
	 *
	 * @return string|bool The hash or false if user doesn't exist and/or input data is bad.
	 */
	public static function do_hash( $email, $uID ) {
		$hash = false;

		if ( is_string( $email ) && ht_dms_integer( $uID ) && get_user_by( 'id', $uID ) ) {
			$hash = hash( self::$hash_mode, $email . $uID . 'org-invite' . substr(  wp_salt( 'nonce' ), 7 ) );
		}

		return $hash;

	}
} 
