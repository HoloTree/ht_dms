<?php
/**
 * Reload membership AJAX callback
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\api\internal\actions;


class reload_membership extends action implements action_interface {

	/**
	 * Refresh membership view
	 *
	 * @since 0.1.0
	 *
	 * @param $params
	 *
	 * @return string
	 */
	public static function act( $params) {
		$gID = pods_v_sanitized( 'gID', $params );
		if ( $gID ) {

			return ht_dms_ui()->build_elements()->group_membership( $gID );

		}

	}

	/**
	 * Args for this action.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public static function args() {

		return array( 'gID' );

	}

} 
