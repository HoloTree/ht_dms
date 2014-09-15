<?php
/**
 * Handles all paginated views loaded view ajax
 *
 * @package   ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

/**
 * Load the paginated view via ajax.
 */
add_action( 'wp_ajax_ht_dms_paginate', 'ht_dms_paginate');
add_action( 'wp_ajax_nopriv_ht_dms_paginate', '__return_false' );
function ht_dms_paginate() {
	if ( isset( $_REQUEST['nonce'] ) ) {
		if ( ! wp_verify_nonce( $_REQUEST[ 'nonce' ], 'ht-dms' ) ) {
			wp_die( __( 'Your attempt to request data via ajax using the function holotree_dms_ui_ajax_view was denied as the nonce did not match.', 'holotree' ) );
		}

		if ( isset( $_REQUEST[ 'view' ] ) && isset( $_REQUEST[ 'limit' ] ) && isset( $_REQUEST[ 'page' ] ) ) {
			$view = $_REQUEST[ 'view' ];
			$limit = $_REQUEST[ 'limit' ];
			$page = $_REQUEST[ 'page' ];
			$extra_arg = pods_v( 'extra_arg', $_REQUEST );

			$args = array(
				'limit' => $limit,
				'page' => $page,
			);

			if ( $view == 'users_notifications' ) {
				$args[ 'un_viewed_only' ] = $extra_arg;
			}

			$allowed_views = array_keys( ht_dms_paginated_views()  );
			if ( in_array( $view, $allowed_views  ) ) {
				wp_die( ht_dms_pagination_views( $view, $args ) );
			}

		}

	}

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
function ht_dms_pagination_views( $view, $args, $return_obj = false ) {
	$args[ 'return' ] = 'Pods';
	$pagination_args_and_path = ht_dms_paginated_views( $args );
	$pagination_args_and_path = pods_v( $view, $pagination_args_and_path );
	$view_args = pods_v( 'args', $pagination_args_and_path  );

	$obj = holotree_dms_ui()->get_view( $view, $view_args, 'Pods' );
	if ( $return_obj === true ) {

		return $obj;

	}
	else {
		$template_file = trailingslashit( HT_DMS_VIEW_DIR ).'partials/';

		$template_file .= pods_v( 'view', $pagination_args_and_path );

		$obj->find( array( 'page' => $args[ 'page' ], 'limit' => $args[ 'limit' ] ) );


		if ( $obj->total() > 1 ) {
			$out = '';
			if ( file_exists( $template_file ) ) {
				$template_file = file_get_contents( $template_file );
			}
			else {
				return;
			}
			
			while ( $obj->fetch() ) {
				$obj->id = $obj->id();

				$out .= holotree_dms_ui()->view_loaders()->template( $template_file, $obj );

			}

			if ( ! empty ( $out ) ) {
				$out .= holotree_dms_ui()->build_elements()->ajax_pagination_buttons( $obj, $view, $args[ 'page' ] );
				$out = apply_filters( 'ht_dms_paginated_views_template_output', $out, $view );

				return $out;

			}


		}



	}

}

/**
 * Outputs an empty container to load paginated views into.
 *
 * @param string     $view
 * @param  array    $args
 * @param string 	$content
 *
 * @return string
 *
 * @since 0.0.2
 */
function ht_dms_paginated_view_container( $view, $args, $content = '' ) {

	$attrs = array(
		'view' => $view,
		'page' => $args[ 'page' ],
		'limit' => $args[ 'limit' ],

	);

	if ( $view === 'users_notifications' ) {
		$attrs[ 'unViewedOnly' ] = 1;
	}

	$attributes = '';
	foreach( $attrs as $attr => $value  ) {
		$attributes .= $attr.'="'.$value.'"';
	}

	$spinner = ht_dms_spinner();
	$out = sprintf( '<div id="%1s" %2s>%3s</div>', $view, $attributes, $content );
	$out .= sprintf( '<div id="%1s-spinner">%2s</div>', $view, $spinner );


	return $out;

}

/**
 * Returns an array of the paginated views, foreach an array of arguments for calling their view method and the view partial file name.
 *
 * @param null $args
 *
 * @return mixed|void
 *
 * @since 0.0.1
 */
function ht_dms_paginated_views( $args = null ) {
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
			'args' => array( null, get_current_user_id(), null, $args[ 'limit'], 'Pods', $args[ 'page'] ),
			'view' => 'group_preview.php',
		),
		'public_groups' => array(
			'args' => array( null, $args[ 'oID' ], $args[ 'limit' ], 'Pods', $args[ 'page'] ),
			'view' => 'group_preview.php',
		),
		'users_organizations' => array(
			'args' => array( null, $args[ 'oID' ], $args[ 'limit' ], 'Pods', $args[ 'page'] ),
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

	return apply_filters( 'ht_dms_paginated_views', $paginated_views );

}

/**
 * Default arguments to use when loading the paginated views
 *
 * @param null|array $args Optional. Additional arguments to add.
 *
 * @return array
 *
 * @since 0.0.3
 */
function ht_dms_default_paginated_view_arguments( $args = null ) {
	$paginated_view_args = array(
		'uID' => get_current_user_id(),
		'limit' => 5,
		'page' => 1,
	);

	if ( is_array( $args ) ) {
		$paginated_view_args = array_merge( $paginated_view_args, $args );
	}

	return $paginated_view_args;


}



