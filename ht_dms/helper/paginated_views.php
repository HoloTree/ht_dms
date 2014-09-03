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

add_action( 'wp_ajax_ht_dms_paginate', 'ht_dms_paginate');
add_action( 'wp_ajax_nopriv_ht_dms_paginate', 'ht_dms_paginate' );
function ht_dms_paginate() {
	if ( isset( $_REQUEST['nonce'] ) ) {
		if ( ! wp_verify_nonce( $_REQUEST[ 'nonce' ], 'ht-dms' ) ) {
			wp_die( __( 'Your attempt to request data via ajax using the function holotree_dms_ui_ajax_view was denied as the nonce did not match.', 'holotree' ) );
		}

		if ( isset( $_REQUEST[ 'view' ] ) && isset( $_REQUEST[ 'limit' ] ) && isset( $_REQUEST[ 'page' ] ) ) {
			$view = $_REQUEST[ 'view' ];
			$limit = $_REQUEST[ 'limit' ];
			$page = $_REQUEST[ 'page' ];

			$args = array(
				'limit' => $limit,
				'page' => $page,
			);

			if ( in_array( $view , array( 'users_groups' )  ) ) {
				wp_die( ht_dms_pagination_views( $view, $args ) );
			}

		}

	}

}

function ht_dms_pagination_views( $view, $args, $return_obj = false ) {
	$args[ 'return' ] = 'Pods';
	$view_args = array( null, get_current_user_id(), null, $args[ 'limit'], 'Pods', $args[ 'page'] );

	$obj = holotree_dms_ui()->get_view( $view, $view_args, 'Pods' );
	if ( $return_obj === true ) {
		return $obj;

	}
	else {

		$obj->find( array( 'page' => $args[ 'page' ], 'limit' => $args[ 'limit' ] ) );

		$template_file = trailingslashit( HT_DMS_VIEW_DIR ).'partials';
		$template_file .= '/group_preview.php';

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
				return $out;
			}


		}



	}

}

function ht_dms_paginated_view_container( $view, $args, $content = '' ) {

	$attrs = array(
		'view' => $view,
		'page' => $args[ 'page' ],
		'limit' => $args[ 'limit' ],

	);

	$attributes = '';
	foreach( $attrs as $attr => $value  ) {
		$attributes .= $attr.'="'.$value.'"';
	}

	$out = sprintf( '<div id="%0s" %1s>%2s</div>', $view, $attributes, $content );

	return $out;

}
