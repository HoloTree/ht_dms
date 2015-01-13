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



class consensus_details {

	/**
	 * Process and return
	 *
	 * @since 0.3.0
	 *
	 * @param $params
	 */
	public static function act( $params ) {
		if ( ! is_null( $dID = pods_v( 'did', $params ) ) ) {
			$consensus_data = \ht_dms\ui\build\elements\consensus_ui::consensus_data( $dID );

			$data[ 'details' ] = ht_dms_sorted_consensus_details( $consensus_data );
			$data[ 'did' ] = $dID;
			$data[ 'headers' ] = pods_v( 'consensusHeaders', $consensus_data, array() );

			return $data;
		}

	}

	/**
	 * Args to pass
	 *
	 * @since 0.3.0
	 *
	 * @return array
	 */
	public static function args() {
		return array( 'did' );

	}

	/**
	 * Transport method
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	public static function method() {
		return 'POST';

	}

	public static $data_json = false;

} 
