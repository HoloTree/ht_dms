<?php
/**
 * Helper functions for paginated views loaded view ajax
 *
 * @package   ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */



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

	if ( isset( $args[ 'oID' ] ) ) {
		$attrs[ 'oID' ] = $args[ 'oID' ];
	}

	$attributes = '';
	foreach( $attrs as $attr => $value  ) {
		$attributes .= $attr.'="'.esc_attr( $value ) .'" ';
	}

	$spinner = ht_dms_spinner();
	$out = '<div id="' .esc_attr( $view ) . '" '.$attributes . ' >' . esc_html( $content ) . '</div>';
	if ( $view == 'users_notifications' ) {
		$out = ht_dms\ui\build\notification\elements::notification_view_header() . $out;
	}
	
	$out .= sprintf( '<div id="%1s-spinner" class="pagination-spinner spinner">%2s</div>', $view, $spinner );


	return $out;

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



