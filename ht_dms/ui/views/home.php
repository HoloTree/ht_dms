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
		'content'	=> $ui->views()->group_loop( $gObj, 5, true ),
	),
	array(
		'label'		=> __( 'My Organizations', 'holotree' ),
		'content'	=> '\w/',
	),
	array(
		'label'		=> __( 'Assigned Tasks', 'holotree' ),
		'content'	=> $ui->views()->all_tasks( false, null, (int) get_current_user_id(), 5, false, false ),
	),
	array(
		'label'		=> __( 'Notifications', 'holotree' ),
		//'content'	=> $ui->views()->notifications( null ),
		'content'	=> ':)',
	),
	array(
		'label'		=> __( 'New Organization', 'holotree' ),
		'content'	=> $ui->add_modify()->new_organization(),
	),
	array(
		'label'		=> __( 'All Public Groups', 'holotree' ),
		'content'	=> $ui->views()->group_loop( $gObj, 5, false ),
	),

);
return $ui->elements()->tab_maker( $tabs );
