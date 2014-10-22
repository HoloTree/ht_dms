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
	 * @param   int|	$id
	 * @param 	obj|null 		$obj
	 * @param	bool			$group.			Optional. If acting on group or organization. Set to the default (true) for group, false for organization.
	 *
	 * @return \Pods
	 */
	function null_obj( $id = null, $obj = null, $group = true ) {
		if ( is_object( $obj ) && is_pod( $obj ) ) {
			if ( ( $group  && $obj->pod_data['name'] ===  HT_DMS_GROUP_POD_NAME ) || ( ! $group && $obj->pod_data[ 'name' ] === HT_DMS_ORGANIZATION_POD_NAME ) ) {
				return $obj;
			}
		}

		if ( $group ) {
			return ht_dms_group( $id, $obj );
		}

		return ht_dms_organization( $id, $obj );


	}

	/**
	 * Get all members of a group/organization
	 *
	 * @param   int 		$id 		ID of group or organization.
	 * @param	obj|null	$obj
	 * @param	bool		$group.	Optional. If acting on group or organization. Set to the default (true) for group, false for organization.
	 * @param bool $ids_only Optional. If true., the default, only IDs are returned. If false, full field data returned foreach.
	 *
	 * @return 	array					IDs of user IDs.
	 *
	 * @since 	0.0.1
	 */
	function all_members( $id, $obj = null, $group = true, $ids_only = true ) {
		$obj = $this->null_obj( $id, $obj, $group );
		if ( $ids_only ) {
			$user_ids = $obj->field( 'members.ID' );
		} else {
			$user_ids = $obj->field( 'members' );
		}

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
	 * Check if a user exists
	 *
	 *
	 * @param 	int	$uID	A user ID.
	 *
	 * @return 	bool		True if user exists, false if not.
	 *
	 * @since 	0.0.1
	 */
	function user_exists( $uID ) {

		return ht_dms_common_class()->user_exists( $uID );

	}

	/**
	 * Check if a group or organization is public.
	 *
	 * Checks the value of the 'visibility' field.
	 * @param  int    $id Group or organization ID
	 * @param null|obj|Pods $obj
	 * @param bool $group Optional. If true group, if false organization.
	 *
	 * @return false|null|string
	 *
	 * @since 0.0.3
	 */
	function is_public( $id, $obj = null, $group = true ) {
		$obj = $this->null_obj( $id, $obj, $group  );

		return $obj->display( 'visibility' );

	}

	/**
	 * @var string The field for storing invited members in.
	 *
	 * @since 0.0.3
	 */
	private $invite_field = 'invited_members';

	/**
	 * Add an existing user to the invite field for an organization
	 *
	 *
	 * @since 0.0.3
	 *
	 * @param int       $id    Group or organization ID
	 * @param int       $uID    ID of user to add.
	 * @param null|Pods $obj
	 * @param bool      $group Optional. If true group, if false organization.
	 *
	 * @return int      ID of group/organization
	 */
	function invite_existing( $id, $uID, $obj = null, $group = true ) {
		$obj = $this->null_obj( $id, $obj, $group );

		$invited_members = $this->get_invited_members( $id, $obj, $group );
		$invited_members = array_push( $invited_members, $uID );
		$invited_members = serialize( $invited_members );
		return $obj->save( $invited_members );


	}


	/**
	 * Add an invited user to a group/organization
	 *
	 * @since 0.0.3
	 *
	 * @param int $id Group or organization ID
     * @prams int       $uID    User ID to Add/
     * @param null|pods $obj
	 * @param bool      $group Optional. If true group, if false organization.
	 *
	 * @return bool True if added
	 */
	function accept_internal_invite( $id, $uID, $obj = null, $group = true ) {
		$obj = $this->null_obj( $id, $obj, $group );

		$invited_members = (array) $this->get_invited_members( $id, $obj, $group );
		if ( ! empty( $invited_members)  && in_array( $uID, $invited_members ) ) {
			$this->add_member( $id, $uID, $obj, $group );
			unset( $invited_members[ $uID ] );
			$invited_members = serialize( $invited_members );
			$this->add_member( $id, $uID, $obj, $group );

			return $obj->save( $this->invite_field, $invited_members, $id );

		}

	}

	/**
	 * Get members invited to a organization/group that have not accepted yet.
	 *
	 * @since 0.0.3
	 *
	 * @param int       $id Group/organization ID
	 * @param null|Pods $obj
	 * @param bool      $group Optional. If true group, if false organization.
	 *
	 * @return array    Invited members.
	 */
	private function get_invited_members( $id, $obj = null, $group = null ) {
		$obj = $this->null_obj( $id, $obj, $group );

		return $invited_members = (array) unserialize( $obj->field( $this->invite_field ) );

	}

	function invite_message( $name, $oID, $organization_name, $email, $new_user = true ) {
		$message[] = $name . '-';
		if ( $new_user ) {
			$link = sprintf( '<a href="%1s">HoloTree</a>', esc_url( ht_dms_home() ) );
			$message[] =  __( sprintf( '%1s is a decision making system that turns ideas in decisions and decisions into actions.', $link ), 'holotree' ) .
			              __( sprintf( 'You have been invited to work with %1s on HoloTree', $organization_name ), 'holotree' );
			$accept_link = add_query_arg( 'ht_dms_invite_code', ht_dms_invite_code( true, $email, $oID ), wp_registration_url() );

		}
		else{
			$link = ht_dms_link( $oID, 'permalink', $organization_name );
			$message[] = __( sprintf( 'You have been invited to join the organization %1s.', $link ), 'holotree' );
			$accept_link = ht_dms_action_append( $link, 'accept-invite', $oID );
			$args = array(
				'user' => $new_user,
				'type' => 'organization',
			);
			$accept_link = add_query_arg( $args, get_permalink( $oID) );
		}

		$accept_text = __( sprintf( 'Click here to accept the invitation to join %1s.', $organization_name ), 'holotree' );
		$message[] = sprintf( '<a href="%1s">%2s</a>', esc_url( $accept_link ), $accept_text );

        pods_error( $message );
		return implode( $message );

	}

	/**
	 * Get the group or organizations facilitators.
	 *
	 * @param  int    $id Group or organization ID
	 * @param null|obj|Pods $obj
	 * @param bool $group Optional. If true group, if false organization.
	 * @param bool $ids_only Optional. If true, only IDs returned. If true, the default, full field data return foreach.
	 *
	 * @return array
	 *
	 * @since 0.0.3
	 */
	function facilitators( $id, $obj = null, $group = null, $ids_only = false ) {
		$obj = $this->null_obj( $id, $obj, $group );
		$facilitators = array();

		$field = $obj->field( 'facilitators' );

		if ( $ids_only ) {
			if ( is_array( $field ) && ! empty( $field ) ) {
				$facilitators = wp_list_pluck( $field, 'ID' );
			}
		}
		else {
			$facilitators = $field;
		}

		return $facilitators;

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
