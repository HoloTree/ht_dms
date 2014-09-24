<?php
/**
 * Actions and filters from the HoloTree DMS theme.
 *
 * @package   @holotree_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\helper;


class theme implements \Hook_SubscriberInterface {

	/**
	 * Set actions
	 *
	 * @since 0.0.3
	 *
	 * @return array
	 */
	public static function get_actions() {
		$prefix = self::prefix();
		return array(
			"{$prefix}_ht_dms_site_info" => 'footer_text',
			"{$prefix}_main_class" => 'main_class'
		);
	}

	/**
	 * Set filters
	 *
	 * @since 0.0.3
	 *
	 * @return array
	 */
	public  static function get_filters() {
		$prefix = self::prefix();
		return array(
			"{$prefix}_no_sidebar" => 'true',
			"{$prefix}_use_off_canvas" => 'false',


		);

	}


	/**
	 * Initializes the HoloTree_DMS_Theme_Setup() class
	 *
	 * Checks for an existing HoloTree_DMS_Theme_Setup() instance
	 * and if it doesn't find one, creates it.
	 */
	public static function init() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self;
		}

		return $instance;
	}



	static function footer_text() {
		$prefix = self::prefix();
		if ( false !== ( $text = apply_filters( "{$prefix}_footer_text", false ) ) ) {
			return $text;

		}

	}

	function main_class( ) {
		return 'large-12 small-12 columns';
	}


	static function prefix() {
		if ( ( $stylesheet = get_stylesheet() ) == 'ht_dms_theme' ) {
			return 'htdms';
		}

		$stylesheet = str_replace( '-', '_', $stylesheet );

		return $stylesheet;

	}

	function true(  ) {

		return true;

	}

	function false() {

		return false;

	}
}
