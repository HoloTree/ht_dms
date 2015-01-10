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


abstract class help {

	/**
	 * Create modal markup
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	public function modal() {
		return modal::make( 'no_org_modal', array() );
	}

	/**
	 * Hook the modal to wp_footer
	 *
	 * @since 0.3.0
	 */
	public static function hook() {
		add_action( 'wp_footer', array( self::init(), 'modal' ) );
	}

}
