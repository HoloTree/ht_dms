<?php
/**
 * Paginated views
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\api\internal\actions;


use ht_dms\api\internal\actions\paginate\decision;

class paginate {

	/**
	 * Route paginated view request via internal api
	 *
	 * @since 0.1.0
	 *
	 * @param $params
	 *
	 * @return \ht_dms\ui\JSON|\ht_dms\ui\obj|\ht_dms\ui\Pods|null|string
	 */
	public static function act( $params ) {

		if ( is_string( pods_v( 'status', $params ) ) ) {
			return decision::output( $params );

		}

		$view = pods_v( 'view', $params );
		$limit = (int) pods_v( 'limit', $params );
		$page = (int) pods_v( 'page', $params );
		$extra_arg = pods_v( 'extra_arg', $params );
		global $cuID;

		$args = array(
			'limit' => $limit,
			'page'  => $page,
			'mine'  => $cuID,
			'uID'   => $cuID,
		);

		if ( in_array( $view, array( 'users_groups', 'public_groups' ) ) && ! is_null( $oID = pods_v( 'oID', $params ) ) ) {
			$args[ 'oID' ] = $args[ 'in']  = $oID;
		}

		if ( $view == 'users_notifications' ) {
			if ( isset( $params[ 'extraArg' ] ) &&
			     ( "false" === $params[ 'extraArg' ] || false == $params[ 'extraArg' ] )
			) {
				$extra_arg = false;
			}
			elseif ( is_null( $extra_arg ) ) {
				$extra_arg = true;
			}

			$args[ 'un_viewed_only' ] = $extra_arg;
		}

		$view_args = array_keys( self::view_args()  );
		if ( in_array( $view, $view_args  ) ) {
			$out = self::output( $view, $args );
			return $out;

		}

	}

	private static function store_last_view_args( $view, $args ) {

	}

	/**
	 * Args for this action.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public static function args() {

		return  array( 'view', 'limit', 'page', 'extraArg', 'oID', 'status', 'gid' );

	}

	/**
	 * Build return data
	 *
	 * @since 0.1.0
	 *
	 * @param string $view Which view to load
	 * @param array $args
	 *
	 * @return array Data to return via the internal API.
	 */
	private static function output( $view, $args ) {

		if ( in_array( $view, array( 'public_groups', 'users_groups' ) ) ) {
			$template_id = 'group-preview';
			$type = 'group';
		}
		elseif ( $view == 'users_organizations') {
			$template_id = 'organization-preview';
			$type = 'organization';
		}
		elseif( $view == 'users_notifications' ) {
			$template_id = 'notification-preview';
			$type = 'notification';
		}
		else {
			ht_dms_error();
		}

		$page = pods_v( 'page', $args, 1, true );

		$output[ 'outer_html_id' ] = '#' . $view;
		$output[ 'html_id' ] = $html_id = str_replace( '_', '-', $view ).'-container';

		$output[ 'template_id' ] = $template_id;
		$output[ 'template' ] = ht_dms_ui()->view_loaders()->handlebars_template( $template_id );

		$view = self::get_view( $view, $args, $html_id, $type, $page );
		$output[ 'json' ] = pods_v( 'json', $view );
		$output[ 'html' ] = pods_v( 'html', $view );
		$output[ 'total' ] = pods_v( 'total', $view );
		$output[ 'total_found' ] = pods_v( 'total_found', $view );

		return $output;
	}

	/**
	 * Get JSON data and HTML for the view
	 *
	 *
	 * @since 0.1.0
	 *
	 * @param string $view Name of view
	 * @param array $args
	 * @param string $html_id ID of HTML container
	 * @param string $type (short) name/type of view.
	 * @param int $page Optional. Current page for view. Defaults to 1.
	 *
	 * @return array containing 'json' and 'html' keys.
	 */
	private static function get_view( $view, $args, $html_id, $type, $page = 1 ) {

		$args['return'] = 'simple_json';
		$total = $total_found = 0;

		if ( ! isset( $args[ 'page' ] ) ) {
			$args[ 'page' ] = $page;
		}

		if ( 'decision' == $view && isset( $args[ 'status' ] ) && isset( $args[ 'gID' ] ) ) {
			$obj = ht_dms_decision_class()->decisions_by_status(  $args[ 'status' ], $args[ 'gID' ], 'obj');

		} else {
			$obj = call_user_func( array( ht_dms_ui()->views(), $view ), $args );
		}
		if ( $obj ) {
			$json =  $obj;
		}
		else {
			$json = json_encode( array( 0 ) );
		}

		$totals      = \ht_dms\ui\build\models::get_total( $type );
		$total       = pods_v( 'total', $totals );
		$total_found = pods_v( 'total_found', $totals );
		if ( 0 == $total_found ) {
			$total_found = $total;
		}

		$html = ht_dms_ui()->view_loaders()->handlebars_container( $html_id );

		$controls = '#' . $view;
		$html .= ht_dms_ui()->build_elements()->ajax_pagination_buttons( 'force', $view, $page, $type, $controls );

		$html = apply_filters( 'ht_dms_paginated_views_template_output', $html, $view );

		return array(
			'json' => $json,
			'html' => $html,
			'total' => $total,
			'total_found' => $total_found,
		);

	}

	/**
	 * Returns an array of the allowed paginated views, foreach an array of arguments for calling their view method and the view partial file name.
	 *
	 * @todo method that grabs just the path from this and use that on L48 instead.
	 *
	 * @param null $args
	 *
	 * @return mixed|void
	 *
	 * @since 0.0.1
	 */
	private static function view_args( $args = null ) {
		if ( is_null( $args ) ) {
			$args = array( 'limit' => 5, 'page' => 1 );
		}

		foreach( array( 'oID', 'dID', 'uID' ) as $index ) {
			if ( ! isset( $args[ $index ] ) ) {
				if ( $index == 'uID' ) {
					$args[ $index ] = get_current_user_id();
				}
				else {
					$args[ $index ] = null;
				}


			}

		}

		if ( ! isset( $args[ 'un_viewed_only' ] ) ) {
			$args[ 'un_viewed_only' ] = false;
		}

		$paginated_views = array(
			'users_groups' => array(
				'args' => array( null, get_current_user_id(), $args[ 'oID' ], $args[ 'limit'], 'simple_json', $args[ 'page'] ),
				'view' => 'group_preview.php',
			),
			'public_groups' => array(
				'args' => array( null, $args[ 'oID' ], $args[ 'limit' ], 'simple_json', $args[ 'page'] ),
				'view' => 'group_preview.php',
			),
			'users_organizations' => array(
				'args' => array( null, $args[ 'oID' ], $args[ 'limit' ], 'simple_json', $args[ 'page'] ),
				'view' => 'organization_preview.php',
			),
			'assigned_tasks' => array(
				'args' => array( null, $args[ 'uID' ], $args[ 'oID' ], $args[ 'limit' ], 'Pods', $args[ 'page'] ),
				'view' => 'task_preview.php',
			),
			'decisions_tasks' => array(
				'args' => array( null, $args[ 'uID' ], $args[ 'dID' ], $args[ 'limit' ], 'Pods', $args[ 'page'] ),
				'view' => 'task_preview.php',
			),
			'users_notifications' => array(
				'args' => array( null, get_current_user_id(), $args[ 'un_viewed_only'], $args[ 'limit'], 'Pods', $args[ 'page' ] ),
				'view' => 'notification_preview.php',
			),
		);

		return apply_filters( 'ht_dms_paginated_view_args', $paginated_views );

	}

} 
