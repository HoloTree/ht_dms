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

	function profile_list( $id ) {
		$values = $this->get_fields( $this->profile_fields(), $id );
		$out = false;

		$pods = $this->user_pod( null );

		if ( is_array( $values ) ) {
			foreach( $values as $value ) {
				$field = key( $value );
				$value = esc_html( $value[ key( $value ) ] );
				$label = esc_html( $this->label( $field, $pods ) );

				if ( is_string( $value ) ) {
					$out[] = sprintf( '<li><span class="label preference-label">%1s</span> %2s</li>', $label, $value );
				}
				else {
					var_dump( $field );
				}
			}

		}


		if ( is_array( $out ) ) {
			return sprintf( '<ul class="preference-list">%1s</ul>', implode( $out ) );

		}

	}

	function notification_preferences( $get = true, $id = null ) {
		$id = ht_dms_null_user( $id );
		$fields = $this->notification_fields();
		if ( $get ) {

			return $this->get_fields( $fields, $id );

		}
		else {

			return $this->edit_form( $fields, $id );
		}

	}

	function edit_form( $fields = null, $id, $button = null, $notifications = false ) {
		if ( is_null( $fields ) ) {
			if ( $notifications ) {
				$fields = $this->notification_fields();
			}
			else {
				$fields = $this->profile_fields();
			}

		}

		if ( $notifications ) {
			return $this->notification_preferences( false );
		}

		return $this->user_pod( $id )->form( $fields, $button );

	}
	private function get_fields( $fields = null, $id ) {
		$pods = $this->user_pod( $id );
		$user = false;

		foreach( $fields as $field ) {
			$value = $pods->display( $field );
			$user[] = array( $field  => $value );
		}

		return $user;


	}

	private function profile_view_fields() {

		return array_merge( $this->profile_fields(), $this->notification_fields() );

	}

	private function notification_fields() {

		return array( 'notification_days', 'notification_time' );

	}

	private function profile_fields() {

		return array( 'first_name', 'last_name', 'twitter', 'facebook', 'google', 'linkedin', 'github', 'avatar' );

	}

	private function user_pod( $id ) {
		$params = array(
			'where' => 't.ID = "'. $id .'"',
			'expires' => 599,
		);

		if ( is_null( $id ) ) {
			unset( $params[ 'where' ] );
		}

		return pods( 'user', $params );

	}

	private function label( $field, $pods ) {

		$label = $pods->fields( $field );

		return pods_v( 'label', $label, $field, true );

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

