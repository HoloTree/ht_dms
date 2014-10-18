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

namespace ht_dms\helper\registration;


class codes {
	private static $secret_code_option = 'ht_dms_secret_codes';
	public static $public_code_option = 'register_plus_redux_invitation_code_bank-rv1';
	public static $prehash = '@@';

	public static function create_invite_code( $oID, $email ) {

		if ( ht_dms_integer( $oID ) && is_email( $email ) ) {
			$email = strtolower( $email );
			$public = self::generate_public( $oID, $email );
			if ( $public ) {
				$public = $oID . self::$prehash . $public;

				$private = md5( $public . date( 'U' ) );

				if ( self::save_codes( $public, $private ) ) {
					return $public;

				}


			}

		}

	}

	public static function verify_code( $email, $code ) {
		if ( is_email( $email ) && $code  ) {
			$exploder = explode( self::$prehash, $code  );
			if ( is_array( $exploder  ) && isset( $exploder[1] ) && ht_dms_integer( $exploder[0] ) ) {
				$oID = $exploder [0];
				$check_public = $exploder[1];
				if ( self::generate_public( $oID, $email )  === $check_public ) {
					$check_for_private = self::get_private( $code );
					if ( $check_for_private ) {

						return $oID;

					}

				}

			}

		}

	}

	private static function generate_public($oID, $email  ) {
		if ( ht_dms_integer( $oID ) && is_email( $email ) ) {
			return md5( $oID . $email );
		}

	}


	private static function get_private( $public ) {
		$public_codes = self::get_codes();
		if ( is_array( $public_codes ) && in_array( $public, $public_codes ) ) {
			$private_codes = self::get_codes( false );
			if ( ! is_null( $private = pods_v( $public, $private_codes ) ) ) {

				return $private;

			}

		}

	}


	private static function save_codes( $public, $private ) {
		$success = self::update_public_codes( $public );
		$success = self::update_private_codes( $public, $private );

		return $success;

	}

	private static function update_public_codes( $new_value ) {
		$option = self::$public_code_option;
		$values = get_option( $option, array() );
		if ( is_string( $values ) ) {
			$values = array( $values );
		}

		$values[] = $new_value;

		return update_option( $option, $values );

	}

	private static function update_private_codes( $new_public, $new_private ) {
		$option = self::$secret_code_option;
		$values = get_option( $option, array() );
		if ( is_string( $values ) ) {
			$values = array( $values );
		}

		$values[ $new_public ] = $new_private;

		return update_option( $option, $values );

	}

	private static function get_codes( $public = true ) {
		if ( $public ) {
			return get_option( self::$public_code_option );

		}

		return get_option( self::$secret_code_option );

	}

} 
