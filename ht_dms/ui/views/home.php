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

$paginated_view_args = ht_dms_default_paginated_view_arguments();

$tabs = array(
	array(
		'label'		=> __( 'My Groups', 'holotree' ),
		'content' 	=> ht_dms_paginated_view_container( 'users_groups', $paginated_view_args )
	),
	array(
		'label'		=> __( 'My Organizations', 'holotree' ),
		'content' 	=> ht_dms_paginated_view_container( 'users_organizations', $paginated_view_args )
	),
	array(
		'label'		=> __( 'Assigned Tasks', 'holotree' ),
		'content'	=> ht_dms_paginated_view_container( 'assigned_tasks', $paginated_view_args )
	),
	array(
		'label'		=> __( 'All Public Groups', 'holotree' ),
		'content' 	=> ht_dms_paginated_view_container( 'public_groups', $paginated_view_args )
	),


);

if ( HT_DEV_MODE ) {
	$tabs[] = array(
		'label'		=> __( 'New Organization', 'holotree' ),
		'content'	=> $ui->add_modify()->new_organization( null, $uID ),
	);
}

return $ui->elements()->output_container( $tabs );
