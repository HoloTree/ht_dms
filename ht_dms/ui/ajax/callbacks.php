<?php
/**
 * AJAX Callback functions
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\ui\ajax;


class Callbacks  {
	const ACTION = 'ht_dms_';



	/**
	 * Reload consensus on ht_dms_reload_consensus AJAX action
	 *
	 * @uses 'wp_ajax_ht_dms_reload_consensus' action
	 *
	 * @since 0.0.3
	 */
	function reload_consensus( ) {
		if ( $this->nonce_check( $_REQUEST ) ) {

			$dID = pods_v( 'dID', $_REQUEST );
			if ( $dID ) {
				$post = get_post( $dID );
				if ( is_object( $post ) && isset( $post->post_type ) && HT_DMS_DECISION_CPT_NAME === $post->post_type ) {
					wp_cache_flush();
					//$users = ht_dms_decision_class()->consensus_members( $dID );
					$consensus = ht_dms_ui()->output_elements()->view_consensus( $dID );

					if ( is_string( $consensus ) ) {
						wp_die(  $consensus );
					}

				}

			}

		}

	}

	/**
	 * Reload notification on ht_dms_notification action
	 *
	 * @uses 'wp_ajax_ht_dms_notification'
	 *
	 * @since 0.0.3
	 */
	function load_notification( ) {
		if ( $this->nonce_check( $_REQUEST ) ) {
			$nID = pods_v( 'nID', $_REQUEST );
			if ( $nID ) {

				wp_die( ht_dms_ui()->views()->notification( null, $nID ) );

			}

		}

	}

	/**
	 * Update decision status on ht_dms_update_decision_status AJAX action
	 *
	 * @uses 'wp_ajax_ht_dms_update_decision_status' action
	 *
	 * @since 0.0.3
	 */
	function update_decision_status() {
		if ( $this->nonce_check( $_REQUEST ) ) {
			$dID = pods_v( 'dID', $_REQUEST );
			if ( $dID ) {
				wp_die( ucwords( ht_dms_decision_class()->status( $dID ) ) );

			}

		}
	}

	/**
	 * Reload's membership view on ht_dms_reload_membership AJAX action
	 *
	 * @uses 'wp_ajax_ht_dms_reload_membership' action
	 *
	 * @since 0.0.3
	 */
	function reload_membership() {
		if ( $this->nonce_check( $_REQUEST ) ) {
			$gID = pods_v( 'gID', $_REQUEST );
			if ( $gID ) {
				wp_die( ht_dms_ui()->build_elements()->group_membership( $gID) );
			}
		}
	}

	/**
	 * Check a nonce
	 *
	 * @param array      $REQUEST The array with the nonce in it, probably $_REQUEST
	 * @param string $nonce Name of nonce.
	 * @param bool   $message Optional. Fail message.
	 *
	 * @return bool	Returns true if nonce is good.
	 *
	 * @since 0.0.3
	 */
	private function nonce_check( $REQUEST, $nonce = 'ht-dms', $message = false ) {
		if ( ! $message ) {
			$message = __( 'Request denied for security reasons.', 'ht_dms' );
		}

		if ( isset( $REQUEST['nonce'] ) ) {
			if ( ! wp_verify_nonce( $REQUEST[ 'nonce' ], $nonce ) ) {

				wp_die(  $message  );

			}

			return true;

		}

	}

	/**
	 * Mark a notification viewed or unviewed via AJAX
	 *
	 * @uses 'wp_ajax_ht_dms_mark_notification' action
	 *
	 * @since 0.0.3
	 */
	function mark_notification()  {
		if ( $this->nonce_check( $_REQUEST ) ) {
			$nID = pods_v( 'nID', $_REQUEST );
			$value =  ( pods_v( 'mark', $_REQUEST ) );

			if ( $nID && in_array( $value, array( 1, 0 ) ) ) {
				$id = ht_dms_notification_class()->viewed( $nID, null, $value );

				if ( $id == $nID ) {
					wp_die( 1 );
				}
				else {
					wp_die( 0 );
				}
			}

		}

	}

	/**
	 * Returns an array, which is used in the common class' __construct() to build hooks for AJAX actions.
	 *
	 * @return array
	 *
	 * @since 0.0.3
	 */
	public function callbacks() {

		$callbacks = get_class_methods( __CLASS__ );
		foreach( array( 'nonce_check', 'callbacks' ) as $unset ) {
			unset( $callbacks [ array_search( $unset, $callbacks ) ] );
		}

		return $callbacks;

	}

}
