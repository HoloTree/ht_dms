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

$view_id = pods_v( 'dms_id' );


$tabs = array(
	array(
		'label'		=> __( 'Profile', 'holotree' ),
		'content' 	=> $ui->views()->preferences( $uID, false, false ),
	),
	array(
		'label'		=> __( 'Edit Profile', 'holotree' ),
		'content' 	=> $ui->views()->preferences( $uID, true, false ),
	),
	array(
		'label'		=> __( 'Notification Settings', 'holotree' ),
		'content' 	=> $ui->views()->preferences( $uID, true, true ),
	),
);

if ( (int) $view_id !== (int) $uID ) {
	unset( $tabs[1] );
	unset( $tabs[2] );
	$tabs[] = array(
		'label' 	=> __( 'Send Message', 'holotree' ),
		'content'	=> 'Functionality not complete',
	);
}

return $ui->elements()->output_container( $tabs );
