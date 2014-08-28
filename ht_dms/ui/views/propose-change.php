<?php
$class = holotree_decision_class();
$ui = holotree_dms_ui();
$uID = get_current_user_id();

$id = pods_v( 'dms_id' );

//@todo check if current user is a member of group

$tabs = array(
	array(
		'label' => __( 'Propose Modification', 'holotree' ),
		'content' => $ui->add_modify()->modify_decision( $id, null, $uID ),
	),
	array(
		'label' => __( 'Decision Being Modified' ),
		'content' => '@TODO',
	),
	array(
		'label' => __( 'Discussion From Original Decision' ),
		'content' => '@TODO',
	),
);

return $ui->elements()->output_container( $tabs );
