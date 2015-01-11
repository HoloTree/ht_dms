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


class help implements \Action_Hook_SubscriberInterface {

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
	 * Instantiate classes for help things or otherwise make them so
	 *
	 * @since 0.3.0
	 */
	static function make_it_so() {
		if ( modals\no_org::conditional() ) {
			modals\no_org::hook();
		}

		if ( modals\org_no_members::conditional() ) {
			modals\org_no_members::hook();
		}
 	}
}
