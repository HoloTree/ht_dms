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

		// Loads frontend scripts and styles
		//add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
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

		/**
		 * All styles goes here
		 */
		wp_enqueue_style( 'baseplugin-styles', plugins_url( 'css/style.css', __FILE__ ), false, date( 'Ymd' ) );

		/**
		 * All scripts goes here
		 */
		wp_enqueue_script( 'baseplugin-scripts', plugins_url( 'js/script.js', __FILE__ ), array( 'jquery' ), false, true );

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

}

/**
 * Activate if core plugin and Pods is active.
 *
 * @since 0.0.1
 */
//add_action( 'plugins_loaded', 'holotree_dms', 30 );
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
		require_once(  trailingslashit( HT_DMS_UI_DIR ). 'ui.php' );
		/**
		 * Setup Auto Loader
		 */
		require_once( trailingslashit( HT_DMS_ROOT_DIR ) . 'ClassLoader.php' );
		$classLoader = new HT_DMS_ClassLoader();
		$classLoader->addDirectory( trailingslashit( HT_DMS_ROOT_DIR ) . 'ht_dms' );
		$classLoader->addDirectory( HT_DMS_UI_DIR );
		$classLoader->register();

		holotree_dms_ui();

		/**
		 * Include class/ item functions
		 */
		require_once( trailingslashit( HT_DMS_ROOT_DIR ) . 'inc/dms.php' );

	}

}
