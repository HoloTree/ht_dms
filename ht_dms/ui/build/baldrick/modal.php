<?php
/**
 * Create a Baldrick Modal
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\ui\build\baldrick;


use ht_dms\api\internal\access;

class modal {

	/**
	 * Create a Baldrick Modal
	 *
	 * @since 0.3.0
	 *
	 * @param string $action
	 * @param $atts
	 * @param bool $text
	 *
	 * @return string
	 */
	public static function make( $action, $atts = array(), $text = false ) {

		if ( ! isset( $atts[ 'data-autoload' ]) ) {
			$atts['data-autoload'] = 'true';
		}

		$api = access::get_url( $action );

		return \calderawp\baldrick_wp_front_end\modal::make( $action, $atts, $text, $api );

	}

}
