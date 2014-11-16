<?php
/**
 * Reload notification AJAX callback
 *
 * @package   @TODO
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\api\internal\actions;


class load_notification {

	/**
	 * Reload notification.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public static function act( $params ) {
		$nID = pods_v_sanitized( 'nID', $params  );
		if ( $nID ) {

			$output[ 'json' ] =  ht_dms_ui()->views()->notification( null, $nID, 'simple_json' );
			$output[ 'outer_html_id' ] = '#users-notifications-container';
			$output[ 'html_id' ] = '#notification-'.$nID;
			$output[ 'template_id' ] = '#notificaiton';

			return $output;
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

		return array( 'nID' );

	}

} 
