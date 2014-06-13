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
		add_action( 'wp_enqueue_scripts', array( $this, 'form_style' ) );
	}

	/**
	 * New decision form
	 *
	 * @return 	string    Form
	 *
	 * @since 	0.0.1
	 */
	function new_decision ( $oID = null, $obj = null ) {
		
		return holotree_decision_class()->new_decision( null, $obj, $oID );

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
	function edit_decision( $id, $oID = null, $obj = null  ) {

		return holotree_decision_class()->new_decision( $id, $obj, $oID );

	}

	/**
	 * Modify Decision Form
	 *
	 * @param 	int			$id			ID of decision to propose modification to.
	 * @param	obj|null	$single_obj	Optional. Decision object of single item that is being modified. If isn't a Pods Object for whole class, bad things will happen.
	 * @param	obj|null	$full_obj	Optional. Full decisions object. If isn't a Pods Object for whole class, bad things will happen.
	 *
	 * @return 	string		Form
	 *
	 * @since 	0.0.1
	 */
	function modify_decision( $id, $single_obj = null, $full_object = null ) {
		
		$dms_decision = holotree_decision_class();
		$form = $dms_decision->propose_modify( $id );
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
	function new_group( $oID, $uID = null, $obj = null ) {
		
		return holotree_group_class()->new_group( null, $uID, $obj, $oID );

	}

	/**
	 * Edit group form
	 *
	 * @return 	string
	 *
	 * @since 	0.0.1
	 */
	function edit_group( $id, $oID = null, $uID = null, $obj = null  ) {
		
		return holotree_group_class()->new_group( $id, $uID, $obj, $oID );

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
	function new_task( $dID, $oID = null ) {
		
		$form = holotree_task_class()->new_task( null, $dID, $oID );

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
	function edit_task( $dID, $id, $oID = null ) {
		
		return holotree_task_class()->new_task( $dID, $id, $oID );

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
	function edit_organization( $id, $uID = null, $obj = null ) {

		return holotree_organization_class()->edit( $id, $uID, $obj );
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

	function form_style() {
		wp_enqueue_style( 'pods-form', false, array(), false, false );
	}


} 
