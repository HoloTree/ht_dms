<?php
/**
 * Single group view.
 *
 * @package   @holotree
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link
 * @copyright 2014 Josh Pollock
 */

global $post;
$id = $post->ID;
$ui = holotree_dms_ui();
$g = holotree_group_class();
$obj = holotree_group( $id );
$uID = get_current_user_id();

$oID = (int) $obj->display( 'organization.ID' );

$statuses = array( 'New', 'Blocked', 'Passed' );
$tabs = $ui->build_elements()->decisions_by_status_tabs( $statuses, $id, null );


$tabs[] = array(
	'label'	 	=> ht_dms_add_icon( __( 'Discussion', 'holotree' ), 'discussion' ),
	'content' 	=> $ui->elements()->discussion( $id, 5, true ),
);
$tabs[] = array(
	'label'		=> ht_dms_add_icon( __( 'Membership', 'holotree' ), 'members' ),
	'content'	=> $ui->build_elements()->group_membership( $id, $obj ),
);
//only show edit group if member & facilitator.
if ( $g->is_member( $id, $uID, $obj ) && $g->is_facilitator( $id, $uID, $obj ) ) {
	$tabs[ ] = array (
		'label'   => ht_dms_add_icon( __( 'Edit Group', 'holotree' ), 'group' ),
		'content' => $ui->add_modify()->edit_group( $id, $obj ),
	);
}

//only allow add decision if is member
if ( $g->is_member( $id, $uID, $obj ) ) {
	$tabs[ ] = array (
		'label'   => __( 'Create New Decision', 'holotree' ),
		'content' => $ui->add_modify()->new_decision( null, $uID, $oID ),
	);
}

return $ui->elements()->output_container( $tabs );
