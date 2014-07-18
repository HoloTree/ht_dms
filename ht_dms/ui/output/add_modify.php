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

namespace ht_dms\ui\output;

class add_modify {

	function __construct() {

	}

	/**
	 * New decision form
	 *
	 * @return 	string    Form
	 *
	 * @since 	0.0.1
	 */
	function new_decision ( $obj = null, $uID = null, $oID = null   ) {

		return holotree_decision_class()->edit( null, $uID, $obj, $oID );

	}

	/**
	 * Edit decision form
	 *
	 * @todo Will this ever get used vs $this->modify_decision() ??
	 *
	 * @return 	string    Form
	 *
	 * @since 	0.0.1
	 */
	function edit_decision( $id, $obj = null, $oID = null  ) {

		return holotree_decision_class()->edit( $id, null, $ob, $oID );

	}

	/**
	 * Modify Decision Form
	 *
	 * @param 	int			$id			ID of decision to propose modification to.
	 * @param	obj|Pods	$obj
	 * @param	int|null	$uID
	 *
	 * @return 	string		Form
	 *
	 * @since 	0.0.1
	 */
	function modify_decision( $id, $obj = null, $uID = null ) {
		
		$dms_decision = holotree_decision_class();
		$form = $dms_decision->propose_modify( $id, $obj, $uID );
		$out = '<div class="modify-decision" id="modify-'.$id.'">';
		$out .= $form;
		$out .= '</div><!--.modify-decision-->';

		return $out;
	}

	/**
	 * Create group form
	 *
	 * @return 	string
	 *
	 * @since 	0.0.1
	 */
	function new_group( $obj = null, $uID = null, $oID = null) {
		
		return holotree_group_class()->edit( null, $uID, $obj, $oID );

	}

	/**
	 * Edit group form
	 *
	 * @return 	string
	 *
	 * @since 	0.0.1
	 */
	function edit_group( $id, $obj = null, $oID = null ) {
		
		return holotree_group_class()->edit( $id, null, $obj, $oID );

	}

	/**
	 * Form for creating a new task
	 *
	 * @param 	int		$dID	ID of decision to add task to.
	 *
	 * @return 	string			The form.
	 *
	 * @since 	0.0.1
	 */
	function new_task( $obj = null, $uID = null, $dID, $oID = null ) {
		
		$form = holotree_task_class()->edit( null, $uID, $obj, $dID, $oID );

		return $form;
	}

	/**
	 * Form for editing a new task
	 *
	 * @param 	int		$dID	ID of decision to add task to.
	 *
	 * @return 	string			The form.
	 *
	 * @since 	0.0.1
	 */
	function edit_task( $id, $obj = null, $dID, $oID = null ) {
		
		return holotree_task_class()->new_task( $id, null, $obj, $dID, $oID );

	}

	/**
	 * Form for creating a new organization
	 *
	 * @return 	string			The form.
	 *
	 * @since 	0.0.1
	 */
	function new_organization( $uID = null, $obj = null ) {

		return holotree_organization_class()->edit( null, $uID, $obj );

	}

	/**
	 * Form for editing an organization
	 *
	 * @return 	string			The form.
	 *
	 * @since 	0.0.1
	 */
	function edit_organization( $id, $obj = null, $oID = null ) {

		return holotree_organization_class()->edit( $id, null, $obj, $oID );
	}

	/**
	 * Form for creating a notification
	 *
	 * @return string
	 *
	 * @since 0.0.1
	 */
	function new_notification() {

		return holotree_notification_class()->new_notificiation();

	}

	/**
	 * Get instance of UI class
	 *
	 * @return 	\holotree\ui
	 *
	 * @since 	0.0.1
	 */
	function ui(){
		$ui = holotree_dms_ui();

		return $ui;

	}


} 
