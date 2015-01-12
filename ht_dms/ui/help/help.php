<?php
/**
 * Run the help things
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\ui\help;


use ht_dms\ui\build\baldrick\modals;
use ht_dms\ui\help\modals\glow_cloud;

class help implements \Action_Hook_SubscriberInterface {

	/**
	 * The namespace that help modals have.
	 *
	 * @since 0.3.0
	 *
	 * @var string
	 */
	public static $namespace_for_modals = "\\ht_dms\\ui\\help\\modals\\";

	/**
	 * Hook make it so to init
	 *
	 * @since 0.3.0
	 *
	 * @return array
	 */
	static public function get_actions() {
		return array(
			'init' => 'make_it_so'
		);

	}

	/**
	 * Instantiate classes for help things or otherwise make them happen if their method conditional() returns true;
	 *
	 * @since 0.3.0
	 */
	static function make_it_so() {
		$help_classes = self::find_help_modals();


		foreach( $help_classes as $class ) {
			if ( $class::conditional() ) {
				$class::hook();
			}

		}

 	}

	/**
	 * Return an array of classes that are help modal creating classes
	 *
	 * Criteria is they must be in the \ht_dms\ui\helpmodals namespace and implement the ht_dms\ui\help\modals\help_modal interface.
	 *
	 * @since 0.3.0
	 *
	 * @return array|mixed
	 */
	public static  function find_help_modals() {
		$key = __CLASS__ . __METHOD__;
		if ( false == ( $help_classes = get_transient( $key ) ) ) {
			$dir   = trailingslashit( dirname( __FILE__ ) ) . 'modals';
			$files = scandir( $dir );
			foreach ( $files as $file ) {
				$path = pathinfo( $file, PATHINFO_EXTENSION );
				if ( 'php' == $path ) {
					$file      = str_replace( '.php', '', $file );
					$help_classes[] = $file;
				}

			}

			if ( is_array( $help_classes ) && ! empty( $help_classes ) ) {
				$namespace = self::$namespace_for_modals;
				foreach ( $help_classes as $i => $class_name ) {

					$class = $namespace . $class_name;
					if ( method_exists( $class, 'init' ) ) {
						$class      = $class::init();
						$implements = class_implements( $class, false );
						if ( ! in_array( 'ht_dms\ui\help\modals\help_modal', $implements ) ) {
							unset( $help_classes[ $i ] );
						}else{
							$help_classes[ $i ] = $namespace.$class_name;
						}

					}else{
						unset( $help_classes[ $i ] );
					}

				}

			}

			if ( is_array( $help_classes ) && ! empty( $help_classes ) ) {
				set_transient( $key, $help_classes, WEEK_IN_SECONDS );
			}

		}

		return $help_classes;

	}

	/**
	 * Holds the instance of this class.
	 *
	 * @since  0.3.0
	 * @access private
	 * @var    object
	 */
	private static $instance;

	/**
	 * Returns an instance of this class.
	 *
	 * @since  0.3.0
	 * @access public
	 *
	 */
	public static function init() {

		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;

	}
}
