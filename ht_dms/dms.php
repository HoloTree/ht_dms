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

				return $this->field_loop( $obj );

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

		$fields = (array) $this->fields_to_loop();



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
		 * @param	string		$type	The prefixed CPT name.
		 * @param 	int|null	$id  	ID of item being edited. When creating ID is null. Can not be used to set ID.
		 * @param	obj			$obj	Object being used to create/edit item
		 * @param	$uID		$uID	ID of member creating/editing item. Changing this has no effect on who are the initial members. For that use 'ht_dms_initial_members' filter.
		 *
		 * @since 0.0.1
		 */

		$form_fields = apply_filters( 'ht_dms_edit_form_fields', $form_fields, $type, $oID, $id, $obj, $uID );

		/**
		 * Action that runs before any ht_dms form
		 *
		 * @since 0.0.1
		 */
		$form = do_action( 'ht_dms_before_form' );

		if ( $this->form_fix() ) {
			$form .= $this->form_fix();
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
	 * @param 	obj		$obj
	 *
     * @return 	bool
	 *
	 * @since 	0.0.1
	 */
	function field_loop( $obj ) {

		return false;
	}

	/**
	 * Fields to loop when getting a fields and in $this->edit()
	 *
	 * Implement in inherited class.
	 *
	 * @return 	bool
	 *
	 * @since 	0.0.1
	 */
	function fields_to_loop() {

		return false;
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

}
