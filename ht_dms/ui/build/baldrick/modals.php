<?php
/**
 * Interface for classes that create a Baldrick modal
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\ui\build\baldrick;


interface modals {

	/**
	 * The modal content.
	 *
	 * @since 0.3.0
	 *
	 * @return bool
	 */
	public static function content();

	/**
	 * Create modal markup
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	public static function modal();

	/**
	 * Hook the modal to wp_footer
	 *
	 * @since 0.3.0
	 */
	public static function hook();

	/**
	 * Returns an instance of this class.
	 *
	 * @since  0.1.0
	 * @access public
	 *
	 */
	public static function init();

}
