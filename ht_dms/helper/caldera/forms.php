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

namespace ht_dms\helper\caldera;


class forms {
	public $join_group_form_id = 'CF54138690b504b';
	public $leave_group_form_id = 'CF5413964215412';
	public $group_pending_form_id = 'CF5413972657523';
	public $decision_actions_form_id = 'CF5411fb087123d';
	public $invite_new_user_form_id = 'CF5441caf7dddf7';

	/**
	 * The ID for the new organization form.
	 *
	 * @since 0.3.0
	 *
	 * @var string
	 */
	public $new_organization_form_id = 'CF54b747e8a3941';

	/**
	 * The ID for the organization details editor form.
	 *
	 * @since 0.3.0
	 *
	 * @var string
	 */
	public $organization_details_form_id = 'CF54bb176a217ad';

	/**
	 * Get the ID for a form
	 *
	 * @param $form
	 *
	 * @return string
	 *
	 * @since 0.0.3
	 */
	function form_id( $form ) {
		if ( isset( $this->$form ) ){

			return $this->$form;
		}

		$form = $form.'_id';

		return $this->$form;

	}

	function membership_forms() {
		return array( $this->form_id( 'join_group_form' ), $this->form_id( 'leave_group_form', $this->form_id( 'group_pending_form' ) ) );
	}

} 
