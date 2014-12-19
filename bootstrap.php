<?php
/**
 * Loading actions necessary to bootstap HT DMS
 *
 * This is intentionally outside of the ht_dms namespace. It is called
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */


function holotree_dms_boot() {
	/**
	 * Action that runs right before Holotree DMS system is initialized.
	 *
	 * @since 0.0.1
	 */
	do_action( 'holotree_before_DMS_activation' );

	/**
	 * Setup Auto Loader
	 */
	include( trailingslashit( HT_DMS_ROOT_DIR ) . 'ClassLoader.php' );
	$classLoader = new HT_DMS_ClassLoader();
	$classLoader->addNamespace( 'ht_dms', untrailingslashit( HT_DMS_DIR ) );

	$classLoader->register();

	/**
	 * Include vendor dir and composer autoloader
	 */
	$autoloader = dirname( __FILE__ ) .'/vendor/autoload.php';
	if ( file_exists( $autoloader ) ) {
		include ( $autoloader );
	}else {
		ht_dms_error();
	}

	/**
	 * Load the function files
	 */
	include( trailingslashit( HT_DMS_ROOT_DIR ) . 'inc/dms.php' );
	include( trailingslashit( HT_DMS_ROOT_DIR ) . 'inc/helper.php' );
	include( trailingslashit( HT_DMS_DIR ) ) . 'helper/paginated_views.php';

	/**
	 * Plugins API Manager
	 */
	include( trailingslashit( HT_DMS_ROOT_DIR ) . 'wp-plugin-api-manager/interface.php' );
	include( trailingslashit( HT_DMS_ROOT_DIR ) . 'wp-plugin-api-manager/manager.php' );
	include( trailingslashit( HT_DMS_ROOT_DIR ) . 'wp-plugin-api-manager/registration.php' );
	$api_registration = new \HT_DMS_WP_API_Registration();
	$api_registration->boot();


	/**
	 * DMS class
	 *
	 * @todo autoload?
	 */
	$GLOBALS[ 'HoloTree_DMS' ] = HoloTree_DMS::init();

	ht_dms_common_class();



	/**
	 * Action that runs right after Holotree DMS system is initialized.
	 *
	 * @since 0.1.0
	 */
	do_action( 'holotree_after_DMS_activation' );


}


/**
 * Lockout admin to non admins
 *
 * @since 0.1.0
 */
add_action( 'admin_init', function( ) {
	if ( 'ht_dms_validate_invite_code' !== pods_v_sanitized( 'action', 'post' )  ){
		global $current_user;
		if ( ! HT_DEV_MODE || ! isset( $current_user->caps ) ) {
			$caps = $current_user->caps;
			if ( is_null( pods_v( 'administrator', $caps ) ) ) {
				pods_redirect( ht_dms_home() );
			}
		}
	}
}, 1 );

/**
 * Default initialization for the plugin:
 * - Registers the default textdomain.
 *
 * @since 0.0.3
 */
add_action( 'init', 'ht_dms_init_translation' );
function ht_dms_init_translation() {
	$locale = apply_filters( 'plugin_locale', get_locale(), 'ht_dms' );
	load_textdomain( 'ht_dms', WP_LANG_DIR . '/ht_dms/ht_dms-' . $locale . '.mo' );
	load_plugin_textdomain( 'ht_dms', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

/**
 * Check and correct Permalinks
 */
add_action( 'after_theme_setup', 'holotree_dms_permalinks' );
function holotree_dms_permalinks() {
	global $wp_rewrite;

	if ( ! is_object( $wp_rewrite ) ) {
		return;
	}

	if ( $wp_rewrite->permalink_structure !== '/%postname%/') {
		$wp_rewrite->set_permalink_structure('/%postname%/');
		$wp_rewrite->flush_rules();
	}

}

/**
 * Put current user ID in a global.
 *
 * @since 0.1.0
 */
add_action( 'init', function() {
	global $current_user;
	global $cuID;
	$cuID = pods_v( 'ID', $current_user );

} );


