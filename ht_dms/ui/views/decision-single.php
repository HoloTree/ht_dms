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

global $post;
$id = $post->ID;
$d = holotree_decision_class();
$ui = holotree_dms_ui();

if ( $d->get_action_var() === 'changing' ) {
	return $ui->add_modify()->modify_decision( $id );

}
else {
	$obj = $d->item( $id );
	if ( !is_object( $obj) ) {
		holotree_error( 'Not an object!', __FILE__ );
	}
	else {
		$current = $ui->views()->decision( $obj, $id );
		$status = $obj->field( 'decision_status' );
		$status = strtolower( $status );
		if ( $status === 'new' ) {
			$status = 'open';
		}
		$what = $status.'-decision';
		$current .= $ui->views()->action_buttons( $obj, $id, $what );

		$tabs = array (
			array (
				'label'   => __( 'Decision Information', 'holotree' ),
				'content' => $current
			),
			array(
				'label'	 	=> __( 'Discussion' , 'holotree' ),
				'content' 	=> $ui->elements()->discussion( $id, 5, true ),
			),
			array(
				'label'	 	=> __( 'View Tasks' , 'holotree' ),
				'content' 	=> $ui->views()->decisions_tasks( null, $id ),
			),
			array (
				'label'   => __( 'Add Task', 'holotree' ),
				'content' => $ui->add_modify()->new_task(  null, null, $id ),
			),
			array (
				'label'   => __( 'Propose Modification', 'holotree' ),
				//'content' => $ui->add_modify()->modify_decision(  $id, $obj ),
			),
		);

		$proposed_modifications = $ui->views()->proposed_modifications( $id );
		if ( is_string( $proposed_modifications ) ) {
			$tabs[ ] = array (
				'label'   => __( 'Proposed Modifications', 'holotree' ),
				'content' => $proposed_modifications,
			);
		}

		return $ui->elements()->tab_maker( $tabs );

	}
}
