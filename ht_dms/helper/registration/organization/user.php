<?php
/**
 * Get user info
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\helper\registration\organization;


class user {

	/**
	 * Get a user's email by ID.
	 *
	 * @param int $uID User ID
	 *
	 * @return string|null
	 */
	public static  function email_form_id( $uID ) {
		$email = pods_v( 'user_email', pods_v( 'data', get_userdata( $uID ) ) );

		return $email;

	}

} 
