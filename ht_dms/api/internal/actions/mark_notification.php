<?php
/**
 * Mark notification AJAX callback
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\api\internal\actions;


class mark_notification {

	/**
	 * Mark a notification viewed or unviewed
	 *
	 *
	 * @since 0.1.0
	 *
	 * @param array $params
	 *
	 * @return int
	 */
	public static function act( $params ) {
		$nID = pods_v_sanitized( 'nID', $params );
		$value =  pods_v_sanitized( 'mark', $params );

		if ( $nID && in_array( $value, array( 1, 0 ) ) ) {
			$id = ht_dms_notification_class()->viewed( $nID, null, $value );

			if ( $id == $nID ) {
				return 1;
			}
			else {
				return 0;
			}
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

		return array( 'nID', 'mark' );

	}
} 
