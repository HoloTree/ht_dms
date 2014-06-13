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

$ui = holotree_dms_ui();

$uID = get_current_user();
$tabs = array( array(
		'label'		=> __( 'All Public Groups', 'holotree' ),
		'content'	=> $ui->views()->group_loop( null, 5, false, null, true ),
	), array(
		'label'		=> __( 'My Groups', 'holotree' ),
		'content'	=> $ui->views()->group_loop( null, 5, true, $uID, false ),
	), array(
		'label'		=> __( 'New Group', 'holotree' ),
		'content'	=> $ui->add_modify()->new_group(),
	),

);
return $ui->elements()->tab_maker( $tabs );
