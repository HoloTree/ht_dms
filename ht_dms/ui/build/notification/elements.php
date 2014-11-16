<?php
/**
 * UI elements for notifications
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\ui\build\notification;


class elements {
	/**
	 * Adds additional markup for notifications view to allow AJAX-based UI.
	 *
	 * @since 0.1.0
	 *
	 * @param $type
	 *
	 * @return string
	 *
	 */
	public static function notification_view_header( ) {

		$header = sprintf(
			'<div id="notifications-header"><h3 style="float:left">%0s</h3>
 					<span id="notification-options" class="button" style="float:right">
 						<a href="#" id="notification-all-view-toggle" state="%1s">%2s</a>
 					</span></div>',

			__( 'Notifications', 'ht_dms' ),
			esc_attr( 1 ),
			__( 'Show All Messages', 'ht_dms' )

		);


		return $header;

	}


} 
