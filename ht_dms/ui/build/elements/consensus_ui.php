<?php
/**
 * Consensus Views
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\ui\build\elements;

use ht_dms\ui\output\elements;

class consensus_ui {
	/**
	 * Visual display of the current status of a consensus_ui
	 *
	 * @since 0.3.0
	 *
	 * @param int $dID Decision ID
	 *
	 * @return string
	 */
	public static function view( $dID = null) {
		if ( is_null( $dID ) ) {
			$dID = (int) get_queried_object_id();
		}

		if ( ! ht_dms_is_decision( $dID ) ) {
			return;
		}

		$content = false;
		$id = "consensus_ui-view";

		$out = ht_dms_ui()->view_loaders()->handlebars( 'consensus_view', false, false );

		$out .= "<div id=\"{$id}\" class=\"consensus_ui-view\" did=\"{$dID}\">{$content}</div>";

		return $out;

	}

	/**
	 * Modal to show a decision in.
	 *
	 * @since 0.3.0
	 *
	 * @param int $dID Decision ID
	 *
	 * @return string
	 */
	public static function modal( $dID ) {
		if ( ! ht_dms_is_decision( $dID ) ) {
			return;
		}

		$text = __( 'View Consensus', 'ht-dms' );

		$atts[ 'did' ] = $dID;

		$trigger = modal::make( 'consensus_details', $atts, $text );

		return $trigger;

	}

	/**
	 * Consensus data for rendering consensus_ui view via JavaScript
	 *
	 * @since 0.3.0
	 *
	 * @param int $dID Decision ID
	 *
	 * @return null|array
	 */
	public static function consensus_data( $dID ) {
		$consensus = ht_dms_consensus_class()->sort_consensus( $dID, true );
		if ( $consensus ) {
			$consensusMembers = $consensus;
			$data[ 'consensusHeaders' ] = pods_v( 'headers', $consensus, array() );

			if ( $consensusMembers ) {
				$data['consensusMembers'] = json_encode( $consensusMembers );
			}

			$data[ 'consensusMemberDetails' ] = ht_dms_sorted_consensus_details( $consensus );

			return $data;
		}

	}

	/**
	 * Header for consensus_ui tabs (or other use)
	 *
	 *
	 * @since 0.3.0
	 *
	 * Shows icon-status-count
	 *
	 * @param int $status_code 0|1|2
	 * @param int $count number of users with that status
	 *
	 * @return string
	 */
	public static function consensus_tab_header( $status_code, $count ) {
		$status = ht_dms_consensus_status_readable( $status_code, false, true );
		$icon   = self::consensus_icons( $status_code );
		if ( ! ht_dms_integer( $count ) ) {
			$count = 0;
		}

		if ( $status ) {

			return sprintf( '<div class="consensus_ui-tab-label">%1s<span class="status">%2s</span><span="count">%3s</span></div>', $icon, $status, $count );

		}

	}

	/**
	 * Returns an icon for consensus_ui code
	 *
	 * @param int $status_code 0|1|2
	 *
	 * @return mixed
	 *
	 * @since 0.3.0
	 */
	public static function consensus_icons( $status_code ) {
		$class = 'fa-2x';
		$build_elements = ht_dms_ui()->build_elements();
		$icons = array(
			0 => $build_elements->icon( 'silence', $class ),
			1 => $build_elements->icon( 'accepted', $class ),
			2 => $build_elements->icon( 'blocked', $class ),
		);

		$icons = apply_filters( 'ht_dms_consensus_icons', $icons );

		return pods_v( $status_code, $icons, false, false );

	}

	/**
	 * Create consensus headers
	 *
	 * @since 0.3.0
	 *
	 * @param array $count Counts by status of users with said status/
	 *
	 * @return string
	 */
	public static function consensus_headers( $count ) {


		for ( $i=0; $i<=2; $i++ ) {

			$headers[ $i ] = consensus_ui::consensus_tab_header( $i, pods_v( $i, $count, '' ) );

		}

		return $headers;

	}



} 
