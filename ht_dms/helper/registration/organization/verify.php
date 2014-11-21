<?php
/**
 * Process a new organization code.
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\helper\registration\organization;

class verify {
	/**
	 * The code we are checking.
	 *
	 * @since 0.1.0
	 *
	 * @access private
	 * @var string
	 */
	private $code;

	private $delete;

	/**
	 * Constructor for class.
	 *
	 * @since 0.1.0
	 *
	 * @param string $code Code to check.
	 */
	function __construct( $code, $delete = true ) {
		$this->code = $code;
		$this->delete = $delete;
	}

	/**
	 * Do the check & delete code if it passed.
	 *
	 * @since 0.1.0
	 *
	 * @return bool True if code checked out, false if not.
	 */
	public function check() {
		if ( $this->verify() ) {
			if ( $this->delete ) {
				crud::delete( $this->code );
			}
			return true;
		}

	}

	/**
	 * Verify that the code is legit.
	 *
	 * @since 0.1.0
	 *
	 * @return bool True if code checked out, false if not.
	 */
	private function verify()  {
		$code = $this->code;
		if ( false != ( $code = crud::read( $code ) ) ) {
			global $cuID;
			$email = user::email_form_id( $cuID );
			if ( hash::do_hash( $email, $cuID ) === $code ) {
				return true;

			}

		}

	}


} 
