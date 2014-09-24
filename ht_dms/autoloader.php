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
/**
 * Autoloads MyPlugin classes using WordPress convention.
 *
 * @author Carl Alexander
 */
class HT_DMS_Autoloader {
	/**
	 * Registers HT_DMS_Autoloader as an SPL autoloader.
	 *
	 * @param boolean $prepend
	 */
	public static function register( $prepend = false) {

		spl_autoload_register( array(new self, 'autoload'));

	}

	/**
	 * Handles autoloading of MyPlugin classes.
	 *
	 * @param string $class
	 */
	public static function autoload( $class ) {
		if ( 0 !== strpos($class, 'HT_DMS' ) ) {
			return;
		}

		$class = str_replace( 'HT_DMS_', '', $class );

		$file = trailingslashit( dirname( __FILE__ ) ) . str_replace( array( '_', "\0" ), array( '/', '' ), $class ).'.php';
		if (is_file( $file ) ) {
			require_once $file;
		}
		else{
			ht_dms_error( var_dump( array( $file, $class ) ) );
		}

	}
}
