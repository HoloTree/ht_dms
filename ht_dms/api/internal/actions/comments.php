<?php
/**
 * Return comments.
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\api\internal\actions;

use ht_dms\ui\output\elements;

class comments {
	public static function act( $params ) {
		$id = pods_v_sanitized( 'id', $params );

		if ( $id ) {
			$output[ 'json' ] = elements::comment_json( $id );
			$output[ 'template' ] = ht_dms_ui()->view_loaders()->handlebars_template( 'comments' );
			return $output;
		}



	}

	public static function args() {
		return array( 'id' );
	}
} 
