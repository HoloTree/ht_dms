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

namespace ht_dms\ui\build\elements;


class decision {

	/**
	 * Prepare one or more decision by status result sets.
	 *
	 * @access private
	 *
	 * @since 0.2.0
	 *
	 * @param int $gID The ID of the group to query for decisions in.
	 * @param array|string|null $statuses Optional. A status to get, or an array of statuses. If null, the default, New', 'Blocked', 'Passed' will be returned.
	 */
	private function prepare( $gID, $statuses = null  ) {
		if ( is_null( $statuses )  ) {
			$statuses = array ( 'New', 'Blocked', 'Passed' );
		}

		if ( is_string( $statuses ) ) {
			$statuses = array( $statuses );
		}

		$cache_key = $gID.implode( $statuses );
		$cache_group = __CLASS__.__METHOD__;


		if ( ! HT_DEV_MODE || false == ( $decisions = pods_cache_get( $cache_key, $cache_group ) ) ) {
			$obj = ht_dms_decision_class()->object();
			$view_loaders         = ht_dms_ui()->view_loaders();
			$view                 = ht_dms_ui()->models()->path( 'decision', true );

			foreach ( $statuses as $status ) {
				$s_obj = ht_dms_decision_class()->decisions_by_status( $status, $gID, 'obj', $obj );

				if ( is_object( $s_obj ) ) {
					$obj = $s_obj;
					unset( $s_obj );

					if ( $obj->total() > 0 ) {
						$d_s                  = $view_loaders->magic_template( $view, $obj );
						$decisions[ $status ] = $d_s;


					} //endif have pods
					$obj->reset();
				}


			}

			pods_cache_set( $cache_key, $decisions, $cache_group, 99 );

		}

		return $decisions;

	}

	/**
	 *
	 * @since 0.2.0
	 *
	 * @param int $gID The ID of the group to query for decisions in.
	 * @param array|string|null $statuses Optional. A status to get, or an array of statuses. If null, the default, New', 'Blocked', 'Passed' will be returned.
	 */
	public function tabs( $gID, $statuses = null ) {
		$decisions = $this->tabs( $gID, $statuses );
		$tabs = array( );

		if ( isset( $decisions ) && is_array( $decisions ) ) {
			$content = '';
			foreach ( $statuses as $status  ) {

				if ( isset( $decisions[ $status ] ) ) {
					$content = '';
					$content .= '<div id="' . $status . '-decisions-list" class="decisions-list">';
					$heading = $status . ' Decisions';
					$content .= '<h3>' . $heading . '</h3>';
					$content .= $decisions[ $status ];
					$content .= '</div>';


					$tabs[] = array (
						'label'   => ht_dms_add_icon( $status . __( ' Decisions', 'ht_dms' ), strtolower( $status ) ),
						'content' => $content,
					);

					unset( $content );
				}

			}


		}

		if ( isset( $tabs ) && is_array( $tabs ) ) {

			return $tabs;

		}

	}

} 
