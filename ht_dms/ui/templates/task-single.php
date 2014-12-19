<?php
	$t = ht_dms_task_class();

	$obj = $t->item( get_queried_object_id() );
	$id = $obj->id();

	$dID = (int) $obj->display( 'decision.ID' );

	$ui = ht_dms_ui();

	$tabs = array(
		array(
			'label' 	=> ht_dms_add_icon( __( 'Task Details', 'ht_dms' ), 'details' ),
			'content'	=> $ui->views()->task( $obj, $id  ),
		),
		array(
			'label' 	=> ht_dms_add_icon ( __( 'Task Documents', 'ht_dms' ), 'docs' ),
			'content'	=> $ui->views()->docs( $obj, $id, 'task' ),
		),
		array(
			'label'		=> ht_dms_add_icon( __( 'Decision', 'ht_dms' ), 'decision' ),
			'content'	=> $ui->views()->decision( null, $dID ),

		),
		array(
			'label'		=> ht_dms_add_icon( __( 'Edit Task', 'ht_dms' ), array( 'edit', 'task' ) ),
			'content'  => '',
		),
	);

	return $ui->elements()->output_container( $tabs );
