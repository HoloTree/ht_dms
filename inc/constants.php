<?php
/**
 * Define HoloTree DMS constants
 *
 * @package   @holotree_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */


if ( !defined ( 'HT_DMS_DIR' )  ) {
	define( 'HT_DMS_DIR', trailingslashit( HT_DMS_ROOT_DIR ) . 'dms' );
}

if ( !defined ( 'HT_DMS_UI_DIR' ) && defined( 'HT_DMS_DIR' ) ) {
	define( 'HT_DMS_UI_DIR', trailingslashit( HT_DMS_DIR ) . 'ui' );
}

if ( !defined( 'HT_DMS_VIEW_DIR' ) && defined( 'HT_DMS_DIR' ) ) {
	define( 'HT_DMS_VIEW_DIR', trailingslashit( HT_DMS_UI_DIR ) . 'views' );
}

if ( !defined( 'HT_DMS_DECISION_CPT_NAME' ) ) {
	define( 'HT_DMS_DECISION_CPT_NAME', 'ht_dms_decision' );
}

if ( !defined( 'HT_DMS_GROUP_CPT_NAME' ) ) {
	define( 'HT_DMS_GROUP_CPT_NAME', 'ht_dms_group' );
}

if ( !defined( 'HT_DMS_TASK_CT_NAME' ) ) {
	define( 'HT_DMS_TASK_CT_NAME', 'ht_dms_task' );
}

if ( !defined( 'HT_DMS_NOTIFICATION_NAME' ) ) {
	define( 'HT_DMS_NOTIFICATION_NAME', 'ht_dms_notification' );
}

if ( !defined( 'HT_DMS_ORGANIZATION_NAME' ) ) {
	define( 'HT_DMS_ORGANIZATION_NAME', 'ht_dms_organization' );
}

if ( !defined( 'HT_HT_DMS_USE' ) ) {
	define( 'HT_HT_DMS_USE', true );
}
