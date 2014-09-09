<?php
/**
 * Notifications front-end view
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link
 * @copyright 2014 Josh Pollock
 */

$ui = holotree_dms_ui();

$uID = get_current_user_id();

$tabs = array(
	array(
		'label'		=> __( 'Profile', 'holotree' ),
		'content' 	=> $ui->views()->preference( $uID, false, false ),
	),
	array(
		'label'		=> __( 'Edit Profile', 'holotree' ),
		'content' 	=> $ui->views()->preference( $uID, true, false ),
	),
	array(
		'label'		=> __( 'Notification Settigns', 'holotree' ),
		'content' 	=> $ui->views()->preference( $uID, true, true ),
	),
);

return $ui->elements()->output_container( $tabs );
