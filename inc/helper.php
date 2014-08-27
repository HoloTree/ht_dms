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


/**
 * Setups the Pods for HoloTree DMS
 *
 *  @since 0.0.2
 *
 * @param bool $package_only	Optional. Whether to just import from import package and skip relationship field updates. Default is false.
 * @param bool $skip_package	Optional. Whether to skip importing the package, and advanced directly to updating relationship fields, not pass go and not collect $200 dollars. Default is false.
 * @param bool $delete_existing	Optional. Whether to delete existing Pods (for DMS including user) before importing. Default is true.
 */
function ht_dms_setup_pods( $package_only = false, $skip_package = true, $delete_existing = false) {

	include( trailingslashit( HT_DMS_DIR ).'setup.php' );

	new ht_dms\setup( $package_only, $skip_package, $delete_existing );

}

function ht_dms_pods_exist() {
	$key = 'ht_dms_pods_exists';
	if ( false == pods_transient_get( $key )  ) {
		$pods = array( HT_DMS_ORGANIZATION_NAME, HT_DMS_GROUP_CPT_NAME, HT_DMS_DECISION_CPT_NAME, HT_DMS_TASK_CT_NAME );
		$api = pods_api();
		foreach( $pods as $pod ) {
			$params = array( 'name' => $pod );
			if ( ! $api->pod_exists( $params ) ) {
				return false;
			}
		}

		pods_transient_set( $key, true );
	}
	else {
		return pods_transient_get( $key );
	}

}

/**
 * Returns the main HT DMS Page url.
 *
 * By default returns home_url(), can be modified with the 'ht_dms_home_url' filter
 *
 * @since 0.0.2
 *
 * @return mixed|void
 */
function ht_dms_home() {
	$home = home_url();

	/**
	 * Customize the main HT DMS page
	 *
	 * @param string $home. URL
	 *
	 * @since 0.0.2
	 */
	return apply_filters( 'ht_dms_home_url', $home );

}

/**
 * Set mini-menu items outputted
 *
 * @since 0.0.2
 *
 * @return array
 */
function ht_dms_mini_menu_items() {
	$items = array(
		ht_dms_home() => __( 'Home', 'holotree' ),
		wp_logout_url() => __( 'Logout', 'holotree' ),
	);

	/**
	 * Override mini-menu items
	 *
	 * @param array $items Must be in form of  link => link text
	 *
	 * @since 0.0.2
	 */
	$items = apply_filters( 'ht_dms_mini_menu_items', $items );

	return $items;

}
