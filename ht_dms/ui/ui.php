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

namespace ht_dms\ui;


class ui {

	/**
	 * Initializes the UI() class
	 *
	 * Checks for an existing UI() instance
	 * and if it doesn't find one, creates it.
	 *
	 * @since 0.0.1
	 */
	public static function init() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new \ht_dms\ui\ui();
		}

		return $instance;

	}

	function group_widget() {
		$this->file( 'group_widget', 'build' );

		return new \ht_dms\ui\build\group_widget();
	}

	function my_stuff() {
		$this->file( 'my_stuff', 'build' );

		return new \ht_dms\ui\build\my_stuff();
	}

	function views() {
		$this->file( 'views', 'build' );

		return new \ht_dms\ui\build\views();
	}

	/**
	 * Ajax callbacks class
	 *
	 * @return output\ajax_callbacks
	 *
	 * @since 0.0.3
	 */
	function ajax_callbacks() {

		$this->file( 'ajax_callbacks', 'output' );

		return new \ht_dms\ui\output\ajax_callbacks();

	}

	/**
	 * Get any view defined in the ht_dms\ui\build\views class.
	 *
	 * Exists to power ht_dms_ui_ajax_view(), but can be used independently.
	 *
	 * @param string $view The name of any method in the class.
	 * @param array $args An array of arguments in order for the chosen method.
	 * @param null|string Optional. What to return. If used overrides, $args[ 'return'] Options: template|Pods|JSON|urlstring
	 *
	 * @return null|string|obj|Pods|JSON
	 *
	 * @since 0.0.1
	 */
	function get_view( $view, $args, $return = null ) {
		$views = $this->views();
		if ( ! is_null( $return ) ) {
			end( $args );
			$last_id = key( $args );
			$args[ $last_id ] = $return;
		}

		return call_user_func_array( array( $views, $view ), $args );

	}

	function models() {
		$this->file( 'models', 'build' );

		return \ht_dms\ui\build\models::init();

	}

	/**
	 * @return \ht_dms\ui\build\elements
	 */
	function build_elements() {
		$this->file( 'elements', 'build' );

		return \ht_dms\ui\build\elements::init();

	}

	function login() {
		$this->file( 'login', 'build' );

		return new \ht_dms\ui\build\login();
	}

	function tags() {
		$this->file( 'views', 'build' );

		return new \ht_dms\ui\build\tags();

	}

	function add_modify() {
		$this->file( 'add_modify', 'output' );

		return new \ht_dms\ui\output\add_modify();
	}

	function output_elements() {
		$this->file( 'elements', 'output' );
		
		return new \ht_dms\ui\output\elements;

	}

	function elements( $output = true ) {
		if ( $output ) {
			return $this->output_elements();
		}

		return $this->build_elements();

	}

	function view_loaders() {
		$this->file( 'view_loaders', 'output' );

		return new \ht_dms\ui\output\view_loaders();

	}

	/**
	 * Returns activity stream
	 *
	 * @param string $type Type of stream network|user|organization|group
	 *
	 * @return build\activity_stream
	 *
	 * @since 0.0.3
	 */
	function activity_stream( $type, $id ) {
		$this->file( __FUNCTION__, 'build' );

		return new \ht_dms\ui\build\activity_stream( $type, $id );

	}

	/**
	 * Group & Organization Membership Elements
	 *
	 * @return build\membership
	 *
	 * @since 0.0.3
	 */
	function membership() {
		$this->file( 'membership', 'build' );

		return new \ht_dms\ui\build\membership();

	}

	private function file( $file, $dir  ) {
		require_once( trailingslashit( HT_DMS_UI_DIR ) ). trailingslashit( $dir ). $file .'.php';
	}



}
