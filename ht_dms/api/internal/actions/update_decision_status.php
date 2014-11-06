<?php
/**
 * Update decision status AJAX callback
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\api\internal\actions;


class update_decision_status {

	/**
	 * Update decision status
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public static function act( $params ) {
		$dID = pods_v_sanitized( 'dID', $params );
		if ( $dID ) {
			return ucwords( ht_dms_decision_class()->status( $dID ) );

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

		return array( 'dID' );

	}
} 
