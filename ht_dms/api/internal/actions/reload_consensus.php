<?php
/**
 * Reload consensus AJAX callback
 *
 * @package   @TODO
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\api\internal\actions;


class reload_consensus {

	public static function act( $params ) {
		$dID = pods_v_sanitized( 'dID', $params );
		if ( $dID ) {
			$consensus = ht_dms_sorted_consensus_details( ht_dms_consensus_class()->sort_consensus( $dID, true ) );

			return $consensus;

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
