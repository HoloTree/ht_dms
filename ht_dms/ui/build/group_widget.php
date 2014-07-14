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

namespace ht_dms\ui\build;

class group_widget {
	/**
	 * Panel for joining or approving members to join a group.
	 *
	 * @TODO Aprovals. Requires adding a role 'aprove_members' or something.
	 *
	 * @param 	int     	$gID	ID of group.
	 * @param 	null|obj 	$obj	Optional. Pods object of group class.
	 *
	 * @return	string		$out	The panel.
	 *
	 * @since	0.0.1
	 */
	function join_group_widget( $gID, $obj = null ) {
		if ( is_null( $obj ) ) {
			$obj = holotree_group_class()->null_object( $obj, $gID );
		}
		$access = $obj->field( 'open_access' );

		if ( $access == 0 ) {
			$access_label = __('Membership to this group does not require approval.', 'holotree' );
		}
		else {
			$access_label = __('Membership to this group requires approval.', 'holotree' );
		}
		$out = '<aside id="join-panel-'. $gID. '" class="join-panel panel">';

		if ( holotree_group_class()->is_pending( null, $gID, $obj ) ) {
			$out .= $this->ui()->elements()->alert( __( 'Your membership in this group is pending', 'holotree' ) );
		}
		else {
			$link = holotree_action_append( get_permalink( $gID ), 'join-group', $gID );
			$out .= '<a href="' . $link . '" class="button join-group-button">Join Group</a><br />';
		}

		$out .= '<em>'.$access_label.'</em>';
		$out .= '</aside>';

		return $out;
	}

	/**
	 * Panel for listing group members
	 *
	 * @param 	int     	$gID	ID of group.
	 *
	 * @return	string		$out	The panel.
	 *
	 * @since	0.0.1
	 */
	function group_members_widget( $gID ) {
		$members = holotree_group_class()->all_members( $gID );
		if ( is_array( $members ) ) {
			$out = '<aside class="group-members-panel panel" id="group-members-panel-'.$gID.'">';
			$out .= '<h5 class="widget-title">Group Members</h5>';
			foreach ( $members as $member ) {
				$user = get_userdata( $member );
				$first_name = $user->first_name;
				$last_name = $user->last_name;

				$fallback = 'http://www.adiumxtras.com/images/pictures/futuramas_bender_dock_icon_1_8169_3288_image_4129.png';
				/**
				 * Fallback avatar for users without one set.
				 *
				 * @param $fallback url of fallback image.
				 *
				 * @since 0.0.1
				 */
				$fallback = apply_filters( 'ht_dms_fallback_avatar', $fallback );

				$avatar = get_avatar( $member, 96, $fallback );
				$out .= '<div class="group-member row">';
				$out .= '<div class="large-3 columns group-member-avatar">';
				$out .= $avatar;
				$out .= '</div><!--.group-member-avatar-->';
				$out .= '<div class="large-9 columns group-member-info">';
				if ( $first_name != '' && $last_name != '' ) {
					$out .= $first_name. ' ' . $last_name;
				}
				else {
					$out .= $user->user_login;
				}
				//@TODO Info about member.
				$out .= '</div><!--.group-member-info-->';
				$out .= '</div><!--.group-member-->';
			}

			$out .= '</aside><!--.group-members-panel-->';

			return $out;
		}


	}

	/**
	 *
	 * @TODO A handler for this.
	 * @TODO Link to user profile.
	 * @TODO Correct get var names
	 *
	 * @param $gID
	 *
	 * @return string
	 */
	function group_approve_widget( $gID ) {

		$dms = holotree_group_class();
		if ( holotree_common_class()->is_facilitator( null, $gID ) ) {
			$pending = holotree_group_class()->get_pending( $gID );
			//@todo what does a lack of pending members actually look like?
			if ( $pending != '' ) {
				$out = '<aside class="group-members-panel panel" id="group-members-approve-panel-'.$gID.'">';
				$out .= '<h5 class="widget-title">Approve Or Reject Members</h5>';
				$form = '<form action="' . $this->ui()->elements()->current_page_url() . '" method="get" id="dms-approve-members-form">';
				$form .= '<select name="dms_member_id">';
				foreach ( $pending as $member ) {
					//@TODO link to a user profile
					$user_info = get_userdata( $member );
					$first_name = $user_info->first_name;
					$last_name = $user_info->last_name;
					if ( $first_name != '' || $last_name != '' ) {
						$name = $first_name .' '.$last_name;
					}
					else {
						$name = $user_info->user_login;
					}
					$form .= '<option value="' . $member . '">' . $name . '</option>';
					//@TODO WHAT IS VALUE?

				}
				$form .= '</select>';
				$form .= '<select name="dms_action">';
				$form .= '<option value="approve-pending">Approve</option>';
				$form .= '<option value="reject-pending">Don\'t Approve</option>';
				$form .= '</select>';
				$form .= '<input type="hidden" name="dms_id" value="' . $gID. '">';
				$form .= '<input type="submit" />';
				$form .= '</form>';
				$out .= $form;
				$out .= '</aside>';
				return $out;
			}
		}
	}

	/**
	 * Get instance of UI class
	 *
	 * @return 	\holotree\ui
	 *
	 * @since 	0.0.1
	 */
	function ui(){
		$ui = holotree_dms_ui();

		return $ui;

	}
} 
