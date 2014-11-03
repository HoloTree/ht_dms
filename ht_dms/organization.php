<?php
/**
 * HoloTree DMS Organization Management
 *
 * @package   @holotree_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms;

class organization extends \ht_dms\dms\dms implements \Hook_SubscriberInterface{

	/**
	 * Set name of CPT this class is for.
	 *
	 * @var string
	 *
	 * @since 0.0.1
	 */
	public static $type = HT_DMS_ORGANIZATION_POD_NAME;

	function __construct() {
		$type = $this->get_type( );

		add_filter( "ht_dms_{$type}_edit_form_fields", array( $this, 'form_fields' ), 10, 6  );

	}

	/**
	 * Set actions
	 *
	 * @since 0.0.3
	 *
	 * @return array
	 */
	public static function get_actions() {
		return array();
	}

	/**
	 * Set filters
	 *
	 * @since 0.0.3
	 *
	 * @return array
	 */
	public static function get_filters() {
		$type = self::$type;

		return array(
			"ht_dms_{$type}_edit_form_fields" => array( 'form_fields', 10, 6 ),
		);

	}

	/**
	 * Set the name of the CPT
	 *
	 * @param 	string 	$type
	 *
	 * @since 0.0.1
	 */
	function set_type() {

		return self::$type;

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
	function field_loop( $id, $obj, $all = false ) {
		if ( is_null( $id ) ) {
			if ( $obj->total() > 0 ) {
				while ( $obj->fetch() ) {
					$fields = $obj->fields();
					foreach ( $fields as $key => $value ) {
						$organization[ $key ] = $obj->field( $key );
						$organizations[ $obj->id() ] = $organization;
					}
				}

				return $organizations;

			}
		}
		else {
			$fields = $this->fields_to_loop( $obj, true );
			$fields[ 'ID' ] = null;
			$fields[ 'id' ] = null;
			$fields[ 'post_title' ] = null;
			$fields[ 'post_author' ] = null;
			foreach ( $fields as $key => $value ) {
				$organization[ $key ] = $obj->field( $key );
			}

			if ( !is_null( $id ) ) {
				$organizations = array();
				$organizations[ $obj->id() ] = $organization;
				return $organizations;
			}
			else {

				return $organization;

			}

		}

	}



	/**
	 * Get all members of a organization.
	 *
	 * @param   int 	 	$id 		ID of organization.
	 * @param	obj|null	$obj
	 * @param bool $ids_only Optional. If true., the default, only IDs are returned. If false, full field data returned foreach.
	 *
	 *
	 * @return 	array 					IDs for all members of organization.
	 *
	 * @since 	0.0.1
	 */
	function all_members( $id, $obj = null, $ids_only = true ) {
		$user_ids = ht_dms_membership_class()->all_members( $id, $obj, false, $ids_only );

		return $user_ids;

	}

	/**
	 * Add a member to a organization.
	 *
	 * @TODO Allow for an array of members to be added?
	 *
	 * @param	int			$id		ID of organization to add member to.
	 * @param 	int|null 	$uID	Optional. ID of user to add. Default is current user.
	 * @param	obj|null	$obj
	 *
	 * @return 	int					ID of organization member was added to.
	 *
	 * @since 	0.0.1
	 */
	function add_member ( $id, $uID = null, $obj = null ) {
		$id = ht_dms_membership_class()->add_member( $id, $uID, $obj, false );

		return $id;

	}

	/**
	 * Remove a member from a organization.
	 *
	 * @TODO Allow for an array of members to be added?
	 *
	 * @param	int			$id		ID of organization to remove member from.
	 * @param 	int|null 	$uID	Optional. ID of user to add. Default is current user.
	 * @param	obj|null
	 *
	 * @return 	int		 			ID of organization member was removed from.
	 *
	 * @since 	0.0.1
	 */
	function remove_member( $id, $uID = null, $obj = null ) {

		$id = ht_dms_membership_class()->remove_member( $id, $uID, $obj, false );

		return $id;

	}

	/**
	 * Check if a user is a member of a organization.
	 *
	 * @param int     	$id		ID of organization to check for member in.
	 * @param int|null 	$uID	Optional. User ID. Defaults to current user.
	 * @param obj|null	$obj	Optional. Single organization object for organization to check.
	 *
	 * @return bool				True if user is a member, false if not.
	 *
	 * @since 0.0.1
	 */
	function is_member( $id, $uID = null, $obj = null ) {

		return ht_dms_membership_class()->is_member( $id, $uID, $obj, false );

	}

	/**
	 * Check if a user is a facilitator of a organization
	 *
	 * @param int     	$id		ID of organization to check for facilitator of.
	 * @param int|null 	$uID	Optional. User ID. Defaults to current user
	 * @param obj|null	$obj	Optional. Single organization object for organization to check.
	 *
	 * @return bool				True if user is a facilitator, false if not.
	 *
	 * @since 0.0.1
	 */
	function is_facilitator( $id, $uID = null, $obj = null ) {

		$uID = $this->null_user( $uID );
		$facilitators = $this->all_facilitators( $id, $obj );

		if ( is_array( $facilitators ) ) {
			if ( in_array( $uID, $facilitators ) ) {
				return true;

			}

		}elseif ( is_int( $facilitators ) || intval( $facilitators ) > 0 ) {

			if ( $facilitators === $uID ) {

				return true;

			}

		}



	}

	/**
	 * Get all facilitators of a organization.
	 *
	 * @param   int 		$id 		ID of organization.
	 * @param 	obj|null	$obj		Optional. Single organization object for organization to check.
	 *
	 * @return 	array 					IDs for all facilitators of organization.
	 *
	 * @since 	0.0.1
	 */
	function all_facilitators( $id, $obj = null ) {
		$obj = $this->null_object( $obj, $id );
		$user_ids = $obj->field( 'facilitators.ID' );

		return $user_ids;

	}



	/**
	 * Either add member to organization, or add to pending members, depending on organization access.
	 *
	 * @param 	int     	$id		ID of organization to join.
	 * @param 	int|null	$uID	Optional. ID of user to add. Default is current user.
	 * @param 	pods object	$obj	Optional. Provide a Pods object instead of getting one.
	 * @return 	int			$id		ID of organization member is joining.
	 *
	 * @since	0.0.1
	 */
	function join( $id, $uID = null, $obj = null ) {
		$uID = $this->null_user( $uID );
		if ( get_user_by( 'id', $uID ) !== false ) {
			if ( is_null( $obj ) ) {
				$obj = $this->single_organization_object( $id );
			}
			$access = $obj->field( 'open_access' );
			if ( $access == 1 ) {
				$id = $this->add_member( $id, $uID );
				$id = $access;
				return $id;
			}
			else {
				$pending = $obj->field( 'pending_members.ID' );
				$pending[] = $uID;
				$id = $obj->save( 'pending_members', $pending );
				return $id;
			}

		}

	}

	/**
	 * List pending members for a organization.
	 *
	 * @param   int 	$id			ID of organization.
	 *
	 * @return 	array	$pending	IDs of pending members.
	 *
	 * @since 	0.0.1
	 */
	function get_pending( $id, $obj = null ) {
		$obj = $this->null_object( $obj, $id );
		$pending = $obj->field( 'pending_members.ID' );

		return $pending;

	}

	function is_pending( $uID = null, $id, $obj = null ) {
		$uID = $this->null_user( $uID );
		$obj = $this->null_object( $obj, $id );

		if ( is_array( $this->get_pending( $id, $obj ) ) ) {
			return in_array( $uID, $this->get_pending( $id, $obj ) );
		}

	}

	/**
	 * Approve or reject one or all pending members for a organization.
	 *
	 * @param 	int     	$id			ID of organization to add members to.
	 * @param 	int|null	$uID		Optional. ID of user to add/ reject. Default is current user. Not used if $all = true
	 * @param 	bool 		$all		Optional. Approve/ reject all pending members. Default is false.
	 * @param 	bool		$approve	Optional. If true user(s) are added, if false, rejected. Default is true.
	 *
	 * @return 	int|array	$id		 	ID of organization pending member(s) were approved to join.
	 *
	 * @since 	0.0.1
	 */
	function pending( $id, $uID = null, $all = false, $approve = true ) {
		$uID = $this->null_user( $uID );
		$obj = $this->null_object( null, $id  );

		if ( $all ) {
			if ( $this->user_exists( $uID )) {
				$none = array();
				$pending = $obj->field( 'pending_members.ID' );

				$members = $obj->field( 'members.ID' );
				if ( is_array( $pending ) ) {
					if ( $approve ) {
						foreach ( $pending as $new_member ) {
							$members[ ] = $new_member;
						}
						$obj->save( 'pending_members', $none );
						$id = $obj->save( 'members', $members );

						return $id;
					}
					else {
						$id = $obj->save( 'pending_members', $none );

						return $id;
					}

				}
				elseif ( is_int( $pending ) ) {
					if ( $approve ) {
						$members[ ] = $pending;
						$id = $obj->save( 'members', $members );
						$obj->save( 'pending_members', $none );


						return $id;
					}
					else {
						$pending = $obj->field( 'pending_members.ID' );
						if( ( $key = array_search( $uID, $pending ) ) !== false) {
							unset( $pending[ $key ] );
						}

						$obj->save( 'pending_members', $pending );

					}

				}
				else {
					ht_dms_error();
				}

			}
		}
		else {

			$id = $this->add_member( $id, $uID );

			$pending = $obj->field( 'pending_members.ID' );
			if ( is_array( $pending ) ) {
				if ( ( $key = array_search( $uID, $pending ) ) !== FALSE ) {
					unset( $pending[ $key ] );
				}
			}
			$obj->save( 'pending_members', $pending );

			return $id;
		}

	}


	/**
	 * Set fields for the edit/new organization field
	 *
	 * @uses 'ht_dms_{$type}_edit_form_fields' filter
	 *
	 * @return array $fields
	 */
	function form_fields( $fields, $new, $id, $obj, $oID, $uID ) {
		if ( $new ) {
			global $cuID;
			$fields[ 'members' ][ 'type' ][ 'default' ] = $cuID;
		}

		$fields[ 'members' ][ 'type' ] = 'hidden';

		if ( $new ) {
			unset($fields['facilitators'] );
		}
		else{
			$fields['facilitators'] = array(
				'data' => $this->format_member_field( $this->all_members( $id, false, false ) )
			);
		}

		$unset_fields = array( 'groups', 'decisions', 'tasks' );
		foreach( $unset_fields as $field ) {

			unset( $fields[ $field ] );
		}

		return $fields;

	}

	/**
	 * Holds the instance of this class.
	 *
	 *
	 * @access private
	 * @var    object
	 */
	private static $instance;


	/**
	 * Returns the instance.
	 *
	 * @since  0.0.1
	 * @access public
	 * @return object
	 */
	public static function init() {

		if ( !self::$instance )
			self::$instance = new self;

		return self::$instance;

	}

	/**
	 *  Get total number of groups in an organization.
	 *
	 * @since 0.1.0
	 *
	 * @param int $oID Organization ID
	 * @param null|Pods $obj
	 *
	 * @return int|null
	 */
	function group_count( $oID, $obj = null ){
		$obj = $this->null_obj( $obj );
		$obj->reset();

		$params[ 'where' ] = 'organization.ID "' . esc_attr( $oID );
		$obj = $obj->find( $params );

		if ( is_object( $obj ) ) {

			return $obj->total();

		}

	}


} 
