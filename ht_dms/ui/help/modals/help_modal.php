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


interface help_modal {

	/**
	 * Set condition by which this will be added.
	 *
	 * @since 0.3.0
	 *
	 * @return bool
	 */
	public static function conditional();

}
