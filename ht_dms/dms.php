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

namespace ht_dms;

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
	function item( $obj = null, $id = null, $params = null, $cached = true, $fields = false ) {
		if ( ! is_array( $params ) && intval( $params ) > 0 ) {
			$id = $params;
			$params = null;
		}
		$obj = $this->null_object( $obj, $params );

		if ( $fields ) {
			if ( ! $this->field_loop( $obj ) ) {

				if ( $this->fields_to_loop() !== false ) {
					$fields_to_loop = $this->fields_to_loop();
				}
				else {
					foreach( $obj->fields() as $name => $data ) {
						$fields_to_loop[ $name ] = $name;
					}

				}

				foreach( $fields_to_loop as $name  ) {
					$fields[ $name ] = $obj->field ( $name );
				}

				return $fields;

			}
			else {

				return $this->field_loop( $id, $obj );

			}
		}
		else {

			return $obj;
		}

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
	function edit( $id = null, $uID = null, $obj = null, $post_title_label = false ) {
		$uID = $this->null_user( $uID );

		$new = false;
		if ( is_null( $id ) ) {
			$new = true;
		}

		$params = null;
		if ( !$new && self::$type !== HT_DMS_GROUP_CPT_NAME ) {
			$params = array( 'where' => 't.id = " ' . $id . ' " ' );
		}

		$type = $this->get_type( false );

		$params = apply_filters( 'ht_dms_edit_params', $params, $type );

		$obj = $this->null_object( $obj, $params );

		$fields = (array) $this->fields_to_loop( $obj, false );

		foreach ( $fields as $k => $v ) {
			$form_fields[ $k ] = array( 'label' => $v[ 'label' ] );
		}

		$form_fields[ 'post_title' ] = array( 'label' => $post_title_label );

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
		else {
			$oID = (int) $obj->display( 'organization.ID' );
		}

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

		$form_fields = apply_filters( "{$this->get_type()}_edit_form_fields", $form_fields, $new, $id, $obj, $oID, $uID, $type );

		/**
		 * Action that runs before any ht_dms form
		 *
		 * @since 0.0.1
		 */
		$form = do_action( 'ht_dms_before_form' );

		if ( $this->form_fix() ) {
			if ( ( $type === HT_DMS_DECISION_CPT_NAME || HT_DMS_TASK_CT_NAME ) && $new ) {
				$form .= $this->form_fix( $new );
			}
			else {
				$form .= $this->form_fix();
			}
		}

		$form .= $obj->form( $form_fields );

		/**
		 * Action that runs after any ht_dms form
		 *
		 * @since 0.0.1
		 */
		$form .= do_action( 'ht_dms_after_form' );

		return $form;

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
	function form_fix( ) {
		return false;
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
				$fields[ $key ] = $key;
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
	 * @params	obj|null	$obj	Optional. Pods Obj
	 *
	 * @return 	int 		ID
	 *
	 * @since 	0.0.1
	 */
	public function update( $id, $field, $value, $obj = null ) {
		$obj = $this->null_object( $obj, $id );
		if ( $field === 'consensus' ) {
			$value = serialize( $value );
		}

		$id = $obj->save( $field, $value );
		//$this->reset_cache( $id );

		return $id;
	}

}
