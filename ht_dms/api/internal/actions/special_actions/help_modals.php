<?php
/**
 * Route/ validate actions for the help modals.
 *
 * @package   ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2015 Josh Pollock
 */

namespace ht_dms\api\internal\actions\special_actions;

use ht_dms\ui\help\help;

class help_modals {

	/**
	 * Check if action is a help modal
	 *
	 * @since 0.3.0
	 *
	 * @param string $action Name of action
	 *
	 * @return bool
	 */
	public static function is_help_modal( $action ) {
		$help_modals = self::names();
		if ( in_array( $action, $help_modals ) ) {
			return true;
		}

	}

	/**
	 * Get names of actions
	 *
	 * @since 0.3.0
	 *
	 * @access protected
	 *
	 * @param bool $without_namespace Optional. Whether to return without namespaces. Default is true.
	 *
	 * @return array|
	 */
	protected static function names( $without_namespace = true ) {
		$help_modals = help::find_help_modals();
		if ( $without_namespace ) {
			$temp = array();
			foreach( $help_modals as $class ) {
				$class = explode( "\\", $class );
				$temp[] = array_pop( $class );
			}

			$help_modals = $temp;

		}

		return $help_modals;


	}

	/**
	 * Route action
	 *
	 * @since 0.3.0
	 *
	 * @param string $action
	 *
	 * @return null|string
	 */
	public static function route( $action ) {
		$class = self::get_class( $action );

		if ( self::is_help_modal( $action ) && class_exists( $class ) && method_exists( $class, 'content' ) ) {
			return $class::content();

		}

	}

	/**
	 * Get the namespaced class name, based on action
	 *
	 * @since 0.3.0
	 *
	 * @access protected
	 *
	 * @param string $action
	 *
	 * @return string
	 */
	protected static function get_class( $action ) {
		$class = help::$namespace_for_modals.$action;

		return $class;

	}

}
