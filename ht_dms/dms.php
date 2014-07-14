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

//namespace ht_dms;

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
	 * @param null $id
	 * @param null $uID
	 * @param null $obj
	 * @param bool $post_title_label
	 *
	 * @return null|string
	 *
	 * @since 0.0.1
	 */
	function edit( $id = null, $uID = null, $obj = null, $dID = null, $post_title_label = false ) {
		$uID = $this->null_user( $uID );

		$type = $this->get_type( false );

		$new = false;
		if ( is_null( $id )  ) {
			$new = true;
		}

		$params = null;
		if ( ! $new && $this->get_type() !== HT_DMS_TASK_CT_NAME ) {
			$params = array( 'where' => 't.id = " ' . $id . ' " ' );
		}
		else{
			$params = $id;
		}

		$params = apply_filters( 'ht_dms_edit_params', $params, $type );

		$obj = $this->null_object( $obj, $params );

		$fields = (array) $this->fields_to_loop( $obj, false );

		foreach ( $fields as $k => $v ) {
			$form_fields[ $k ] = array( 'label' => $v[ 'label' ] );
		}

		//$form_fields[ 'post_title' ] = array( 'label' => $post_title_label );

		if ( $new ) {
			if ( isset( $form_fields[ 'members' ] ) ) {
				unset( $form_fields[ 'members' ] );
			}

			$initial_members = apply_filters( 'ht_dms_initial_members', $uID, $type );

			$form_fields[ 'members' ] = array ( 'default' => $initial_members );
		}

		if ( $type === HT_DMS_ORGANIZATION_NAME ) {
			$oID = $id;

		}
		elseif ( ! $new ) {
			$oID = $obj->field( 'organization' );
			$form_fields[ 'organization' ] = $oID;
		}
		else {
			global $post;

			if ( $post->post_type === HT_DMS_ORGANIZATION_NAME ) {

				$oID = $post->ID;
				$form_fields[ 'organization' ] = $oID;

			}
			else {

				holotree_error();
			}
		}

		if ( $type === HT_DMS_TASK_CT_NAME ) {
			if ( !is_null( $dID ) ) {
				$form_fields[ 'decision' ] = $dID;
			}
			else {
				if ( is_null( $id ) ) {
					holotree_error( __( 'Decision ID must be set when creating new tasks.', 'holotree' ) );
				}
			}

		}

		return $this->form( $obj, $form_fields, $new, $id, $obj, $oID, $uID, $type );

	}

	/**
	 * Add inline jQuery to fix form.
	 *
	 * Implement in inherited class.
	 *
	 * @return 	bool
	 *
	 * @since 	0.0.1
	 */
	function form_fix( $new = true, $type ) {

		/**
		 * Set jQuery to fix up forms, but content type
		 *
		 * Note: script tags and jQuery no conflict wrapper is added automatically.
		 *
		 * @param string|null $jQuery The jQuery to use. Defaults to null.
		 *
		 * @param bool $new Whether this script is for a new item or not.
		 *
		 * @return string|null The jQuery or null
		 */
		$jQuery = apply_filters( "ht_dms_{$type}_form_fix_jQuery", null, $new  );

		if ( is_null( $jQuery ) ) {
			return '';
		}

		$script = "
		<script type='text/javascript'>
		jQuery(document).ready(function($) {
		//Fix for hidden fields
		";
		$script .= $jQuery;
		$script .= "
});
		</script>
		";

		return $script;
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
			$fields_array = $obj->fields();
			foreach ( $fields_array as $key => $value ) {
				$fields[ $key ] = $value;
			}

		}

		return $fields;
	}

	/**
	 * Convert null value for $id to current user ID
	 *
	 * @param 	int|null	$id	Optional. A user ID.
	 *
	 * @return 	int				Same as input or current user ID if input is null.
	 *
	 * @since 	0.0.1
	 */
	function null_user( $id ) {
		if ( is_null( $id ) ) {
			$id = get_current_user_id();
		}

		return $id;

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
		//$this->reset_cache( $id );

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

		if ( $type !== HT_DMS_DECISION_CPT_NAME ) {
			holotree_error( __METHOD__, __('only supports decisions. For now...', 'holotree' ) );
		}

		$uID = $this->null_user( $uID );

		$old = $obj->fields();

		unset( $old[ 'reason_for_change' ] );
		unset( $old[ 'change_to' ] );
		unset( $old[ 'proposed_by' ] );
		unset( $old[ 'consensus' ] );
		$form_fields[ 'post_title'][ 'default' ] = $obj->field('post_title' );

		foreach( $old as $field => $value ) {
			$form_fields[ $field ][ 'default' ] = $obj->field( $field );
		}

		$form_fields[ 'reason_for_change' ] = array(
			'required' => 'true'
		);
		$form_fields[ 'change_to' ] = array(
			'default' => $id )
		;
		$form_fields[ 'proposed_by' ][ 'default' ] = $uID;
		$form_fields[ 'decision_type' ][ 'default' ] = 'change';

		$oID = $dID = $gID = null;

		if ( isset( $old[ 'organization' ] ) ) {
			$oID = $form_fields[ 'organization' ][ 'default' ] = $this->get_organization( $id, $obj );
		}
		else {
			$oID = $id;
		}
		if ( isset( $old[ 'group' ] ) ) {
			$gID = $form_fields[ 'group' ][ 'default' ] = $this->get_group( $id, $obj );
			$dID = $id;
		}
		else{
			$gID = $id;
		}
		$obj = $this->object();

		return $this->form( $obj, $form_fields, 'modify', $id, $obj, $oID, $uID, $type );

	}

	private function form( $obj, $form_fields, $new, $id, $obj, $oID, $uID, $type ) {

		remove_filter( 'the_title', '__return_false' );

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

		$form_fields = apply_filters( "ht_dms_{$type}_edit_form_fields", $form_fields, $new, $id, $obj, $oID, $uID, $type );
		var_Dump( $obj->ID() );
		/**
		 * Action that runs before any ht_dms form
		 *
		 * @since 0.0.1
		 */
		$form = do_action( 'ht_dms_before_form' );

		//$form .= $this->form_fix( $new, $type );

		if ( $new !== 'modify' ) {
			$form .= $obj->form( $form_fields );
		}
		else {
			$form .= $obj->form( $form_fields, 'Propose Change', get_permalink( $id ) );
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

		return (int) $obj->display( 'organization.ID' );
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

		return (int) $obj->display( 'group.ID' );

	}

}

