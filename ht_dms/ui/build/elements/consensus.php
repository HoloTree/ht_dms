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


use ht_dms\ui\help\modals\modal;
use ht_dms\ui\output\elements;

class consensus {
	/**
	 * Visual display of the current status of a consensus
	 *
	 * @since 0.3.0
	 *
	 * @param int $dID Decision ID
	 *
	 * @return string
	 */
	public static function view( $dID = null  ) {
		if ( is_null( $dID ) ) {
			$dID = (int) get_queried_object_id();
		}

		if ( ! ht_dms_is_decision( $dID ) ) {
			return;
		}
		$out = ht_dms_ui()->view_loaders()->handlebars( 'consensus_view', false, false );

		$out .= "<div id=\"consensus-view-{$dID}\" class=\"consensus-view\" did=\"{$dID}\"></div>";

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
	 * Consensus data for rendering consensus view via JavaScript
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
} 
