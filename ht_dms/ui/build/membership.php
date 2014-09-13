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
		$obj = holotree_group( $gID, $obj );
		if ( $access == 0 ) {
			$access_label = __('Membership to this group does not require approval.', 'holotree' );
		}
		else {
			$access_label = __('Membership to this group requires approval.', 'holotree' );
		}

		return  $this->ouptut_container(
			ht_dms_caldera_loader( \ht_dms\helper\caldera_actions::$join_group_form_id, $access_label ),
		__FUNCTION__
		);

	}

	function leave() {
		$message = __( 'Click to leave this group.', 'ht_dms' );

		return  $this->ouptut_container(
			ht_dms_caldera_loader( \ht_dms\helper\caldera_actions::$leave_group_form_id, $message ),
			__FUNCTION__
		);

	}

	function pending() {

	}

	function view() {

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
		return holotree_dms_ui();

	}


} 
