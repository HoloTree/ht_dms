<?php
/**
 * Helper Functions
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

/**
 * Removes the prefix.
 *
 * Useful for turning content type names into short names. Prefix is defined in HT_DMS_PREFIX
 *
 * @param prefixed string  $string
 * @param bool $remove_underscore Optional. Whether to remove the trailing underscore or not. Default is true.
 *
 * @return string
 *
 * @since 0.0.2
 */
function ht_dms_prefix_remover( $string, $remove_underscore = true ) {
	$prefix = HT_DMS_PREFIX;
	if ( $remove_underscore ) {
		$prefix = $prefix.'_';
	}

	return str_replace( $prefix, '', $string );
}
