<?php
/**
 * Membership elements
 *
 * @package   ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\ui\build;


class membership {

	function join( $gID, $obj = null ) {
		$obj = ht_dms_group( $gID, $obj );
		$access = ht_dms_group_class()->open_access( $gID, $obj );
		if ( $access == 0 ) {
			$access_label = __( 'Membership to this group does not require approval.', 'ht_dms' );
		}
		else {
			$access_label = __( 'Membership to this group requires approval.', 'ht_dms' );
		}

		return  $this->output_container(
			ht_dms_caldera_loader(
				ht_dms_ui()->caldera_actions()->join_group_form_id,
				__( 'Click to join this group', 'ht_dms' ),
				$access_label
			),
			__FUNCTION__
		);

	}

	function leave() {
		$message = __( 'Click to leave this group.', 'ht_dms' );

		return  $this->output_container(
			ht_dms_caldera_loader( ht_dms_ui()->caldera_actions()->leave_group_form_id, $message ),
			__FUNCTION__
		);

	}

	function pending() {
		$message = __( 'Approve or reject pending members to this group.', 'ht_dms' );

		return  $this->output_container(
			ht_dms_caldera_loader( ht_dms_ui()->caldera_actions()->group_pending_form_id, $message ),
			__FUNCTION__
		);

	}

	function view( $gID, $obj = null ) {
		$obj = ht_dms_group( $gID, $obj );
		$members = ht_dms_group_class()->all_members(  $gID, $obj );

		$out = false;
		if ( is_array( $members ) ) {
			$fallback = ht_dms_fallback_avatar();
			foreach( $members as $member ) {
				$member_data =  get_userdata( $member );
				$view = sprintf(
						'<div id="group-member-details" class="row">
							<div class="large-3 small-12 columns">
								%1s
							</div>
							<div class="large-9 small-12 columns">
								%2s
							</div>
						</div>
					',
					get_avatar( $member, '128', $fallback  ),
					$member_data->data->display_name
				);

				$out[] = sprintf( '<li class="group-member-view">%1s</li>', $view );
			}

		}

		if ( is_array( $out ) ) {

			return sprintf( '<div id="group-members-view"><ul>%1s</ul></div>', implode( $out ) );

		}

	}

	private function output_container( $content, $function, $type = 'group' ) {

		$out = sprintf( '<div id="%1s-%2s">%3s</div>', $function, $type, $content );

		if ( is_string( $out ) ) {

			return $out;

		}


	}

	/**
	 * @return \ht_dms\ui\ui
	 */
	private function ui() {
		return ht_dms_ui();

	}


} 
