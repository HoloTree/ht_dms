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

namespace ht_dms\api\internal\actions\paginate;


use ht_dms\helper\json;

class decision {

	/**
	 * Create decision by status output.
	 *
	 * @since 0.2.0
	 *
	 * @param array $params
	 *
	 * @return array|string
	 */
	public static function output( $params ) {
		$gID = pods_v( 'gid', $params );
		$status = pods_v( 'status', $params );
		if ( is_null( $gID ) || is_null( $status ) ) {
			return 404;
		}

		$output[ 'outer_html_id' ] = '#decision-' . $status;
		$output[ 'html_id' ] = $html_id = str_replace( '_', '-', $output[ 'outer_html_id' ] ).'-container';

		$output[ 'template_id' ] = '#decision-preview';
		$output[ 'template' ] = ht_dms_ui()->view_loaders()->handlebars_template( 'decision-preview' );

		$output[ 'json' ] = self::json( $params, $status, $gID );
		$output[ 'html' ] = $html = ht_dms_ui()->view_loaders()->handlebars_container( $html_id );

		return $output;
	}

	/**
	 * Get JSON for view.
	 *
	 * @access private
	 *
	 * @since 0.2.0
	 *
	 * @param array $params
	 * @param string $status
	 * @param int $gID
	 *
	 * @return mixed|string|void
	 */
	private static function json( $params, $status, $gID ) {
		$obj = ht_dms_decision_class()->decisions_by_status(
			$status,
			$gID,
			'obj',
			null,
			pods_v( 'page', $params, 1 ),
			pods_v( 'limit', $params, 5 )
		);

		$data = self::loop( $obj );

		return json_encode( $data );

	}

	/**
	 * Loop the results using the JSON builder method.
	 *
	 * @access private
	 *
	 * @since 0.3.0
	 *
	 * @param obj|\Pods $obj Pods object to loop
	 *
	 * @return array
	 */
	private static function loop( $obj ) {
		$data = array();
		if ( is_object( $obj ) && $obj->total > 0 ) {
			while ( $obj->fetch()  ){
				$id = $obj->id();
				$data[ ] = json::decision( $id, $obj );
			}

		}


		return $data;

	}

} 
