<?php
/**
 * HoloTree DMS Membership management for groups and organisations
 *
 * @package   @holotree_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\helper;


class membership {

	/**
	 * Test input object to ensure it is valid
	 *
	 * @param   int|arraynull 	$id_or_params
	 * @param 	obj|null 		$obj
	 * @param	bool			$group.			Optional. If acting on group or organization. Set to the default (true) for group, false for organization.
	 *
	 * @return bool|\holotree\object|mixed|null|\Pods|void
	 */
	function null_obj( $id_or_params = null, $obj = null, $group = true ) {
		if ( is_object( $obj ) && is_pod( $obj ) ) {
			if ( ( $group  && $obj->pod_data['name'] ===  HT_DMS_GROUP_CPT_NAME ) || ( ! $group && $obj->pod_data[ 'name' ] === HT_DMS_ORGANIZATION_NAME ) ) {
				return $obj;
			}
		}

		if ( $group ) {
			$name = 'group';
		}
		else {
			$name = 'organization';
		}

		if ( is_array( $id_or_params ) ) {
			$params = $id_or_params;
			$id = null;
		}
		elseif( intval( $id_or_params ) > 0 ) {
			$id = $id_or_params;
			$params = null;
		}
		else {
			$id = null;
			$params = null;
		}

		return holotree_pods_object( $name, $id , $expire = HOUR_IN_SECONDS, true, $params );


	}

	/**
	 * Get all members of a group/organization
	 *
	 * @param   int 		$id 		ID of group or organization.
	 * @param	obj|null	$obj
	 * @param	bool		$group.	Optional. If acting on group or organization. Set to the default (true) for group, false for organization.
	 *
	 * @return 	array					IDs of user IDs.
	 *
	 * @since 	0.0.1
	 */
	function all_members( $id, $obj = null, $group = true ) {
		$obj = $this->null_obj( $id, $obj, $group );
		$user_ids = $obj->field( 'members.ID' );

		return $user_ids;

	}

	/**
	 * Add a member to a group/organization
	 *
	 * @TODO Allow for an array of members to be added?
	 *
	 * @param	int			$id		ID of group or organization to add member to.
	 * @param 	int|null 	$uID	Optional. ID of user to add. Default is current user.
	 * @param	obj|null	$obj
	 * @param	bool		$group.	Optional. If acting on group or organization. Set to the default (true) for group, false for organization.
	 *
	 * @return 	int					ID of group/ organization member was added to.
	 *
	 * @since 	0.0.1
	 */
	function add_member ( $id, $uID = null, $obj = null, $group = true ) {
		$obj = $this->null_obj( $id, $obj, $group );

		$uID = $this->null_user( $uID );
		if ( get_user_by( 'id', $uID ) !== false ) {
			$members = $this->all_members( $id, $obj, $group  );
			$members[] = $uID;
			$id = $this->update( $id, 'members', $members );

			return $id;

		}

	}


	/**
	 * Remove a member from a group / organization
	 *
	 * @TODO Allow for an array of members to be added?
	 *
	 * @param	int			$id		ID of group or organization to remove member from.
	 * @param 	int|null 	$uID	Optional. ID of user to add. Default is current user.
	 * @param	bool		$group.	Optional. If acting on group or organization. Set to the default (true) for group, false for organization.
	 * @param	bool		$group.	Optional. If acting on group or organization. Set to the default (true) for group, false for organization.
	 *
	 * @return 	int				ID of group or organization member was removed from.
	 *
	 * @since 	0.0.1
	 */
	function remove_member( $id, $uID = null, $obj = null, $group = true ) {
		$obj = $this->null_obj( $id, $obj, $group );

		$uID = $this->null_user( $uID );

		if ( get_user_by( 'id', $uID ) !== false ) {
			$members = $this->all_members( $id, $obj, $group  );
			if ( ( $key = array_search( $uID, $members ) ) !== false) {
				unset( $members[ $key ] );
			}

			$id = $this->update( $id, 'members', $members, $obj, $group );

			return $id;
		}

	}

	/**
	 * Check if an organization or group has open access.
	 *
	 * @param   int   		$id
	 * @param 	obj|null 	$obj
	 * @param	bool		$group.	Optional. If acting on group or organization. Set to the default (true) for group, false for organization.
	 *
	 * @return 	bool			Whether or not group/organization is "open access"
	 */
	function open_access( $id, $obj = null, $group = true ) {
		$obj = $this->null_obj( $id, $obj, $group  );
		if ( $obj->field( 'open_access' ) == 1 ) {
			return true;

		}

	}

	/**
	 * Check if a user is a member of a group or organization.
	 *
	 * @param int     	$id		ID of group or organization to check for member in
	 * @param int|null 	$uID	Optional. User ID. Defaults to current user
	 * @param obj|null	$obj	Optional.
	 * @param	bool		$group.	Optional. If acting on group or organization. Set to the default (true) for group, false for organization.
	 *
	 * @return bool				True if user is a member, false if not.
	 *
	 * @since 0.0.1
	 */
	function is_member( $id, $uID = null, $obj = null, $group = true ) {
		$obj = $this->null_obj( $id, $obj, $group  );
		$uID = $this->null_user( $uID );

		$members = $this->all_members( $id, $obj, $group );
		if ( is_array( $members ) ) {
			if ( in_array( $uID, $members ) ) {

				return true;
			}

		}

	}

	/**
	 * Update group or organization
	 *
	 * @param      $id
	 * @param      $field
	 * @param      $value
	 * @param 	null $obj
	 * @param	bool		$group.	Optional. If acting on group or organization. Set to the default (true) for group, false for organization.
	 *
	 * @return 	int					ID of group or organization that was updated.
	 *
	 * @since 	0.0.1
	 */
	function update( $id, $field, $value, $obj = null,  $group = true  ) {
		$obj = $this->null_obj( $id, $obj, $group );

		$id = $obj->save( $field, $value );

		return $id;

	}

	/**
	 * Convert null value for $uID to current user ID.
	 *
	 * Alias of HoloTree_DMS::null_user()
	 *
	 * @param 	int	$uID	A user ID.
	 *
	 * @return 	int		 	Same as input or current user ID.
	 *
	 * @since 	0.0.1
	 */
	function null_user( $uID ) {

		return holotree_dms_class()->null_user( $uID );

	}

	/**
	 * Check if a user exists
	 *
	 * Alias of HoloTree_DMS::user_exists()
	 *
	 * @param 	int	$uID	A user ID.
	 *
	 * @return 	bool		True if user exists, false if not.
	 *
	 * @since 	0.0.1
	 */
	function user_exists( $uID ) {

		return holotree_dms_class()->user_exists( $uID );

	}

	/**
	 * Holds the instance of this class.
	 *
	 * @since  0.0.1
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
} 
