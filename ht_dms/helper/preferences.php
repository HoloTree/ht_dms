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

namespace ht_dms\helper;

class preferences {
	function __construct() {

	}

	function profile( $get = true ) {

	}

	function notification_preferences( $get = true ) {
		$fields = $this->notification_fields();
		if ( $get ) {

			return $this->get_fields( $fields );

		}
		else {

			return $this->edit_form( $fields );
		}

	}

	private function edit_form($fields = null, $id, $button = null ) {

		return $this->user_pod( $id )->form( $fields, $button );

	}
	private function get_fields( $fields = null, $id ) {
		$pods = $this->user_pod( $id );
		$user = (int) $id;
		if ( is_null( $fields ) ) {
			$fields = $pods->fields();
			$field_names = array_keys( $fields );
		}
		else{
			foreach( $fields as $field ) {
				$field_names = $pods->field( $field );
			}
		}

		foreach( $fields as $field ) {
			$value = $pods->display( $field );
			$user[ $id ] = array( $field  => $value );
		}

		return $user;


	}

	private function notification_fields() {

		return array( 'notification_days', 'notification_time' );

	}

	private function profile_fields() {

		return array();

	}

	private function user_pod( $id ) {
		$params = array(
			'where' => 't.ID = "'. $id .'"',
			'expires' => MINUTE_IN_SECONDS,
		);

		return pods( 'user', $params );

	}

	/**
	 * Holds the instance of this class.
	 *
	 *
	 * @access private
	 * @var    object
	 */
	private static $instance;


	/**
	 * Returns the instance.
	 *
	 * @since  0.0.3
	 * @access public
	 * @return object
	 */
	public static function init() {

		if ( !self::$instance )
			self::$instance = new preferences();

		return self::$instance;

	}

}
