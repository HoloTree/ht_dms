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

	function __construct() {
		if ( has_filter( 'app_starter_content_part_view') ) {
			add_filter( 'app_starter_content_part_view', array ( $this->view_loaders(), 'view_loader' ) );
		}
		else {
			add_filter( 'the_content', array ( $this->view_loaders(), 'generic_view_loader' ) );
			add_filter( 'template_include', array ( $this->view_loaders(), 'task_view' ) );
		}
		if ( ! is_user_logged_in() ) {
			add_filter( 'template_include', array ( $this->login(), 'force_login' ) );
		}

		//don't show titles!
		if ( !is_admin() ) {
			add_filter( 'the_title', '__return_false' );
		}
	}

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

	function models() {
		$this->file( 'models', 'build' );

		return \ht_dms\ui\build\models::init();

	}

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

	private function file( $file, $dir  ) {
		require_once( trailingslashit( HT_DMS_UI_DIR ) ). trailingslashit( $dir ). $file .'.php';
	}



}
