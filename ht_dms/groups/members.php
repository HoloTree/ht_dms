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

namespace ht_dms\groups;


class members {

	/**
	 * Get all members of a group.
	 *
	 * @param   int 	 	$id 		ID of group.
	 * @param	\Pods|null	$obj        Optional. Pods object
	 *
	 * @return 	array 					IDs for all members of group.
	 *
	 * @since 	0.3.0
	 */
	public static function all_members( $id, $obj = null, $ids_only = true ) {
		$user_ids = ht_dms_membership_class()->all_members( $id, $obj, true, $ids_only );

		return $user_ids;

	}

	/**
	 * Add a member to a group.
	 *
	 * @TODO Allow for an array of members to be added?
	 *
	 * @param	int			$id		ID of group to add member to.
	 * @param 	int|null 	$uID	Optional. ID of user to add. Default is current user.
	 * @param	\Pods|null	$obj        Optional. Pods object
	 *
	 * @return 	int					ID of group member was added to.
	 *
	 * @since 	0.3.0
	 */
	public static function add_member ( $id, $uID = null, $obj = null ) {
		$id = ht_dms_membership_class()->add_member( $id, $uID, $obj, true );

		return $id;

	}

	/**
	 * Remove a member from a group.
	 *
	 * @TODO Allow for an array of members to be added?
	 *
	 * @param	int			$id		ID of group to remove member from.
	 * @param 	int|null 	$uID	Optional. ID of user to add. Default is current user.
	 * @param	\Pods|null	$obj        Optional. Pods object
	 *
	 * @return 	int		 			ID of group member was removed from.
	 *
	 * @since 	0.3.0
	 */
	public static function remove_member( $id, $uID = null, $obj = null ) {

		$id = ht_dms_membership_class()->remove_member( $id, $uID, $obj, true );

		return $id;

	}


	/**
	 * Check if a user is a member of a group.
	 *
	 * @param int     	$id		ID of group to check for member in.
	 * @param int|null 	$uID	Optional. User ID. Defaults to current user.
	 * @param	\Pods|null	$obj        Optional. Pods object
	 *
	 * @return bool				True if user is a member, false if not.
	 *
	 * @since 0.3.0
	 */
	public static function is_member( $id, $uID = null, $obj = null ) {

		return ht_dms_membership_class()->is_member( $id, $uID, $obj, true );

	}


	/**
	 * Either add member to group, or add to pending members, depending on group access.
	 *
	 * @param 	int     	$id		ID of group to join.
	 * @param 	int|null	$uID	Optional. ID of user to add. Default is current user.
	 * @param	\Pods|null	$obj        Optional. Pods object
	 * @return 	int			$id		ID of group member is joining.
	 *
	 * @since	0.3.0
	 */
	public static function join( $id, $uID = null, $obj = null ) {
		$uID = ht_dms_null_user( $uID );
		if ( get_user_by( 'id', $uID ) !== false ) {
			$obj = ht_dms_group_class()->null_object( $obj, $id );
			$access = $obj->field( 'open_access' );
			if ( $access == 1 ) {
				$id = \ht_dms\groups\members::add_member( $id, $uID );
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
	 * List pending members for a group.
	 *
	 * @param   int 	$id			ID of group.
	 *
	 * @return 	array	$pending	IDs of pending members.
	 *
	 * @since 	0.3.0
	 */
	public static function get_pending( $id, $obj = null ) {
		$obj = ht_dms_group_class()->null_object( $obj, $id );
		$pending = $obj->field( 'pending_members.ID' );

		return $pending;

	}

	/**
	 * Check if a user's membership is pending
	 *
	 * @param null|int $uID
	 * @param int $id
	 * @param \Pods|null $obj
	 *
	 * @return bool
	 *
	 * @since 0.3.0
	 */
	public static function is_pending( $uID = null, $id, $obj = null ) {
		$uID = ht_dms_null_user( $uID );
		$obj = ht_dms_group_class()->null_object( $obj, $id );

		if ( is_array( self::get_pending( $id, $obj ) ) ) {
			return in_array( $uID, self::get_pending( $id, $obj ) );

		}

	}

	/**
	 * Approve or reject one or all pending members for a group.
	 *
	 * @param 	int     	$id			ID of group to add members to.
	 * @param 	int|null	$uID		Optional. ID of user to add/ reject. Default is current user. Not used if $all = true
	 * @param 	bool		$approve	Optional. If true user(s) are added, if false, rejected. Default is true.
	 *
	 * @return 	int|array	$id		 	ID of group pending member(s) were approved to join.
	 *
	 * @since 	0.0.1
	 */
	public static function pending( $id, $uID = null, $approve = true ) {
		$uID = ht_dms_null_user( $uID );
		$obj = ht_dms_group_class()->null_object( null, $id  );

		if ( $approve ) {
			\ht_dms\groups\members::add_member( $id, $uID, $obj  );

		}

		$pending = $obj->field( 'pending_members' );

		$pending =  wp_list_pluck( $pending, 'ID' );
		$pending = array_flip( $pending  );
		unset( $pending[ $uID ] );
		$pending = array_flip( $pending );

		if ( ! empty( $pending ) ) {
			$obj->save( 'pending_members', $pending );
		}
		else{
			$obj->save( 'pending_members', array() );
		}


	}

}
