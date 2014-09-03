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

if ( HT_DEV_MODE ) {
	$link = get_post_type_archive_link( HT_DMS_DECISION_CPT_NAME );
	echo '<a href="' . $link . '">Decisions</a><br>';
	$link = get_post_type_archive_link( HT_DMS_GROUP_CPT_NAME );
	echo '<a href="' . $link . '">Groups</a><br>';
}

global $post;
$id = $post->ID;

//@TODO Use where/or select to only get the groups/tasks in organziation
$obj = holotree_organization( $id );
$org_class = holotree_organization_class();

$uID = get_current_user_id();
$ui = holotree_dms_ui();

$paginated_view_args = ht_dms_default_paginated_view_arguments( array( 'oID' => $id ) );

if ( $org_class->is_member( $id, $uID, $obj ) || $org_class->open_access( $id, $obj )   ) {

	$tabs = array (

		array (
			'label'   => __( 'My Groups In Organization', 'holotree' ),
			'content' 	=> ht_dms_paginated_view_container( 'users_groups', $paginated_view_args )
		),
		array (
			'label'   => __( 'Public Groups In Organization', 'holotree' ),
			ht_dms_paginated_view_container( 'public_groups', $paginated_view_args )
		),
		array (
			'label'   => __( 'Assigned Tasks In This Organization', 'holotree' ),
			'content'	=> ht_dms_paginated_view_container( 'assigned_tasks', $paginated_view_args )
		),

	);

	$is_facilitator = $org_class->is_facilitator( $id, $uID );

	if ( $is_facilitator ) {
		$tabs[] = array (
			'label'   => __( 'New Group In Organization', 'holotree' ),
			'content' => $ui->add_modify()->new_group(  $id, $uID ),
		);
		$tabs[] = array(
			'label'		=> __( 'Edit Organization', 'holotree' ),
			'content'	=> $ui->add_modify()->edit_organization( $id, $uID, $obj ),
		);
	}



}
else {
	$tabs = array(
		'label'		=> get_the_title( $id ),
		'content'	=> __( 'You must be a member of this organization to view it\'s content', 'holotree' ),
	);
}

return $ui->elements()->output_container( $tabs );
