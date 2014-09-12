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

namespace ht_dms\ui\output;


class ajax_callbacks {

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
					//$users = holotree_decision_class()->consensus_members( $dID );
					$consensus = holotree_dms_ui()->output_elements()->view_consensus( $_REQUEST[ $dID ] );

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

				wp_die( holotree_dms_ui()->views()->notification( null, $nID ) );

			}

		}

	}

	function update_decision_status() {
		if ( $this->nonce_check( $_REQUEST ) ) {
			$dID = pods_v( 'dID', $_REQUEST );
			if ( $dID ) {
				wp_die( ucwords( holotree_decision_class()->status( $dID ) ) );

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

}
