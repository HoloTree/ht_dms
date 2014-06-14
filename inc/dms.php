<?php
/**
 * Get DMS classes or objects from class.
 *
 * @package   @holotree_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

/**
 * Return decision object or field array
 *
 * @param 	int|bool	$id 	Optional. ID of decision. Default is true, which returns complete object for post type.
 * @param 	obj|null	$obj	Optional. Prebuilt Object. If is valid Pods object, <em>of any Pod</em> this object is returned and no other parameters matter. If not is new object is created.
 * @param 	bool		$cached	Optional. Whether to get cached value, if possible. Default is true.
 * @param	bool		$fields Optional. Whether to return field array instead of object. Default is false, which returns object.
 * @return 	obj|array			Decision Pods object or field array.
 *
 * @since 	0.0.1
 */
function holotree_decision( $id = true, $obj = null, $cached = true, $fields = false ) {
	$dms_decision = holotree_decision_class();
	$decision = $dms_decision->decision( $id, $obj, null, $cached, $fields );

	return $decision;
}

/**
 * Get an instance of the Decision class
 *
 * @return Class Instance
 *
 * @since	0.0.1
 */
function holotree_decision_class() {

	return decision::init();
	//return htdms\decision::init();

}

/**
 * Return group object or field array
 *
 * @param 	int|bool	$id 	Optional. ID of group. Default is true, which returns complete object for post type.
 * @param 	obj|null	$obj	Optional. Prebuilt Object. If is valid Pods object, <em>of any Pod</em> this object is returned and no other parameters matter. If not is new object is created.
 * @param 	bool		$cached	Optional. Whether to get cached value, if possible. Default is true.
 * @param	bool		$fields Optional. Whether to return field array instead of object. Default is false, which returns object.
 *
 * @return 	obj|array			Group Pods object or field array.
 *
 * @since 	0.0.1
 */
function holotree_group( $id = true, $obj = null, $cached = true, $fields = false ) {
	$dms_group = holotree_group_class();
	$group = $dms_group->group( $id, $obj, null, $cached, $fields );

	return $group;
}

/**
 * Get an instance of the Group class
 *
 * @return Class Instance
 *
 * @since	0.0.1
 */
function holotree_group_class() {

	return group::init();
	//return ht_dms\group::init();

}

/**
 * Get an instance of the Task class
 *
 * @return 	HoloTree_DMS_Task
 *
 * @return Class Instance
 *
 * @since	0.0.1
 */
function holotree_task_class() {

	return task::init();
	//return ht_dms\task::init();

}

/**
 * Return task object or field array
 *
 * @param 	int|bool	$id 	Optional. ID of task. Default is true, which returns complete object for post type.
 * @param 	obj|null	$obj	Optional. Prebuilt Object. If is valid Pods object, <em>of any Pod</em> this object is returned and no other parameters matter. If not is new object is created.
 * @param 	bool		$cached	Optional. Whether to get cached value, if possible. Default is true.
 * @param	bool		$fields Optional. Whether to return field array instead of object. Default is false, which returns object.
 *
 * @return 	obj|array			Task Pods object or field array.
 *
 * @since 	0.0.1
 */
function holotree_task( $id = true, $obj = null, $cached = true, $fields = false ) {
	$dms_task = holotree_task_class();
	$task = $dms_task->task( $id, $obj, null, $cached, $fields );

	return $task;
}

/**
 * Get an instance of the Consensus class
 *
 * @return Class Instance
 *
 * @since	0.0.1
 */
function holotree_consensus_class() {

	return  ht_dms\helper\consensus::init();

}

/**
 * Get a consensus or create one if it does not exist.
 *
 * @param 	int 	$dID ID of decision to get/ create for.
 *
 * @return 	array		The consensus array.
 *
 * @since 	0.0.1
 */
function holotree_consensus( $dID ) {
	return holotree_consensus_class()->consensus( $dID );

}

/**
 * Get an instance of the Consensus class
 *
 * @return Class Instance
 *
 * @since	0.0.1
 */
function holotree_organization_class() {

	return organization::init();
	//return ht_dms\organization::init();

}

/**
 * Return organization object or field array
 *
 * @param 	int|bool	$id 	Optional. ID of organization. Default is true, which returns complete object for post type.
 * @param 	obj|null	$obj	Optional. Prebuilt Object. If is valid Pods object, <em>of any Pod</em> this object is returned and no other parameters
 * @param 	bool		$cached	Optional. Whether to get cached value, if possible. Default is true.
 * @param	bool		$fields Optional. Whether to return field array instead of object. Default is false, which returns object.
 * matter. If not is new object is created.
 *
 * @return 	obj|array			Notification Pods object or field array.
 *
 * @since 	0.0.1
 */
function holotree_organization( $id = true, $obj = false, $cached = true, $fields = false ) {
	
	return holotree_organization_class()->organization( $id, $obj, null, $cached, $fields );

}

function holotree_membership_class() {

	return ht_dms\helper\membership::init();

}

/**
 * Get an instance of the DMS Common class
 *
 * @return Class Instance
 *
 * @since 	0.0.1
 */
function holotree_common_class() {

	return ht_dms\helper\common::init();

}

/**
 * Get an instance of the ui class.
 *
 * @return \ht_dms\ui\ui
 */
function holotree_dms_ui() {

	return ht_dms\ui\ui::init();

}
