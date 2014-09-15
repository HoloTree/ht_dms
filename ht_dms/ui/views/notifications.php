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

$paginated_view_args = ht_dms_default_paginated_view_arguments( array( 'un_viewed_only' => true  ) );

$tabs = array(
	array(
		'label'		=> __( 'Notifications', 'holotree' ),
		'content' 	=> ht_dms_paginated_view_container( 'users_notifications', $paginated_view_args )
	),
	array(
		'label'		=> __( 'New Private Message', 'holotree' ),
		'content' 	=> 'Functionality not yet implemented',
	),
);

//@todo a place to load single notifications in via AJAX + make that possible.

return $ui->elements()->output_container( $tabs );
