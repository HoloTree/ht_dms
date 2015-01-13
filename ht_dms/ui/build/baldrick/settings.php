<?php
/**
 * Sets the Internal API for baldrick_wp_front_end
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\ui\build\baldrick;


use ht_dms\api\internal\access;

class settings implements \Action_Hook_SubscriberInterface {

	/**
	 * Set actions
	 *
	 * @since 0.3.0
	 *
	 * @return array
	 */
	public static function get_actions() {
		return array(
			'init' => 'set'

		);
	}

	/**
	 * Default API URL
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	public static function set() {
		$api = access::get_url();
		baldrick_wp_front_end_settings_object(
			array(
				'default_api' => $api
			)
		);

	}

	/**
	 * Get class instance
	 *
	 * @since 0.3.0
	 *
	 * @return settings
	 */
	public static function init() {
		return new self;

	}

}
