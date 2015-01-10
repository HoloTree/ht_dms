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


use ht_dms\api\internal\access;

class modal {


	public static function make( $action, $atts, $text = false ) {

		if ( ! isset( $atts[ 'data-autoload' ]) ) {
			$atts['data-autoload'] = true;
		}

		$api = access::get_url( $action );

		return \calderawp\baldrick_wp_front_end\modal::make( $action, $atts, $text, $api );

	}

}
