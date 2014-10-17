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


class register implements \Hook_SubscriberInterface {


	/**
	 * Set actions
	 *
	 * @since 0.0.3
	 *
	 * @return array
	 */
	public static function get_actions() {

		return array(
			'user_register' => array( 'add_to_organization', 10, 1 ),

		);

	}

	/**
	 * Set filters
	 *
	 * @since 0.0.3
	 *
	 * @return array
	 */
	public static function get_filters() {
		return array(
			'registration_errors' => array( 'pre_save_verify', 1, 3 ),
		);
	}

	function add_to_organization( $user_id ) {
		if (  ! is_null( $code = pods_v_sanitized( 'invitation_code', 'post' ) ) ) {
			if ( $oID = holotree_integer( $this->verify_code( $code, pods_v_sanitized( 'user_email', 'post' ) ) ) ) {

				ht_dms_organization_class()->add_member( $oID, $user_id );

			}
		}
	}

	function pre_save_verify( $errors, $sanitized_user_login, $user_email ) {

		if ( false ===$this->verify_code( $user_email, pods_v_sanitized( 'invitation_code', 'post' ) ) ) {
			$errors->add( 'ht_dms_bad_code', __( '<strong>ERROR</strong>: Your invite code is not valid.','holotree') );
		}

		return $errors;

	}

	private function verify_code( $email, $code ) {

		return holotree_invite_code( false, $email, false, $code );

	}


	/**
	 * Holds the instance of this class.
	 *
	 * @since 0.0.3
	 *
	 * @access private
	 * @var    object
	 */
	private static $instance;


	/**
	 * @since 0.0.3
	 *
	 * @return register|object
	 */
	public static function init() {

		if ( !self::$instance )
			self::$instance = new self();

		return self::$instance;

	}
} 
