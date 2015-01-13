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
	$handle  = false;
	$attrs = array(
		'view' => $view,
		'page' => pods_v( 'page', $args, 1 ),
		'limit' => pods_v( 'limit', $args, 5 ),

	);

	if ( $view == 'decision' ) {
		$attrs[ 'status' ] = $args[ 'status' ];
		$view = 'decision-'.strtolower( $args[ 'status' ] );
		$attrs[ 'gid' ] = $args[ 'gID' ];
		$handle = 'decision-preview';

	}

	if ( strpos($view, 'group' ) > 0 ) {
		$handle = 'group-preview';
	}

	if ( strpos( $view, 'organization') ) {
		$handle = 'organization-preview';
	}

	if ( $view === 'users_notifications' ) {
		$attrs[ 'unViewedOnly' ] = 1;

	}

	if ( isset( $args[ 'oID' ] ) ) {
		$attrs[ 'oid' ] = $args[ 'oID' ];
	}

	$attributes = '';
	foreach( $attrs as $attr => $value  ) {
		$value = strtolower( $value );
		$attributes .= $attr.'="'.esc_attr( $value ) .'" ';
	}

	$spinner = ht_dms_spinner();
	$out = '<div id="' .esc_attr( $view ) . '" '.$attributes . ' >' . html_entity_decode( $content ) . '</div>';
	if ( $view == 'users_notifications' ) {
		$out = ht_dms\ui\build\notification\elements::notification_view_header() . $out;
		$out .= ht_dms_ui()->view_loaders()->handlebars_template( 'notification' );
	}

	holotree_enqueue_handlebar( $handle, ht_dms_ui()->view_loaders( )->handlebars_template_file_location( $handle ) );

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



