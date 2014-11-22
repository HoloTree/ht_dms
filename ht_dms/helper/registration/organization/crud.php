<?php
/**
 * CRUD for organization codes
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\helper\registration\organization;


class crud {

	/**
	 * The option we are using to store codes.
	 *
	 * @since 0.1.0
	 * @access private
	 * @var string name of option.
	 */
	private static $option_name = 'ht_dms_org_create_codes';

	/**
	 * Create the code for a user.
	 *
	 * @since 0.1.0
	 *
	 * @param int $uID User ID to create code for.
	 *
	 * @return bool True if code created.
	 */
	public static function create( $uID, $return_code = false ) {
		$email = user::email_form_id( $uID );
		$code = hash::do_hash( $email, $uID );

		if ( is_string( $code ) ) {
			$update = self::update( $code );
			if ( $return_code ) {
				return $code;
			}

			return $update;

		}

	}

	/**
	 * Get all codes or a code.
	 *
	 * @param null|string $code Optional. If null, the default, all codes are returned. If a valid code is specified, it is returned.
	 *
	 * @return string|array|bool
	 */
	public static function read( $code = null ) {

		$values = get_option( self::$option_name, array() );
		if ( ! is_null( $code ) ) {
			if ( self::code_exists( $code ) ) {

				return $code;
			}
			else {
				return false;
			}

		}

		return $values;

	}

	/**
	 * Add or remove a code from the saved codes
	 *
	 * @since 0.1.0
	 *
	 * @param string $value The code to add or delete
	 * @param bool  $delete Optional. Whether to delete or not. Default is false.
	 *
	 * @return bool
	 */
	private static function update( $value, $delete = false ) {
		$option = self::$option_name;
		$values = get_option( $option, array() );

		if ( is_string( $values ) ) {
			$values = array( $values );
		}

		$exists = self::code_exists( $value );
		if ( $delete ) {

			if ( $exists ) {
				$key = array_search( $value, $values );
				if ( isset( $values[ $key ] ) ) {
					unset( $values[ $key ] );
				}
			}
			else {
				return;
			}

		}
		else {
			if ( $exists ) {
				return;
			}
			else {
				$values[] = $value;
			}

		}

		return update_option( $option, $values );

	}

	/**
	 * Delete an existing code
	 *
	 * @since 0.1.0
	 *
	 * @param string $code Code to delete
	 *
	 * @return bool
	 */
	public static function delete( $code ) {

		return self::update( $code, true );

	}

	/**
	 * Check if a code exists.
	 *
	 * @since 0.1.0
	 *
	 * @param string $code Code to check if it exists.
	 *
	 * @return bool
	 */
	private static function code_exists( $code  ) {

		$codes = self::read();

		if ( in_array( $code, $codes )) {
			return true;

		}

	}






} 
