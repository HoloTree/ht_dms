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

namespace ht_dms\api\internal\actions;


class members {

	/**
	 * Get members of a group/organixation
	 *
	 *
	 * @since 0.1.0
	 * @param array $params
	 *
	 * @return array|string
	 */
	public static function act( $params ) {
		$id = pods_v_sanitized( 'id', $params );
		$type = pods_v_sanitized( 'type', $params );
		if ( $id && $type ) {
			if ( in_array( $type, array( 'group', 'organization' ) ) ) {
				$is_group = false;
				if ( $type == 'group' ) {
					$is_group = true;
				}

				$members = ht_dms_membership_class()->all_members( $id, null, $is_group );
				$members = ht_dms_ui()->output_elements()->members_details_view( $members, 20, 20, true );
				if ( is_string( $members ) ) {
					return $members;
				}


			}

		}

	}

	/**
	 * Args for this action.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public static function args() {

		return array( 'id', 'type' );

	}
}
