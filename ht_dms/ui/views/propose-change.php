<?php
if ( ! ht_dms_verify_action_nonce() ) {
	ht_dms_error( __( 'Alert, security nonce fail! Alert.', 'ht_dms' ) );
}

$class = ht_dms_decision_class();
$ui = ht_dms_ui();
$uID = get_current_user_id();

$id = pods_v_sanitized( 'dms_id' );

//@todo check if current user is a member of group

$tabs = array(
	array(
		'label' => ht_dms_add_icon( __( 'Propose Modification', 'ht-dms' ), array( 'new', 'modification' ) ),
		'content' => $ui->add_modify()->modify_decision( $id, null, $uID ),
	),
	array(
		'label' => ht_dms_add_icon( __( 'Decision Being Modified', 'ht-dms' ), 'decision' ),
		'content' => $ui->views()->decision(
			array(
				'id' => $id,
				'preview' => true,
				'return' => 'template'
			)
		),
	),
	array(
		'label' => ht_dms_add_icon( __( 'Discussion From Original Decision', 'ht-dms' ), 'discussion' ),
		'content' 	=> $ui->elements()->discussion( $id, 5, true ),
	),
);

return $ui->elements()->output_container( $tabs );
