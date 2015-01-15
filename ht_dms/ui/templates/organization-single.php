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

$tabs = array();

$count = $org_class->group_count( $id, $obj );
$is_member = $org_class->is_member( $id, $uID, $obj );
$is_facilitator = $org_class->is_facilitator( $id, $uID );
$facilitator_tabs = array();
$paginated_view_args = array();

if ( $is_facilitator ) {
	$facilitator_tabs = $ui->output_elements()->organization_facilitator_tabs( $id, $uID, $obj );
}

if ( $is_facilitator && 0 === $count  ) {
	$tabs = array_merge( $tabs, $facilitator_tabs );
}
else {
	if ( $count > 0 || HT_DEV_MODE ) {
		$paginated_view_args = ht_dms_default_paginated_view_arguments( array( 'oID' => $id ) );
	}

	if ( $count > 0 && ( $is_member || $org_class->open_access( $id, $obj ) ) ) {

		$tabs[] =array (
				'label'   => ht_dms_add_icon( __( 'My Groups In Organization', 'ht_dms' ), 'group' ),
				'content' 	=> ht_dms_paginated_view_container( 'users_groups', $paginated_view_args )
		);
		$tabs[] = array (
				'label'   => ht_dms_add_icon( __( 'Public Groups In Organization', 'ht_dms' ), array( 'public', 'group' ) ),
				'content' => ht_dms_paginated_view_container( 'public_groups', $paginated_view_args )
		);

		if ( ht_dms_task_mode() ) {
			$tabs[] = array (
				'label'   => ht_dms_add_icon( __( 'Assigned Tasks In This Organization', 'ht_dms' ), 'task' ),
				'content'	=> ht_dms_paginated_view_container( 'assigned_tasks', $paginated_view_args )
			);
		}

		if ( $is_facilitator ) {
			$tabs = array_merge( $tabs, $facilitator_tabs );
		}

	}
	else {
		$tabs[] = array(
			'label'		=> get_the_title( $id ),
			'content'	=> __( 'You must be a member of this organization to view it\'s content', 'ht_dms' ),
		);
	}
}



return $ui->elements()->output_container( $tabs );
