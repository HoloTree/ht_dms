<?php
/*
Plugin Name: HoloTree Decision Making System
License: GPL v2 or Later
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
define( 'HT_DMS_VERSION', '0.0.1' );
define( 'HT_DMS_DB_VERSION', '1' );
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

		add_action( 'init', array( $this, 'theme') );
		add_action( 'init', array( $this, 'helper' ) );

		// Loads frontend scripts and styles
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_action( 'init', array( $this, 'setup_check' ), 25 );

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
		wp_enqueue_style( 'pods-select2' );
		wp_enqueue_script( 'pods-select2' );
		wp_enqueue_style( 'pods-form' );
		wp_enqueue_script( 'ht-dms', plugins_url( 'js/ht-dms.js', __FILE__ ), array( 'jquery' ), HT_DMS_VERSION, true );
		wp_enqueue_script( 'ht-dms-ui', plugins_url( 'js/ht-dms-ui.js', __FILE__ ), array( 'jquery', 'ht-dms' ), HT_DMS_VERSION, true );

		if ( is_array( $this->htDMS_js_var() ) ) {
			wp_localize_script( 'ht-dms', 'htDMS', $this->htDMS_js_var() );
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
		);

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
		if ( defined( 'HT_DMS_THEME' ) ) {
			if ( HT_DMS_THEME ) {

				return new ht_dms\helper\theme_setup();

			}
			else {
				holotree_error( _('Your theme is incompatible with The HoloTree Decision Making System. Theme must set HT_DMS_THEME true.', 'holotree' ) );
			}
		}
	}

	function helper() {
		$helpers = array(
			'common',
			'consensus',
			'membership',
		);
		foreach ( $helpers as $helper ) {
			include_once( trailingslashit( HT_DMS_DIR ).'helper/'.$helper.'.php' );
		}

		new ht_dms\helper\common();


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
 * Activate if core plugin and Pods is active.
 *
 * @since 0.0.1
 */
add_action( 'plugins_loaded', 'holotree_dms', 30 );
function holotree_dms() {
	if ( defined( 'HT_VERSION' ) && defined( 'PODS_VERSION' ) ) {
		$GLOBALS[ 'HoloTree_DMS' ] = HoloTree_DMS::init();

		/**
		 * Action that runs right after main Holotree DMS class is initialized.
		 *
		 * @since 0.0.1
		 */
		do_action( 'holotree_DMS' );

		require_once( trailingslashit( HT_DMS_ROOT_DIR ) . 'inc/dms.php' );
		require_once( trailingslashit( HT_DMS_ROOT_DIR ) . 'inc/helper.php' );
		require_once( trailingslashit( HT_DMS_UI_DIR ). 'ui.php' );
		require_once( trailingslashit( HT_DMS_DIR ) . 'helper/theme_setup.php' );
		new ht_dms\helper\Theme_Setup();

		/**
		 * Setup Auto Loader
		 *
		 * @TODO MAKE THIS WORK RIGHT
		 */
		require_once( trailingslashit( HT_DMS_ROOT_DIR ) . 'ClassLoader.php' );
		$classLoader = new HT_DMS_ClassLoader();
		$classLoader->addDirectory( trailingslashit( HT_DMS_ROOT_DIR ) . 'ht_dms' );
		$classLoader->addDirectory( HT_DMS_UI_DIR );
		$classLoader->addDirectory( trailingslashit( HT_DMS_DIR ) . 'helper' );
		$classLoader->register();

		holotree_dms_ui();


		/**
		 * Make REST API not require auth
		 *
		 * THIS MUST GET REPLACED WITH PROPER AUTH!
		 */

		$filters = array( 'pods_json_api_access_pods', 'pods_json_api_access_api' );
		foreach ( $filters as $filter ) {
			add_filter( $filter, '__return_true' );
		}

		/**
		 * Include class/ item functions
		 */
		require_once( trailingslashit( HT_DMS_ROOT_DIR ) . 'inc/dms.php' );

		return ht_dms\ui\ui::init();

	}


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

