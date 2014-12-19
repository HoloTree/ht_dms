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


use ht_dms\ui\build\elements\consensus;

class consensus_details {

	public static function act( $params ) {
		return consensus::consensus_data( $params[ 'did' ] );
	}

	public static function args() {
		return array( 'did' );

	}
} 
