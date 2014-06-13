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

namespace ht_dms\ui\build;


class tags {
	function create_tags() {
		foreach( $this->create_tag() as $tag ) {
			extract( $tag );
			$this->create_tag( $name, $callback, $args );
		}

	}

	function create_tag( $name, $callback, $args = null ) {

	}

	private function parse( $tag ) {
		$tag = strtr( $tag, array('[' => '', ']' => ''));
		$args = shortcode_parse_atts( $tag );
		$func = $args[0];
		array_shift( $args );
		return call_user_func_array( $func, $args );

	}

	function tags() {
		$tags = array(
			'name' 		=> 'is_pending',
			'callback' => array( holotree_group(), 'is_pending',
			'args'		 =>
		);

		$tags = apply_filters( 'ht_dms_tags', $tags );

		return $tags;
	}


} 
