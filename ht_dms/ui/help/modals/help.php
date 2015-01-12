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

namespace ht_dms\ui\help\modals;


use ht_dms\ui\build\baldrick\modal;

abstract class help {

	/**
	 * Will hold the name of the inheriting class.
	 *
	 * DO NOT USE, as it could be empty. Use self::get_class_name() instead.
	 *
	 * #@since 0.3.0
	 *
	 * @var
	 */
	public static $class_name;

	/**
	 * Create modal markup
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	public static function modal() {
		$modal =  modal::make( self::get_class_name(), array() );
		echo $modal;
	}

	/**
	 * Hook the modal to wp_footer
	 *
	 * @since 0.3.0
	 */
	public static function hook() {
		$class = self::get_class_name( false );

		add_action( 'wp_footer', array( $class::init(), 'modal' ) );
	}

	/**
	 * Get the class' name
	 *
	 * @since 0.3.0
	 *
	 * @return array|string
	 */
	public static function get_class_name( $without_namespace = true ) {
		if ( ! $without_namespace || ! is_string( self::$class_name ) ) {
			$class_name = get_called_class();
			if ( $without_namespace ) {
				$class_name = explode( '\\', $class_name );
				end( $class_name );
				$last  = key( $class_name );
				$class_name = $class_name[ $last ];
			}
		}else{
			$class_name = self::$class_name;
		}

		return $class_name;

	}

}
