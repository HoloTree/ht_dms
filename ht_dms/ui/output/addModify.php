<?php
/**
 * Return
 */

namespace ht_dms\ui\output;

class addModify {

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

		return ht_dms_decision_class()->edit( null, $uID, $obj, $oID );

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

		return ht_dms_decision_class()->edit( $id, null, $ob, $oID );

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
		
		$dms_decision = ht_dms_decision_class();
		$form = $dms_decision->propose_modify( $id, $obj, $uID );
		$out = '<div class="modify-decision" id="modify-'.esc_attr( $id ).'">';
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
		
		return ht_dms_group_class()->edit( null, $uID, $obj, $oID );

	}

	/**
	 * Edit group form
	 *
	 * @return 	string
	 *
	 * @since 	0.0.1
	 */
	function edit_group( $id, $obj = null, $oID = null ) {
		
		return ht_dms_group_class()->edit( $id, null, $obj, $oID );

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
		
		$form = ht_dms_task_class()->edit( null, $uID, $obj, $oID, $dID );

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
		
		return ht_dms_task_class()->new_task( $id, null, $obj, $dID, $oID );

	}

	/**
	 * Form for creating a new organization
	 *
	 * @return 	string			The form.
	 *
	 * @since 	0.0.1
	 */
	function new_organization( $uID = null ) {
		return ht_dms_caldera_loader( $this->get_form_class()->new_organization_form_id );
		/**
		 * Filter to swap out new organization form.
		 *
		 * @param null|string $form the form
		 *
		 * @since 0.0.3
		 */
		$alt_org_edit_form = apply_filters( 'ht_dms_new_organization_form', null, $uID, null );

		if ( is_string( $alt_org_edit_form ) ) {

			return $alt_org_edit_form;

		}

		$type = HT_DMS_ORGANIZATION_POD_NAME;
		add_filter( "ht_dms_{$type}_edit_form_fields", function( $form_fields  ) {
			return  array( 'fields' => $form_fields, 'fields_only' => true );
		});

		$form =  ht_dms_organization_class()->edit( null, $uID );
		$form .= $this->invite_code_fields();
		$form .= $this->new_organization_submit( $uID );
		$form = sprintf( '<form action="%0s" method="POST" class="pods-form pods-form-front" id="new-organization" >%1s</form>', home_url(), $form );

		return $form;

	}

	/**
	 * Form for editing organization details.
	 *
	 * Currently just location. Will do more later.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	public function organization_details( ) {
		return ht_dms_caldera_loader( $this->get_form_class()->organization_details_form_id );

	}
	/**
	 * Holds an instance of the form class
	 *
	 * @var \ht_dms\helper\caldera\forms
	 */
	protected static $form_class;

	/**
	 * Get an instance of the form class.
	 *
	 * @since  0.3.0
	 *
	 * @access protected
	 *
	 * @return \ht_dms\helper\caldera\forms
	 */
	protected static function get_form_class() {
		if ( is_null( self::$form_class ) ) {
			self::$form_class = new \ht_dms\helper\caldera\forms();
		}

		return self::$form_class;
	}

	/**
	 * Fields for the adding invite code
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	private function invite_code_fields() {
		$invite = 'invite';
		$invite_label = __( 'Invite Code', 'ht-dms' );
		$field = sprintf( '<div class="pods-field-label">%1s</div>', \PodsForm::label( $invite, $invite_label ) );
		$field .= sprintf( '<div class="pods-field-input">%1s</div>', \PodsForm::field( $invite, '' ) );
		$message = __( 'Creating a new organization requires an invite code. Please enter it below:', 'ht-dms' );
		$message_container = sprintf( '<p id="invite-code-message">%1s</p>', $message );
		return sprintf( '%0s<ul class="pods-form-fields">%1s</ul>',
			$message_container,
			sprintf( '<li class="pods-field">%1s</li>', $field )
		);
	}

	/**
	 * Create submit button for create org field
	 *
	 * @since 0.1.0
	 *
	 * @param int $uID Current user ID
	 *
	 * @return string
	 */
	private function new_organization_submit( $uID ) {
		$text = __( 'Create', 'ht-dms' );
		return sprintf( '%0s<p class="new-org-submit" style="float:right;margin:%0s;">%1s</p>',
			sprintf( '<p id="new-org-message" style="display:none;"></p> %1s',
				ht_dms_spinner( true, 'new-org-spinner' )
			),
			'1%',
			\PodsForm::submit_button( $text, 'primary large', 'create-org-submit', false, array( 'user' => $uID )
			)
		);

	}

	/**
	 * Form for editing an organization
	 *
	 * @return 	string			The form.
	 *
	 * @since 	0.0.1
	 */
	function edit_organization( $id, $obj = null, $oID = null ) {

		return ht_dms_organization_class()->edit( $id, null, $obj, $oID );
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
	 * Form for adding document to decision or task
	 *
	 * @param int $id Task or document ID.
	 *
	 * @return string
	 *
	 * @since 0.0.3
	 */
	function add_doc( $id ) {
		$form = false;
		$post = get_post( $id );
		if ( ! empty( $post ) && in_array( $post->post_type, array( HT_DMS_DECISION_POD_NAME, HT_DMS_GROUP_POD_NAME ) ) ) {
			$form = pods( $post->post_type, $id );

			if ( $post->post_type == HT_DMS_DECISION_POD_NAME ) {
				$group = (int) $form->display( 'group.ID' );
			}
			else {
				$group = (int )$form->display( 'decision_group' );
			}

			$fields_only = true;
			$is_member = false;
			if ( is_int( $group ) ) {
				$is_member = ht_dms_group_class()->is_member( $group );
				$fields_only = false;
			}

			$params = array(
				'fields' => array( 'documents' ),
				'fields_only' => $fields_only,
			);
			$form = $form->form( $params, __( 'Done Adding Documents', 'ht_dms' ), get_permalink( $id ) );

		}

		if ( ! isset( $is_member ) || ! $is_member ) {
			$form .= '<script>jQuery( ".pods-media-add" ).remove();</script>';
		}

		return $form;

	}

	function invite_member( $id, $obj = null, $group = true ){
		//$out[] = ht_dms_membership_class()->invite_existing( $id, $obj = null, $group = true );
		$caldera_id = ht_dms_ui()->caldera_actions()->invite_new_user_form_id;
		$out[] = ht_dms_caldera_loader( $caldera_id );
		$container_id = "invite-members-{$id}";

		return sprintf( '<div class="invite-members" id="%1s">%2s</div>', esc_attr( $container_id ), implode( $out ) );

	}

	/**
	 * Get instance of UI class
	 *
	 * @return 	\ht_dms\ui
	 *
	 * @since 	0.0.1
	 */
	function ui(){
		$ui = ht_dms_ui();

		return $ui;

	}


} 
