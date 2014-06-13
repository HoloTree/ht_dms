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


if ( $org_class->is_member( $id, $uID, $obj ) || $org_class->open_access( $id, $obj )   ) {
	$ui = holotree_dms_ui();
	$gObj = pods( HT_DMS_GROUP_CPT_NAME );

	$gObj = $gObj->find( array( 'where' => 'organization.ID = "'.$id.'" ' ) );

	//@TODO Make each of these work right here and in home.php given organizations.
	$tabs = array (
		array (
			'label'   => __( 'My Groups In Organization', 'holotree' ),
			'content' => '',
			//'content' => $ui->views()->group_loop( $gObj, 5, true, false, $id ),
		),
		array (
			'label'   => __( 'Public Groups In Organization', 'holotree' ),
			'content' => $ui->views()->group_loop( $gObj, 5, false, true, $id ),
		),
		//@TODO My in org or loose?
		array (
			'label'   => __( 'Assigned Tasks In This Organization', 'holotree' ),
			'content' => $ui->views()->all_tasks( false, null, $uID, 5, true, $id ),
		),
		array (
			'label'   => __( 'New Group In Organization', 'holotree' ),
			'content' => $ui->add_modify()->new_group( $id, $uID, null ),
		),
		array(
			'label'		=> __( 'Edit Organization', 'holotree' ),
			'content'	=> $ui->add_modify()->edit_organization( $id, $uID, $obj ),
		),

	);

	return $ui->elements()->tab_maker( $tabs );

}
else {
	return 'NEED A NO ACCESS SOMETHING!?';
}
