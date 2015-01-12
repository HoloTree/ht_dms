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

namespace ht_dms\ui\build\baldrick;


use ht_dms\api\internal\access;

class settings extends \calderawp\baldrick_wp_front_end\settings {

	/**
	 * Default API URL
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	public static function default_api() {
		return access::get_url();

	}
}
