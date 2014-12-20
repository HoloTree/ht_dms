<?php
/**
 * Interface for modals
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\ui\help\modals;


interface modal {

	/**
	 * Conditional for the modal
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	function content();

	/**
	 * Conditional logic for using modal
	 *
	 * @since 0.3.0
	 *
	 * @return bool
	 */
	function conditional();
} 
