<?php
/**
 * Abstract classes that main classes of this plugin extend.
 *
 * @package   @holotree_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\dms;

abstract class dms extends object {

	/**
	 * Get item of this type
	 *
	 * @todo need this?
	 *
	 * @param null $obj
	 * @param null $id
	 * @param null $params
	 * @param bool $cached
	 * @param bool $fields
	 *
	 * @return bool|Pods|mixed|null|void
	 *
	 * @since 0.0.1
	 */
	function item( $id = null, $obj = null, $params = null, $cached = true, $fields = false ) {

		if ( is_int( $params ) || intval( $params ) > 1 || is_null( $params ) || !is_array( $params ) ) {
			$params = (int) $id;
		}

		$obj = $this->null_object( $obj, $params );

		if ( $fields ) {
			if ( ! $this->field_loop( $id, $obj ) ) {

				if ( $this->fields_to_loop() !== false ) {
					$fields_to_loop = $this->fields_to_loop();
				}
				else {
					foreach( $obj->fields() as $name => $data ) {
						$fields_to_loop[ $name ] = $name;
					}

				}

				foreach( $fields_to_loop as $name  ) {
					$the_fields[ $name ] = $obj->field ( $name );
				}

				return $the_fields;

			}
			else {

				return $this->field_loop( $id, $obj );

			}
		}

		return $obj;

	}

	/**
	 * Use magical __call to turn type functions into $this->item()
	 *
	 * For example, allows $this->group() to pass through to $this->item()
	 *
	 * @param 	$method
	 * @param 	$args
	 *
	 * @return 	bool|mixed|null|Pods|void
	 *
	 * @since	0.0.1
	 */
	function __call( $method, $args ) {

		if ( $method === $this->short_name() ) {

			if ( !isset( $args[4] ) ) {
				$args[4] = null;
			}

			return $this->item( $args[0], $args[1], $args[2], $args[3], $args[4] );

		}

	}

	/**
	 * Removes prefix from $this->get_type()
	 *
	 * Example if prefix is "ht_dms" and type is "ht_dms_group", "group" is returned.
	 *
	 * @return    string
	 *
	 * @since	0.0.1
	 */
	function short_name() {
		//@TODO ht_dms prefix needs to be set dynamically everywhere.
		$prefix = 'ht_dms';
		$prefix = $prefix.'_';
		return str_replace( $prefix, '', $this->get_type() );

	}

	/**
	 * Edit or create items of this type
	 *
	 * @param 	int|null	$id
	 * @param 	int|nulll 	$uID
	 * @param 	int|null 	$obj
	 * @param 	int|null	$oID
	 * @param 	int|null	$dID
	 * @param 	string|bool $post_title_label
	 *
	 * @return 	null|string
	 *
	 * @since 	0.0.1
	 */
	function edit( $id = null, $uID = null, $obj = null, $oID = null, $dID = null,  $post_title_label = false ) {
		$uID = $this->null_user( $uID );

		$type = $this->get_type( false );

		$new = false;
		if ( is_null( $id )  ) {
			$new = true;
		}

		$params = null;
		if ( ! $new && $this->get_type() !== HT_DMS_TASK_POD_NAME ) {
			$params = array( 'where' => 't.id = " ' . $id . ' " ' );
		}
		else{
			$params = $id;
		}

		$params = apply_filters( 'ht_dms_edit_params', $params, $type );

		$obj = $this->null_object( $obj, $params );

		if ( ! $post_title_label ) {
			$post_title_label = $this->display_names( $type );
			$post_title_label = $post_title_label. ' Name';
		}
		$form_fields[ 'post_title' ] = array( 'label' => $post_title_label );

		$fields = (array) $this->fields_to_loop( $obj, false );

		foreach ( $fields as $k => $v ) {
			if ( is_array( $v ) ) {
				$label =  pods_v( 'label', $v, '' );
			}
			else {
				$label = $v;
			}

			$form_fields[ $k ] = array( 'label' => $label );

		}

		if ( $new ) {
			if ( isset( $form_fields[ 'members' ] ) ) {
				unset( $form_fields[ 'members' ] );
			}

			$initial_members = apply_filters( 'ht_dms_initial_members', $uID, $type );

			$form_fields[ 'members' ] = array ( 'default' => $initial_members );
		}

		global $post;
		$gID = $dID = false;

		if ( HT_DMS_DECISION_POD_NAME === $type ) {
			if ( is_null( $id ) ) {
				if ( ht_dms_is_decision( $post->ID )) {
					$dID = $post->ID;
				}
			}
			else {
				$dID = $id;
			}

			if ( ht_dms_integer( $dID ) ) {
				$gID = $this->get_group( $dID );
			}




		}

		if ( HT_DMS_GROUP_POD_NAME === $type || false == $dID ) {
			if ( ht_dms_is_group( $post->ID ) ) {
				$gID = $post->ID;
			}
		}

		if ( is_null( $oID )  && $type !== HT_DMS_ORGANIZATION_POD_NAME  ) {
			//find what content type we're on
			$calling_type = ht_dms_get_content_type();

			if ( in_array( $calling_type, $this->content_types()) ) {
				if ( $calling_type === HT_DMS_ORGANIZATION_POD_NAME ) {

					$oID = $post->ID;
				}
				else {
					global $post;
					$pods = pods( $calling_type, $post->ID );
					$oID = $pods->field( 'organization.ID' );
					$form_fields[ 'organization' ] = $oID;
					unset( $pods );
				}
			}
			else {
					ht_dms_error( __( sprintf( 'When using %d in this context you must specify organization ID in $oID', __METHOD__ ), 'ht_dms' ) );
			}

		}
		elseif (  ! $new && $type !== HT_DMS_ORGANIZATION_POD_NAME ) {
			if (  ! isset( $oID ) || ! $oID  )  {
				ht_dms_error( );
			}

		}

		if ( $type === HT_DMS_TASK_POD_NAME ) {
			if ( !is_null( $dID ) ) {
				$form_fields[ 'decision' ][ 'default' ] = $dID;
			}
			else {
				if ( is_null( $id ) ) {
					ht_dms_error( __( 'Decision ID must be set when creating new tasks.', 'ht_dms' ) );
				}
			}

		}

		return $this->form( $obj, $form_fields, $new, $id, $obj, $oID, $uID, $type, $gID );

	}

	/**
	 * Add inline jQuery to fix form.
	 *
	 * Implement in inherited class.
	 *
	 * @return 	bool
	 *
	 * @todo remove this
	 *
	 * @since 	0.0.1
	 */
	function form_fix( $new = true, $type ) {
		return;
		
	}

	/**
	 * Loop to get values of all fields in CPT.
	 *
	 * Implement in inherited class.
	 *
	 * @param 	int|null 	$id
	 * @param 	obj		$obj
	 * @param	bool	$all	Optional. Whether to to return all fields or selected fields.
	 *
     * @return 	bool
	 *
	 * @since 	0.0.1
	 */
	function field_loop( $id = null, $obj, $all = false ) {

		return false;
	}

	/**
	 * Fields to loop when getting fields and in $this->edit()
	 *
	 * Implement in inherited class.
	 *
	 * @param 	obj		$obj
	 * @param	bool	$all	Optional. Whether to to return all fields or selected fields.
	 *
	 * @return 	bool
	 *
	 * @since 	0.0.1
	 */
	function fields_to_loop( $obj = null, $all = false ) {
		$obj = $this->null_object( $obj );

		/**
		 * Filter for setting the select fields, when all === false.
		 *
		 * @since 0.0.1
		 */
		$fields = apply_filters( "{$this->get_type()}_select_fields", null );

		if ( $all || is_null( $fields ) ) {
			$fields = $this->field_names( $obj, true );

		}

		return $fields;
	}

	/**
	 * Get field names for all fields of current Pod
	 *
	 * @since 0.3.0
	 *
	 * @param null $obj
	 * @param bool $full_array Optional. If false, the default, array of names is returned, if true the full fields array is returned.
	 *
	 * @return array
	 */
	function field_names( $obj = null, $full_array = false ) {
		$obj = $this->null_object( $obj );
		$fields = $obj->fields();

		if ( $full_array ) {
			return $fields;
		}

		$fields = wp_list_pluck( $fields, 'name' );

		return $fields;

	}

	/**
	 * Convert null value for user ID to current user ID.
	 *
	 * @param 	int|null $uID	Optional. A user ID.
	 *
	 * @return 	int				Same as input or current user ID if input is null.
	 *
	 * @since 	0.0.1
	 */
	function null_user( $uID ) {

		return ht_dms_null_user( $uID );

	}

	/**
	 * Update an item
	 *
	 *
	 * @param 	int 		$id 	ID of item to update
	 * @param 	string 		$field	Field to update.
	 * @param 	mixed		$value	New value
	 * @param	obj|null	$obj	Optional. Pods Obj
	 * @param	bool		$add	Optional. To add to existing values (true) or overwrite existing values (false).
	 *
	 * @return 	int 		ID
	 *
	 * @since 	0.0.1
	 */
	public function update( $id, $field, $value, $obj = null, $add = false ) {
		$obj = $this->null_object( $obj, $id );
		if ( $field === 'consensus' ) {
			$value = serialize( $value );
		}

		if ( $add ) {
			$old_values = $obj->field( $field );
			$value = array_merge( $old_values, $value );
		}

		$id = $obj->save( $field, $value );

		return $id;
	}

	/**
	 * Propose a modification to any organization, decision, group or task.
	 *
	 * NOTE: As of now, only works for decisions.
	 *
	 * @param 	int				$id 	ID of decision to propose modification to.
	 * @param 	obj|Pods|null	$obj	Optional. Single Pods object of item proposing modification to.
	 * @param 	int|null		$uID	Optional. ID of user proposing modification.
	 *
	 * @return 	null|string				Form for proposing modification
	 *
	 * @since	0.0.2
	 */
	function propose_modify( $id, $obj = null, $uID ) {
		$obj = $this->null_object( $obj, $id );

		$type = $obj->pod;

		if ( $type !== HT_DMS_DECISION_POD_NAME ) {
			ht_dms_error( __METHOD__, __('only supports decisions. For now...', 'ht_dms' ) );
		}

		$uID = $this->null_user( $uID );

		$old = $obj->fields();

		$manager = $obj->field( 'manager.ID' );

		unset( $old[ 'manager' ] );
		unset( $old[ 'reason_for_change' ] );
		unset( $old[ 'change_to' ] );
		unset( $old[ 'proposed_by' ] );
		unset( $old[ 'consensus' ] );
		unset( $old[ 'proposed_changes' ] );
		unset( $old[ 'tasks' ] );
		unset( $old[ 'group' ] );
		unset( $old[ 'organization' ] );
		unset( $old[ 'decision_type' ] );

		$form_fields[ 'post_title'][ 'default' ] = $obj->field('post_title' );

		foreach( $old as $field => $value ) {
			$form_fields[ $field ][ 'default' ] = $obj->field( $field );
		}

		$form_fields[ 'reason_for_change' ] = array(
			'required' => 'true'
		);

		unset($form_fields[ 'change_to' ]);
		$form_fields[ 'change_to' ] = array(
			'default' => $id
		);
		$form_fields[ 'manager' ][ 'default' ] = $manager;
		$form_fields[ 'proposed_by' ][ 'default' ] = $uID;


		$form_fields[ 'decision_type' ] = array (
			'default' 	=> 'change',
			'type'      => 'hidden',
		);


		$gID = (int) $this->get_group( $id, $obj );
		$oID = (int) $this->get_organization( $id, $obj );

		$form_fields[ 'organization' ][ 'default' ] = $oID;

		$form_fields[ 'group' ][ 'default' ] = $gID;

		$dID = $id;

		$obj->ID = $obj->data->id = $obj->data->field_id = 0;
		$hides = array(
			'change_to',
			'organization',
			'decision_status',
			'group',
			'proposed_by'
		);

		foreach( $hides as $hide ) {
			if ( isset( $form_fields[ $hide ] )  ){
				if ( isset( $form_fields[ $hide ] ) ) {

					$form_fields[ $hide ][ 'type' ] = 'hidden';

				}

			}


		}

		return $this->form( $obj, $form_fields, 'modify', $id, $obj, $oID, $uID, $type, $gID );

	}

	private function form( $obj, $form_fields, $new, $id, $obj, $oID, $uID, $type, $gID ) {
		wp_enqueue_script( 'pods-form' );

		/**
		 * Override form fields for add/edit  form
		 *
		 * @param 	array 	$fields		Parameters for pods::form
		 * 		@see http://pods.io/docs/code/pods/form/
		 *
		 * @param	bool		$new	If is a new item or not.
		 * @param 	int|null	$id  	ID of item being edited. When creating ID is null. Can not be used to set ID.
		 * @param	int			$oID	ID of organization item is a part of.
		 * @param	obj			$obj	Object being used to create/edit item
		 * @param	int			$uID	ID of member creating/editing item. Changing this has no effect on who are the initial members. For that use 'ht_dms_initial_members' filter.
		 * @param	string		$type	The prefixed CPT name.
		 *
		 * @since 0.0.1
		 */

		if ( $new !== 'modify' ) {
			$form_fields = apply_filters( "ht_dms_{$type}_edit_form_fields", $form_fields, $new, $id, $obj, $oID, $uID, $type, $gID );
		}

		/**
		 * Action that runs before any ht_dms form
		 *
		 * @since 0.0.1
		 */
		$form = do_action( 'ht_dms_before_form' );

		if ( ! defined( 'HT_DEV_MODE' ) || ! HT_DEV_MODE ) {
			$form .= $this->form_fix( $new, $type );
		}

		$ui = ht_dms_ui();

		//set a global for checking if we're on a modify form or not #86
		global $dms_modify_form;

		if ( $new !== 'modify' ) {
			$dms_modify_form = false;
			$link = $ui->output_elements()->action_append( ht_dms_home(), 'new', 'X_ID_X' );
			if ( $new ) {
				$label = __( 'Create', 'ht_dms' );
			}
			else {
				$label = __( 'Edit', 'ht_dms' );
			}

			if ( $type === HT_DMS_TASK_POD_NAME ) {
				$link = add_query_arg( 'task', true, $link );
			}

			$label = sprintf( '%1s %s', $label, $this->display_names( $type ) );

			$form .= $obj->form( $form_fields, $label, $link );
		}
		else {
			$dms_modify_form = true;
			$link = $ui->output_elements()->action_append( ht_dms_home(), 'change-proposed', pods_v( 'dms_id', 'get', false, true ) );
			$link = $link.'&pmid=X_ID_X';

			$form .= $obj->form( $form_fields, 'Propose Change', $link );
		}

		/**
		 * Action that runs after any ht_dms form
		 *
		 * @since 0.0.1
		 */
		$form .= do_action( 'ht_dms_after_form' );

		return $form;
	}

	/**
	 * Get the organization this decision/group/task belongs to.
	 *
	 * @param 	int   			$id		ID of decision.
	 * @param 	null|obj|Pods  	$obj	Optional. Pods object.
	 *
	 * @return  int|null                Either the organization ID, or null if none is set.
	 *
	 * @since	0.0.1
	 */
	function get_organization( $id, $obj = null ) {
		$obj = $this->null_object( $obj, $id );

		$org = $obj->display( 'organization' );

		return $org;

	}

	/**
	 * Get the group this decision/task belongs to.
	 *
	 * @param 	int   			$id		ID of decision.
	 * @param 	null|obj|Pods  	$obj	Optional. Decision Pods object.
	 *
	 * @return  int|null                Either the group ID, or null if none is set.
	 *
	 * @since	0.0.1
	 */
	function get_group( $id, $obj = null ) {
		$obj = $this->null_object( $obj, $id );

		$gID =  $obj->display( 'group' );

		return $gID;

	}

	/**
	 * Gives the short "display" name for DMS content types.
	 *
	 *
	 * @param 	string 			$type Full name of content type.
	 *
	 * @return 	string|false		 The display name of content type, or false if input invalid.
	 *
	 * @since 	0.0.2
	 */
	function display_names( $type ) {
		$display_names = array(
			'Organization',
			'Group',
			'Decision',
			'Task',
			'Notification'
		);

		/**
		 * Change the display names for the DMS content types.
		 *
		 * @param array $display_names Array of display names
		 *
		 * @return Array of display names. Will be merged with output of $this->content_types()
		 *
		 * @since 0.0.2
		 */
		$display_names = apply_filters( 'ht-dms_display_names', $display_names );

		$names = $this->content_types();

		if ( is_array( $names ) ) {
			$display_names = array_combine( $names, $display_names );
		}

		return pods_v( $type, $display_names, false, true );

	}

	/**
	 * Get the name of the DMS content types.
	 *
	 * This method intentionally does not have a filter. You may change the names by redefining the vales of these constants.
	 *
	 * @return 	array
	 *
	 * @since 	0.0.2
	 */
	function content_types() {
		return array(
			HT_DMS_ORGANIZATION_POD_NAME,
			HT_DMS_GROUP_POD_NAME,
			HT_DMS_DECISION_POD_NAME,
			HT_DMS_TASK_POD_NAME,
			HT_DMS_NOTIFICATION_POD_NAME,
		);

	}

	/**
	 * Return an item's tittle
	 * @param      $id
	 * @param null|Pods $obj
	 *
	 * @return false|null|string
	 *
	 * @since 0.0.3
	 */
	function title( $id, $obj = null ) {
		$obj = $this->null_object( $obj, $id );

		if ( 'post_type' == $this->pod_type( $obj ) ) {
			return $obj->display( 'post_title' );
		}
		else{
			return $obj->display( 'name' );
		}

	}

	/**
	 * Get Pod type
	 *
	 * @param Pods $obj
	 *
	 * @return string
	 *
	 * @since 0.0.3
	 */
	function pod_type( $obj ){
		$api = pods_v( 'api', $obj  );

		return  $api->pod_data[ 'type' ];

	}

	function create( $data ) {
		if ( is_array( $data ) ) {
			$obj = $this->object();
			$fields = $this->field_names( $obj );
			$save_data = array();
			foreach( $data as $field => $value ) {
				if( in_array( $field, $fields) ) {
					$save_data[ $field ] = $value;
				}
			}

			if ( ! empty( $save_data ) ) {
				return $obj->save( $save_data );
			}
		}
	}

	function sanitize_save( $field, $value ) {

		return $value;

	}

	/**
	 * Format a member field.
	 *
	 * Input must contain at least ID and display_name for each user. Pods field value realated to user makes good input.
	 *
	 * @since 0.0.3
	 *
	 * @param array $members Members to format.
	 *
	 * @return array
	 */
	function format_member_field( $members ) {
		$member_options = array();
		if ( is_array( $members )  && ! empty( $members ) ) {
			foreach ( $members as $member ) {
				$name = pods_v( 'display_name', $member );
				$id = pods_v( 'ID', $member );
				if ( $name && $id ) {
					$member_options[ $id ] = $name;
				}
			}

		}

		return $member_options;
	}

}

