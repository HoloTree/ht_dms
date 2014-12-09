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
		if ( ! is_null( $gID ) || ! is_null( $status ) ) {
			$obj = self::query( $params, $status, $gID  );

			if ( ! is_object( $obj ) ) {
				return 550;

			}
		}
		else {
			return 550;

		}

		$output[ 'outer_html_id' ] = '#decision-' . $status;
		$output[ 'html_id' ] = $html_id = str_replace( '_', '-', $output[ 'outer_html_id' ] ).'-container';

		$output[ 'template_id' ] = '#decision-preview';
		$output[ 'template' ] = ht_dms_ui()->view_loaders()->handlebars_template( 'decision-preview' );
		$file = str_replace( '#', '', $output[ 'template_id' ] );
		holotree_enqueue_handlebar( $file, ht_dms_ui()->view_loaders()->handlebars_template_file_location( $file, true ) );

		$total = (int) $obj->total();
		$total_found = (int) $obj->total_found();

		if ( 0 == $total_found ) {
			$total_found = $total;
		}

		$obj = self::query( $params, $status, $gID  );
		$output[ 'total' ] = $total;
		$output[ 'total_found' ] = $total_found;
		$output[ 'json' ] = self::json( $obj );
		$output[ 'html' ] = $html = ht_dms_ui()->view_loaders()->handlebars_container( $html_id );

		return $output;
	}

	/**
	 * Do query
	 *
	 * @access private
	 *
	 * @since 0.2.0
	 *
	 * @param array $params
	 * @param string $status
	 * @param int $gID
	 * @return array|bool|mixed|null|\Pods|void
	 */
	private static function query( $params, $status, $gID ) {
		$obj = ht_dms_decision_class()->decisions_by_status(
			$status,
			$gID,
			'obj',
			null,
			pods_v( 'page', $params, 1 ),
			pods_v( 'limit', $params, 5 )
		);

		return $obj;

	}

	/**
	 * Get JSON for view.
	 *

	 *
	 * @return mixed|string|void
	 */
	private static function json( $obj ) {


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
