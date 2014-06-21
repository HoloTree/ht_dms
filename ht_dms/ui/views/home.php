<?php
/**
 * @TODO What this does.
 *
 * @package   @TODO
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

$ui = holotree_dms_ui();

$uID = get_current_user_id();

$gObj = holotree_group_class()->object();



$tabs = array(
	array(
		'label'		=> __( 'My Groups', 'holotree' ),
		'content'	=> $ui->views()->users_groups( $gObj, $uID ),
	),
	array(
		'label'		=> __( 'My Organizations', 'holotree' ),
		'content'	=> $ui->views()->users_organizations( null, false, $uID ),
	),
	array(
		'label'		=> __( 'Assigned Tasks', 'holotree' ),
		'content'	=> $ui->views()->assigned_tasks( null, $uID ),
	),
	array(
		'label'		=> __( 'Notifications', 'holotree' ),
		//'content'	=> $ui->views()->notifications( null ),
		'content'	=> ':)',
	),
	array(
		'label'		=> __( 'New Organization', 'holotree' ),
		'content'	=> $ui->add_modify()->new_organization( $uID ),
	),
	array(
		'label'		=> __( 'All Public Groups', 'holotree' ),
		'content'	=> $ui->views()->public_groups( $gObj ),
	),


);

return $ui->elements()->tab_maker( $tabs );
