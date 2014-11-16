<?php
/*
Plugin Name: HoloTree Decision Making System
* Description: Consensus based decision making system.
* Author: Josh Pollock
* Author URI: http://joshpress.net
* Plugin URI: https://holotree.com
Version: 0.1.0-dev
License: GPL v2 or Later
Text Domain: ht_dms
Domain Path: /languages/
*/

/**
 * Copyright (c) 2014 Josh Pollock (Josh@JoshPress.net). All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */

// don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;


/**
 * Define constants
 * 
 * @since 0.0.1
 */
define( 'HT_DMS_VERSION', '0.1.0' );
define( 'HT_DMS_DB_VERSION', '2' );
define( 'HT_DMS_SLUG', plugin_basename( __FILE__ ) );
define( 'HT_DMS_ROOT_URL', plugin_dir_url( __FILE__ ) );
define( 'HT_DMS_ROOT_DIR', plugin_dir_path( __FILE__ ) );

require_once( trailingslashit( HT_DMS_ROOT_DIR ) . 'inc/constants.php' );


/**
 * HoloTree_DMS class
 *
 * @class HoloTree_DMS The class that holds the entire Holo_Tree plugin
 *
 * @since 0.0.1
 */

class HoloTree_DMS {

	/**
	 * Constructor for the HoloTree_DMS class
	 *
	 * Sets up all the appropriate hooks and actions
	 * within our plugin.
	 *
	 * @uses register_activation_hook()
	 * @uses register_deactivation_hook()
	 * @uses is_admin()
	 * @uses add_action()
	 *
	 * @since 0.0.1
	 */
	public function __construct() {
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		// Localize our plugin
		add_action( 'init', array( $this, 'localization_setup' ) );


		// Loads frontend scripts and styles
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

	}


	/**
	 * Runs on Activation
	 *
	 * @since 0.0.1
	 */
	public function activate() {

	}

	/**
	 * Runs on deactivation.
	 *
	 * @since 0.0.1
	 */
	public function deactivate() {

	}

	/**
	 * Initialize plugin for localization
	 *
	 * @uses load_plugin_textdomain()
	 *
	 * @since 0.0.1
	 */
	public function localization_setup() {
		load_plugin_textdomain( 'baseplugin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Enqueue admin scripts
	 *
	 * Allows plugin assets to be loaded.
	 *
	 * @uses wp_enqueue_script()
	 * @uses wp_localize_script()
	 * @uses wp_enqueue_style
	 *
	 * @since 0.0.1
	 */
	public function enqueue_scripts() {
		if ( ! is_admin() ) {
			$version = HT_DMS_VERSION;
			if ( HT_DEV_MODE ) {
				$version = rand();
			}

			wp_enqueue_style( 'pods-form' );
			wp_enqueue_script( 'pods' );
			wp_enqueue_script( 'pods-attach' );

			wp_enqueue_script( 'ht-dms-ui', plugins_url( 'js/ht-dms-ui.js', __FILE__ ), array ( 'jquery' ), $version, true );

			//wp_enqueue_script( 'modernizer-cache', HT_DMS_ROOT_URL . 'js/modernizer.cache.min.js', array(), $version, false );
			//wp_enqueue_script( 'ajax-cache', HT_DMS_ROOT_URL . 'js/jquery-ajax-localstorage-cache.min.js', array( 'modernizer-cache' ), $version, true );

			if ( is_array( $this->htDMS_js_var() ) ) {
				wp_localize_script( 'ht-dms-ui', 'htDMS', $this->htDMS_js_var() );
			}

			$consensus_possibilities = false;

			global $post;
			if ( is_object( $post ) && $post->post_type === HT_DMS_DECISION_POD_NAME ) {

				$consensus_possibilities = ht_dms_consensus_class()->possible_changes( $post->ID, get_current_user_id() );

			}

			wp_localize_script( 'ht-dms-ui', 'consensusPossibilities', $consensus_possibilities );

			if ( ! function_exists( 'json_get_url_prefix' ) ) {
				return;
			}

			wp_enqueue_script( 'wp-api-js', plugins_url( '/js/wp-api.min.js', __FILE__ ), array( 'jquery', 'underscore', 'backbone' ), '1.0', true );

			$settings = array( 'root' => home_url( json_get_url_prefix() ), 'nonce' => wp_create_nonce( 'wp_json' ) );

			wp_localize_script( 'wp-api-js', 'WP_API_Settings', $settings );

		}

	}


	/**
	 * Variables to pass into htDMS JavaScript object, via ht-dms.js
	 *
	 * @return array|mixed|void
	 *
	 * @since 0.0.2
	 */
	function htDMS_js_var() {
		$htDMS = array(
			'ajaxURL' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'ht-dms' ),
			'id' => get_queried_object_id(),
			'consensusMembers' => 0,
			'consensusHeaders' => 0,
			'consensusMemberDetails' => 0,
			'homeURL' => untrailingslashit( ht_dms_home() ),
			'proposeModifyURL' => 0
		);

		if ( ht_dms_integer( get_queried_object_id() ) && ht_dms_is_decision( get_queried_object_id()  ) ) {
			$consensus = ht_dms_consensus_class()->sort_consensus( get_queried_object_id(), true );
			if ( $consensus ) {
				$consensusMembers = $consensus;
				unset( $consensusMembers['headers'] );
				unset( $consensusMembers['details'] );
				$htDMS['consensusHeaders'] = pods_v( 'headers', $consensus, array() );


				if ( $consensusMembers ) {
					$htDMS['consensusMembers'] = json_encode( $consensusMembers );
				}

				$htDMS['consensusMemberDetails'] = ht_dms_sorted_consensus_details( $consensus );
			}

			$htDMS[ 'proposeModifyURL' ] = ht_dms_action_append( ht_dms_home(), 'propose-change', get_queried_object_id() );

		}

		/**
		 * Override or add to variables passed into htDMS JavaScript object.
		 *
		 * Set to null to prevent the object from being created.
		 *
		 * @param 	array $htDMS An array of items to pass into the object.
		 *
		 * @return 				 The array
		 *
		 * @since 	0.0.2
		 */
		$htDMS = apply_filters( 'ht_dms_htDMS_js_var', $htDMS );

		return $htDMS;
	}

	/**
	 * Holds the instance of this class.
	 *
	 * @access private
	 * @var    object
	 *
	 * @since 0.0.1
	 */
	private static $instance;


	/**
	 * Returns the instance.
	 *
	 * @access public
	 * @return object
	 *
	 * @since 0.0.1
	 */
	public static function init() {

		if ( !self::$instance )
			self::$instance = new self;

		return self::$instance;

	}

	function theme() {

		return new ht_dms\helper\theme();

	}





	/**
	 * On load make sure we have the right DB version. If not run the Pods setup.
	 *
	 * @since 0.0.2
	 */
	function setup_check() {

		if ( defined( 'PODS_VERSION' ) && function_exists( 'pods_api' ) ) {
			if ( version_compare( HT_DMS_DB_VERSION, get_option( 'ht_dms_db_version', 0 ) ) > 0 ) {
				require_once( trailingslashit( HT_DMS_ROOT_DIR ) . 'inc/helper.php' );
				ht_dms_setup_pods( false, ht_dms_pods_exist() );
				update_option( 'ht_dms_db_version', HT_DMS_DB_VERSION );

			}
		}
	}

}

/**
 * Activate
 */

$GLOBALS[ 'HoloTree_DMS' ] = HoloTree_DMS::init();

/**
 * Action that runs right before Holotree DMS system is initialized.
 *
 * @since 0.0.1
 */
do_action( 'holotree_before_DMS_activation' );

/**
 * Setup Auto Loader
 */
require_once( trailingslashit( HT_DMS_ROOT_DIR ) . 'ClassLoader.php' );
$classLoader = new HT_DMS_ClassLoader();
$classLoader->addNamespace( 'ht_dms', untrailingslashit( HT_DMS_DIR ) );

$classLoader->register();

require_once( trailingslashit( HT_DMS_ROOT_DIR ) . 'inc/dms.php' );
require_once( trailingslashit( HT_DMS_ROOT_DIR ) . 'inc/helper.php' );
require_once( trailingslashit( HT_DMS_UI_DIR ). 'ui.php' );


require_once( trailingslashit( HT_DMS_DIR ) ) . 'helper/paginated_views.php';
require_once( trailingslashit( HT_DMS_ROOT_DIR ) . 'wp-plugin-api-manager/interface.php' );
require_once( trailingslashit( HT_DMS_ROOT_DIR ) . 'wp-plugin-api-manager/manager.php' );
require_once( trailingslashit( HT_DMS_ROOT_DIR ) . 'wp-plugin-api-manager/registration.php' );

$api_registration = new \HT_DMS_WP_API_Registration();
$api_registration->boot();

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

ht_dms_common_class();
/**
 * Action that runs right after Holotree DMS system is initialized.
 *
 * @since 0.1.0
 */
do_action( 'holotree_after_DMS_activation' );

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
 * Redirect to home when accessing post type archives
 *
 * @since 0.0.3
 */
add_action('template_redirect', 'ht_dms_archive_redirect');
function ht_dms_archive_redirect() {

	if ( is_post_type_archive( ht_dms_content_types() ) ) {

		pods_redirect( ht_dms_home() );
	}
}

/**
 * Redirect to home on 404
 *
 * @since 0.0.3
 */
add_filter( '404_template', 'ht_dms_404_redirect' );
function ht_dms_404_redirect( $template ) {

	pods_redirect( ht_dms_home() );
	
}

/**
 * Shorten nonce lifespan
 *
 * @since 0.1.0
 */
add_filter( 'nonce_life', function () {
	return HOUR_IN_SECONDS;
} );

/**
 * Hack to change the decision_type in propose modify form.
 *
 * @since 0.1.0
 *
 * @see https://github.com/HoloTree/ht_dms/issues/86
 */
add_filter( 'pods_form_ui_field_hidden', function( $output, $name ) {
		if ( $name === 'pods_field_decision_type' ) {
			$output = str_replace( 'original', 'change', $output );
		}

	return $output;
	}, 10, 2 );

add_filter( 'pods_deploy_deploy_in_one_package', '__return_true' );

/**
 * Redirect to preferences after registration
 *
 * @since 0.1.0
 */
add_filter( 'registration_redirect', function( $url ) {
	return ht_dms_home() . '?dms_action=preferences';
}, 99);


/**
 * Lockout admin to non admins
 *
 * @since 0.1.0
 */
add_action( 'admin_init', function( ) {
	global $current_user;
	if ( ! HT_DEV_MODE || ! isset( $current_user->caps ) ) {
		$caps =  $current_user->caps;
		if ( is_null( pods_v( 'administrator', $caps )) ) {
			pods_redirect( ht_dms_home() );
		}
	}
}, 1 );


add_action( 'login_head',
	function() {
		echo '<div id="extra-login">';
		echo '<h1 id="login-extra-title">'. __( 'HoloTree', 'ht_dms' ) . '</h1>';
		echo '<h3 id="login-tagline">'. __( 'Team Decision Making', 'ht_dms' ) . '</h3>';
		echo '</div>';
	}
);
