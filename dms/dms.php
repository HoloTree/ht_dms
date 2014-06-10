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

namespace dms;

abstract class dms {

	/**
	 * The name of the CPT
	 *
	 * Must be set when extending this class using $this->set_type()
	 *
	 * @var string
	 *
	 * @since 0.0.1
	 */
	static public $type;

	/**
	 * The length to cache Pods Objects
	 *
	 * Default is 85321 seconds ~ 1 Day
	 *
	 * @var int
	 *
	 * @since 0.0.1
	 */
	static public $cache_length = 85321;

	/**
	 * Cache mode for Pods Objects
	 *
	 * object|transient|site-transient
	 * @var string
	 *
	 * @since 0.0.1
	 */
	static public $cache_mode = 'object';

	/**
	 * Set the name of the CPT
	 *
	 * @param 	string 	$type
	 *
	 * @since 0.0.1
	 */
	function set_type( $type ) {

		self::$type = $type;

	}

	/**
	 * Get value of self::$type
	 *
	 * @param 	bool 	$lower	Optional. If true, the default, value is returned in lower cases
	 *
	 * @return 	string			The name of the current CPT.
	 *
	 * @since 0.0.1
	 */
	function get_type( $lower = true ) {

		if ( $lower ) {

			return strtolower( self::$type );

		}

		return self::$type;

	}

	/**
	 * Set length to cache Pods Objects
	 *
	 * @param 	int	$length	Time in seconds to cache.
	 *
	 * @since 0.0.1
	 */
	function set_cache_length( $length ) {

		self::$cache_length = $length;

	}

	/**
	 * Set cache mode for Pods Objects
	 *
	 * @param 	string 	$type	object|transient|site-transient
	 *
	 * @since 0.0.1
	 */
	function set_cache_mode( $type ) {

		self::$cache_mode = $type;

	}


	/**
	 * Object of this CPT
	 *
	 * @param 	bool 			$cache	Optional. Whether to attempt to get cached object or not.
	 * @param 	null|array|int 	$params	Optional. Either the item ID, or pods::find() params
	 *
	 * @return 	bool|mixed|null|Pods|void
	 *
	 * @since 0.0.1
	 */
	function object( $cache = true, $params = null ) {
		$id = null;

		if ( ! is_array( $params ) && intval( $params ) > 0 ) {
			$id = $params;
			$params = null;
		}

		return $this->get_pods_object( $id, $params, $cache );

	}

	/**
	 * Get a Pods object For ht_dms content types
	 *
	 * @param 	null 				$id
	 * @param 	null|array|int 	$params	Optional. Either the item ID, or pods::find() params.
	 * @param 	bool 			$cache	Optional. Whether to attempt to get cached object or not.
	 *
	 * @return 	bool|mixed|null|\Pods|void
	 *
	 * @since	0.0.1
	 */
	private function get_pods_object( $id = null, $params = null, $cache = true ) {

		if ( ! $cache ) {
			return self::build_pods_object( self::$type, $id, $params );
		}

		$key_group = self::key_group( self::$type, $id );
		$key = $group = null;
		if ( $key_group !== false ) {
			//sets $key and $group
			extract( $key_group );
		}
		if ( ! is_null( $key )  ) {
			$value = pods_view_get( $key, self::$cache_mode, $group );
			if ( ! $value ) {
				$value = self::build_pods_object( self::$type, $id, $params );

				pods_view_set( $key, $value, self::$cache_length, self::$cache_mode, $group );

			}

			return $value;

		}
		else {

			return self::build_pods_object( self::$type, $id, $params );

		}

	}

	/**
	 * Build Pods object
	 *
	 * @param 	string  $pod_name	Name of content type decision|group|task|notification|organization
	 * @param 	null 	$id			Optional. ID of item. Not used if params isset.
	 * @param 	null 	$params		Optional.pods::find() params
	 *
	 * @return 	bool|\Pods
	 *
	 * @since	0.0.1
	 */
	private function build_pods_object( $pod_name, $id = null, $params = null ) {
		if ( is_null( $params ) ) {
			if ( is_null( $id ) ) {
				$obj = pods( $pod_name );
			}
			else {
				$obj = pods( $pod_name, $id );
			}
		}
		else {
			$obj = pods( $pod_name, $params );
		}

		return $obj;

	}

	/**
	 * Build key group for caching
	 *
	 * @param 	string	$name	Name of content type.
	 * @param 	int		$id		ID of item.
	 *
	 * @return 	array|bool
	 *
	 * @since	0.0.1
	 */
	private function key_group( $name, $id ) {
		$prefix = apply_filters( 'ht_dms_cache_prefix', 'ht_dms_') ;
		if ( is_array( $id ) && isset( $id[0] ) ) {
			$id = intval( $id[0] );
		}

		if ( is_array( $id ) ) {
			return false;
		}

		if ( !is_null( $id ) ) {
			$key = $prefix.$name.'_'.$id;
		}
		else {
			$key = $prefix.$name;
		}

		$group = $prefix.$name;

		$key_group = array( 'key' => $key, 'group' => $group );

		return $key_group;

	}


	/**
	 * Checks that a supplied Pods object is valid and if not rebuilds it.
	 *
	 * @param 	obj|null 		$obj			Optional. Object to check.
	 * @param 	null|array|int 	$params_or_id	Optional. Either the item ID, or pods::find() params
	 *
	 * @return bool|mixed|null|Pods|void
	 *
	 * @since 0.0.1
	 */
	function null_object( $obj = null, $params_or_id = null ) {
		if ( $this->check_obj( $obj, $params_or_id ) ) {
			return $obj;
		}
		else {
			$obj = $this->object( true, $params_or_id );
			if ( $this->check_obj( $obj, $params_or_id ) ) {
				return $obj;

			}
			else {
				$obj = $this->object( false, $params_or_id );
				if ( $this->check_obj( $obj, $params_or_id ) ) {
					return $obj;

				}
				else {
					pods_error( 'Can not build object '.__CLASS__.__METHOD__.__LINE__ );
				}

			}

		}

	}

	/**
	 * Validates a Pods object to ensure it is the correct one to use.
	 *
	 * @param obj 		$obj	Object to check.
	 * @param int|null	$id
	 *
	 * @return bool
	 *
	 * @since 0.0.1
	 */
	function check_obj( $obj, $id = null ) {
		if ( is_object( $obj ) && is_pod ( $obj ) && $obj->pod_data['name'] === self::$type ) {
			if ( is_null( $id ) || ( !is_null( $id ) && $id === $obj->id() ) ) {

				return true;

			}

		}

	}

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
			if ( ! $this->field_loop() ) {

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
