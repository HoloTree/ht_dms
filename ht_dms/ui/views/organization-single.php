<?php
/**
 * Organization single view
 *
 * @package   holotree
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

global $post;
$id = $post->ID;

$obj = ht_dms_organization( $id );
$org_class = ht_dms_organization_class();

$uID = get_current_user_id();
$ui = ht_dms_ui();

$paginated_view_args = ht_dms_default_paginated_view_arguments( array( 'oID' => $id ) );

if ( $org_class->is_member( $id, $uID, $obj ) || $org_class->open_access( $id, $obj )   ) {

	$tabs = array (

		array (
			'label'   => ht_dms_add_icon( __( 'My Groups In Organization', 'ht_dms' ), 'group' ),
			'content' 	=> ht_dms_paginated_view_container( 'users_groups', $paginated_view_args )
		),
		array (
			'label'   => ht_dms_add_icon( __( 'Public Groups In Organization', 'ht_dms' ), array( 'public', 'group' ) ),
			'content' => ht_dms_paginated_view_container( 'public_groups', $paginated_view_args )
		),

	);

	if ( HT_DEV_MODE ) {
		$tabs[] = array (
			'label'   => ht_dms_add_icon( __( 'Assigned Tasks In This Organization', 'ht_dms' ), 'task' ),
			'content'	=> ht_dms_paginated_view_container( 'assigned_tasks', $paginated_view_args )
		);
	}

	$is_facilitator = $org_class->is_facilitator( $id, $uID );

	if ( $is_facilitator ) {
		$tabs[] = array (
			'label'   => ht_dms_add_icon( __( 'New Group In Organization', 'ht_dms' ), array( 'new', 'group') ),
			'content' => $ui->add_modify()->new_group(  $id, $uID ),
		);
		$tabs[] = array(
			'label'		=> ht_dms_add_icon( __( 'Edit Organization', 'ht_dms' ), array( 'edit', 'organization') ),
			'content'	=> $ui->add_modify()->edit_organization( $id, $uID, $obj ),
		);
		$tabs[] = array(
			'label'		=> ht_dms_add_icon( __( 'Invite Members', 'ht_dms' ), array( 'new', 'user') ),
			'content'	=> $ui->add_modify()->invite_member( $id, $obj, false ),
		);
	}



}
else {
	$tabs = array(
		'label'		=> get_the_title( $id ),
		'content'	=> __( 'You must be a member of this organization to view it\'s content', 'ht_dms' ),
	);
}

return $ui->elements()->output_container( $tabs );
