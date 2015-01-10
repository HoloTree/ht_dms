<?php
/**
 * Respond on the no org help modal content
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\api\internal\actions\paginate;


class no_org_modal {

	/**
	 * Respond
	 *
	 * @since 0.3.0
	 *
	 * @param $params
	 *
	 * @return bool
	 */
	public static function act( $params ) {
		return \ht_dms\ui\help\modals\no_org::content();
	}

	/**
	 *The args
	 *
	 * @return array
	 */
	public static function args() {
		return  array();
	}

}
