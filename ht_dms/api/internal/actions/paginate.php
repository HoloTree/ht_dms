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
		$view = pods_v( 'view', $params );
		$limit = pods_v( 'limit', $params );
		$page = pods_v( 'page', $params );
		$extra_arg = pods_v( 'extra_arg', $params );

		$args = array(
			'limit' => $limit,
			'page' => $page,
		);

		if ( in_array( $view, array( 'users_groups', 'public_groups' ) ) && ! is_null( $oID = pods_v( 'oID', $params ) ) ) {
			$args[ 'oID' ] = $oID;
		}

		if ( $view == 'users_notifications' ) {
			$args[ 'un_viewed_only' ] = $extra_arg;
		}

		$view_args = array_keys( self::view_args()  );
		if ( in_array( $view, $view_args  ) ) {
			$out = self::pagination_views( $view, $args );
			return $out;

		}
	}

	/**
	 * Args for this action.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public static function args() {

		return array( 'view', 'limit', 'page', 'extraArg', 'oID' );

	}

	/**
	 *  Loads the paginated view.
	 *
	 * @param string     $view View to load. Must be in whitelist created by ht_dms_paginated_views().
	 * @param array     $args Arguments to pass to the view.
	 * @param bool $return_obj Optional. Whether to return object (if true) or render templates (false, the default.)
	 *
	 * @return \ht_dms\ui\JSON|\ht_dms\ui\obj|\ht_dms\ui\Pods|null|string
	 */
	private  static function pagination_views( $view, $args, $return_obj = false ) {
		$args[ 'return' ] = 'Pods';
		$pagination_args_and_path = self::view_args( $args );
		$pagination_args_and_path = pods_v( $view, $pagination_args_and_path );
		$view_args = pods_v( 'args', $pagination_args_and_path  );

		$obj = ht_dms_ui()->get_view( $view, $view_args, 'Pods' );

		if ( in_array( $view, array( 'users_groups', 'public_groups', 'users_organizations' ) ) ) {
			$html_id = str_replace( '_', '-', $view ).'-container';
			if ( $obj ) {
				if ( in_array( $view, array( 'public_groups', 'users_groups' ) ) ) {
					$template_id = 'group-preview';
					$js  = "groupPreview( {$obj}, '{$template_id}', '{$html_id}' );";
					$out = ht_dms_ui()->view_loaders()->handlebars( $template_id, $html_id, $js );
				} elseif( $view == 'users_organizations') {
					$template_id = 'organization-preview';
					$js = "organizationPreview( {$obj}, '{$template_id}', '{$html_id}' );";
					$out = ht_dms_ui()->view_loaders()->handlebars( $template_id, $html_id, $js );
				}
			}
			else {
				$out = __( 'No items found.', 'ht_dms' );
			}

			return $out;

		}
		elseif ( $return_obj === true ) {

			return $obj;

		}
		else {
			if ( ! is_object( $obj )  ) {
				ht_dms_error();
			}
			$template_file = trailingslashit( HT_DMS_VIEW_DIR ).'partials/';

			$template_file .= pods_v( 'view', $pagination_args_and_path );

			$obj->find( array( 'page' => $args[ 'page' ], 'limit' => $args[ 'limit' ] ) );


			if ( $obj->total() > 1 ) {
				$out = '';
				if ( ! file_exists( $template_file ) ) {
					return false;
				}

				$out .= ht_dms_ui()->view_loaders()->magic_template( $template_file, $obj, pods_v( 'page', $pagination_args_and_path, false, true ) );

				if ( ! empty ( $out ) ) {
					$out .= ht_dms_ui()->build_elements()->ajax_pagination_buttons( $obj, $view, $args[ 'page' ] );
					$out = apply_filters( 'ht_dms_paginated_views_template_output', $out, $view );

					return $out;

				}


			}

		}

	}

	/**
	 * Returns an array of the allowed paginated views, foreach an array of arguments for calling their view method and the view partial file name.
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
