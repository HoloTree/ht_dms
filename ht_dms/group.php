<?php
/**
 * HoloTree DMS Group Management
 *
 * @package   @holotree_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

//namespace ht_dms;


class group extends dms {

	function __construct() {
		$this->set_type( HT_DMS_GROUP_CPT_NAME );
		$type = $this->get_type( false );
		add_action( 'pods_api_post_save_pod_item_ht_dms_group', array( $this, 'user_fix'), 9, 3 );
		add_filter( "ht_dms_{$type}_form_fix_jQuery", array( $this, 'form_fix_jQuery' ), 10, 2 );
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
	 * Get all members of a group.
	 *
	 * @param   int 	 	$id 		ID of group.
	 * @param	obj|null	$obj
	 *
	 * @return 	array 					IDs for all members of group.
	 *
	 * @since 	0.0.1
	 */
	function all_members( $id, $obj = null ) {
		$user_ids = holotree_membership_class()->all_members( $id, $obj, true );

		return $user_ids;

	}

	/**
	 * Add a member to a group.
	 *
	 * @TODO Allow for an array of members to be added?
	 *
	 * @param	int			$id		ID of group to add member to.
	 * @param 	int|null 	$uID	Optional. ID of user to add. Default is current user.
	 * @param	obj|null	$obj
	 *
	 * @return 	int					ID of group member was added to.
	 *
	 * @since 	0.0.1
	 */
	function add_member ( $id, $uID = null, $obj = null ) {
		$id = holotree_membership_class()->add_member( $id, $uID, $obj, true );

		return $id;

	}

	/**
	 * Remove a member from a group.
	 *
	 * @TODO Allow for an array of members to be added?
	 *
	 * @param	int			$id		ID of group to remove member from.
	 * @param 	int|null 	$uID	Optional. ID of user to add. Default is current user.
	 * @param	obj|null
	 *
	 * @return 	int		 			ID of group member was removed from.
	 *
	 * @since 	0.0.1
	 */
	function remove_member( $id, $uID = null, $obj = null ) {

		$id = holotree_membership_class()->remove_member( $id, $uID, $obj, true );

		return $id;

	}

	/**
	 * Check if a user is a member of a group.
	 *
	 * @param int     	$id		ID of group to check for member in.
	 * @param int|null 	$uID	Optional. User ID. Defaults to current user.
	 * @param obj|null	$obj	Optional. Single group object for group to check.
	 *
	 * @return bool				True if user is a member, false if not.
	 *
	 * @since 0.0.1
	 */
	function is_member( $id, $uID = null, $obj = null ) {

		return holotree_membership_class()->is_member( $id, $uID, $obj, true );

	}

	/**
	 * Check if a user is a facilitator of a group
	 *
	 * @param int     	$id		ID of group to check for facilitator of.
	 * @param int|null 	$uID	Optional. User ID. Defaults to current user
	 * @param obj|null	$obj	Optional. Single group object for group to check.
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

		}

	}

	/**
	 * Get all facilitators of a group.
	 *
	 * @param   int 		$id 		ID of group.
	 * @param 	obj|null	$obj		Optional. Single group object for group to check.
	 *
	 * @return 	array 					IDs for all facilitators of group.
	 *
	 * @since 	0.0.1
	 */
	function all_facilitators( $id, $obj = null ) {
		$obj = $this->null_object( $obj, $id );
		$user_ids = $obj->field( 'facilitators.ID' );

		return $user_ids;

	}



	/**
	 * Either add member to group, or add to pending members, depending on group access.
	 *
	 * @param 	int     	$id		ID of group to join.
	 * @param 	int|null	$uID	Optional. ID of user to add. Default is current user.
	 * @param 	pods object	$obj	Optional. Provide a Pods object instead of getting one.
	 * @return 	int			$id		ID of group member is joining.
	 *
	 * @since	0.0.1
	 */
	function join( $id, $uID = null, $obj = null ) {
		$uID = $this->null_user( $uID );
		if ( get_user_by( 'id', $uID ) !== false ) {
			if ( is_null( $obj ) ) {
				$obj = $this->single_group_object( $id );
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
	 * List pending members for a group.
	 *
	 * @param   int 	$id			ID of group.
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
	 * Approve or reject one or all pending members for a group.
	 *
	 * @param 	int     	$id			ID of group to add members to.
	 * @param 	int|null	$uID		Optional. ID of user to add/ reject. Default is current user. Not used if $all = true
	 * @param 	bool 		$all		Optional. Approve/ reject all pending members. Default is false.
	 * @param 	bool		$approve	Optional. If true user(s) are added, if false, rejected. Default is true.
	 *
	 * @return 	int|array	$id		 	ID of group pending member(s) were approved to join.
	 *
	 * @since 	0.0.1
	 */
	function pending( $id, $uID = null, $all = false, $approve = true ) {
		$uID = $this->null_user( $uID );
		$obj = $this->single_group_object( $id, $cached = false );

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

						$this->reset_cache( $id );

						return $id;
					}
					else {
						$id = $obj->save( 'pending_members', $none );

						$this->reset_cache( $id );

						return $id;
					}

				}
				elseif ( is_int( $pending ) ) {
					if ( $approve ) {
						$members[ ] = $pending;
						$id = $obj->save( 'members', $members );
						$obj->save( 'pending_members', $none );

						$this->reset_cache( $id );

						return $id;
					}
					else {
						$pending = $obj->field( 'pending_members.ID' );
						if( ( $key = array_search( $uID, $pending ) ) !== false) {
							unset( $pending[ $key ] );
						}

						$obj->save( 'pending_members', $pending );

						$this->reset_cache( $id );
					}

				}
				else {
					holotree_error();
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
			$this->reset_cache( $id );

			return $id;
		}

	}

	function form_fix_jQuery( $jQuery, $new ) {

		$jQuery =  "$( 'li.pods-form-ui-row-name-members, li.pods-form-ui-row-name-pending-members, li.pods-form-ui-row-name-decisions, li.pods-form-ui-row-name-organization' ).hide();";

		return $jQuery;
	}

	/**
	 * IDs of all groups that a given user is a member of.
	 *
	 * @param   null|String 	$uID	Optional. User ID to search for groups of. Defaults to current user.
	 * @param 	null 		$obj	Optional. Prebuilt Pods object to run find on.
	 * @param 	bool			$name	Optional. Whether to add names to output array. Defaults to false.
	 * @param	int|array|false	$oID	Optional. ID or array of IDs organization(s), if not false, will only return groups from the specified organization(s).
	 *
	 *
	 * @return	array				Array of group IDs and optionally names
	 */
	function users_groups( $uID = null, $name = false, $obj = null, $oID = false ) {
		$uID = $this->null_user( $uID );
		$obj = $this->users_groups_obj( $uID, $obj, 5, $oID );

		if ( $obj->total() > 0 ) {
			while ( $obj->fetch() ) {
				$id = $obj->ID();
				if ( $name ) {
					$groups[ $id ] = array(
						'ID' 	=> $id,
						'name'	=> $obj->field( 'post_title' ),
					);
				}
				else {
					$groups[ $id ] = $id;
				}

			}

		}

		if ( ! empty( $groups ) ) {
			return $groups;

		}

	}

	/**
	 *  Group object with only groups that a user is a member of.
	 *
	 * @param   null|String $uID	Optional. User ID to search for groups of. Defaults to current user.
	 * @param 	null 		$obj	Optional. Prebuilt Pods object to run find on.
	 * @param 	int  		$limit 	Optional. Number of items per page to return. Default is 5.
	 * @param	int|array|false	$oID	Optional. ID or array of IDs organization(s), if not false, will only return groups from the specified organization(s).
	 *
	 * @return 	null|Pods 	$obj	Pods object of groups CPT.
	 *
	 * @since 	0.0.1
	 */
	function users_groups_obj( $uID = null, $obj = null, $limit = 5, $oID = false ) {

		$uID = $this->null_user( $uID );

		$where = 'members.ID = "'.$uID.'"';

		if ( $oID  ) {
			$where .= ' AND organization.ID = "' . $oID . '" ';

		}

		$params[ 'where' ] = $where;

		$obj = holotree_group_class()->null_obj( $obj, $params );

		return $obj;

	}

	/**
	 * Check if a group has open access.
	 *
	 * @param   int   		$id
	 * @param 	obj|null 	$obj
	 *
	 * @return 	bool			Whether or not group is "open access"
	 */
	function open_access( $id, $obj = null ) {

		return holotree_membership_class()->open_access( $id, $obj, true );

	}

	/**
	 * Update user fields related to this post type.
	 *
	 * Workaround for Pods issue #1945
	 * @see https://github.com/pods-framework/pods/issues/1945
	 *
	 * @param $pieces
	 * @param $is_new_item
	 * @param $id
	 *
	 * @uses 'pods_api_post_save_pod_item_' hook
	 *
	 * @return mixed
	 *
	 * @since 0.0.1
	 */
	function user_fix( $pieces, $is_new_item, $id ) {

		if ( isset( $pieces[ 'fields' ][ 'members' ][ 'value' ] ) ) {
			$uID = (int)$pieces[ 'fields' ][ 'members' ][ 'value' ];
			if ( is_int( $uID ) ) {
				$this->user_meta( $id, $uID );
			}
		}

		return $pieces;
	}

	/**
	 * Updates user meta manually.
	 *
	 * Part of workaround for Pods issue #1945
	 *
	 * @param 	int	$id		ID of group change is from.
	 * @param 	int	$uID	IF of user to update meta of
	 *
	 * @since	0.0.1
	 */
	function user_meta( $id, $uID ) {
		if ( get_user_by( 'id', $uID ) !== false ) {
			$groups = get_user_meta(  $uID, 'groups.ID' );
			$groups[] = $id;
			update_user_meta( $uID, 'groups', $groups );
		}
	}

} 
