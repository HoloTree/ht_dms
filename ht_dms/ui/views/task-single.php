<?php


	$t = holotree_task_class();

	$obj = $t->item( get_queried_object_id() );
	$id = $obj->id();

	$dID = (int) $obj->display( 'decision.ID' );

	$ui = holotree_dms_ui();

	echo '<h2>'.$ui->elements()->title( $id, $obj, true  ).'</h2>';

	$tabs = array(
		array(
			'label' 	=> __( 'Task Details', 'holotree'),
			'content'	=> $ui->views()->task( $obj, $id  ),
		),
		array(
			'label' 	=> __( 'Task Documents', 'holotree'),
			'content'	=> $ui->views()->docs( $obj, 'task', $id ),
		),
		array(
			'label'		=> __( 'Decision', 'holotree' ),
			'content'	=> $ui->views()->decision( null, $dID ),

		),
		array(
			'label'		=> __( 'Edit Task', 'holotree' ),
			//'content'	=> $ui->add_modify()->edit_task( $id, $obj, $dID ),
			'content'  => '',
		),
	);

	echo $ui->elements()->output_container( $tabs );
