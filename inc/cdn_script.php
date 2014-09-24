<?php
/**
 * Load JS/CSS from a CDN with local fallback.
 *
 * Adapted from: http://wordpress.stackexchange.com/a/147261/25300
 *
 * @package   @holotree
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

class ht_dms_cdn_script {

	private $cdn_url;
	private $path;
	private $handle;
	private $dependencies;
	private $in_footer;
	private $is_css;
	private $cache_key;

	function __construct ( $cdn_url, $path, $handle, $is_css = false, $dependencies = array(), $in_footer = null ) {
		$this->cdn_url = $this->protocol() . $cdn_url;
		$this->handle = $handle;
		$this->dependencies = $dependencies;
		$this->is_css = $is_css;
		$this->path = $path;

		if ( is_null( $in_footer ) ) {
			if ( $is_css ) {
				$this->in_footer = false;
			}
			else{
				$this->in_footer = true;
			}
		}
		else {
			$this->in_footer = $in_footer;
		}

		$class = __CLASS__;
		$this->cache_key = "{$class}_cdn_check_{$handle}";


		add_action( 'init', array( $this, 'check_cdn' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'load' ) );

	}

	private function  protocol() {
		if ( isset( $_SERVER[ 'HTTPS' ] ) ) {
			return 'https:';
		}

		return 'http:';

	}

	private function up() {

		return get_transient( $this->cache_key );

	}

	private function check_cdn() {


		$cdn_response = wp_remote_get( $this->cdn_url );

		if( is_wp_error( $cdn_response ) || wp_remote_retrieve_response_code( $cdn_response ) != 200 ) {
			return false;

		}

		set_transient( $this->cache_key, true, MINUTE_IN_SECONDS * 5 );

		return true;

	}

	function load() {

		if ( $this->up() ) {
			$src = $this->cdn_url;
		}
		else {
			$src = $this->path;
		}

		if ( $this->is_css ) {
			wp_enqueue_style( $this->handle, $src, $this->dependencies, false, $this->in_footer );

		}
		else {
			wp_enqueue_script( $this->handle, $src, $this->dependencies, false, $this->in_footer );
		}

	}


}
