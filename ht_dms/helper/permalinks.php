<?php
/**
 * @TODO What this does.
 *
 * @package   @TODO
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2015 Josh Pollock
 */

namespace ht_dms\helper;


class permalinks implements \Action_Hook_SubscriberInterface {

	protected static $cache_mode = 'transient';

	/**
	 * Set actions
	 *
	 * @since  0.3.0
	 *
	 * @return array
	 */
	static public function get_actions() {
		return array(
			'post_type_link' => array( 'set', 15, 2 )
		);

	}

	/**
	 * Set the permalinks
	 *
	 * @since  0.3.0
	 *
	 * @param $url
	 * @param $post
	 *
	 * @return string
	 */
	public static function set( $url, $post) {
		if ( ht_dms_is_dms_type( $post->post_type )  ) {
			if ( false == ( $url = self::try_cache( $url ) ) ) {
				$fail       = false;
				$parsed_url = parse_url( $url );
				$path       = pods_v( 'path', $parsed_url );
				$path       = explode( '/', $path );
				$new_path   = array( 'ht-dms' );
				if ( is_array( $path ) & ! empty( $path ) ) {
					if ( ht_dms_is_organization( $post->ID ) ) {
						$new_path[] = $post->post_title;

					} elseif ( ht_dms_is_group( $post->ID ) ) {
						$new_path = self::group( $post, $new_path );

					} elseif ( ht_dms_is_decision( $post->ID ) ) {
						$new_path = self::decision( $post, $new_path );

					} else {
						$fail = true;
					}

				}

				if ( ! $fail ) {
					foreach ( $new_path as $i => $path ) {
						$new_path[ $i ] = sanitize_title_with_dashes( $path );
						if ( ! $new_path[ $i ] ) {
							$fail = true;
							break;
						}

					}
				}

				if ( ! $fail ) {
					$parsed_url['path']   = implode( '/', $new_path );
					$parsed_url['scheme'] = $parsed_url['scheme'] . ':/';

					$url = implode( '/', $parsed_url );

					self::cache_set( $url );

				}

			}

		}

		return $url;

	}

	/**
	 * Attempt to return cache value
	 *
	 * @since 0.3.0
	 *
	 * @access protected
	 *
	 * @param string $url
	 *
	 * @return bool|string
	 */
	protected static function try_cache( $url ) {
		$key = self::cache_key( $url );
		return pods_view_get( $key, self::$cache_mode );

	}

	/**
	 * Get cache key
	 *
	 * @since 0.3.0
	 *
	 * @access protected
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	protected  static function cache_key( $url ) {
		return md5( __CLASS__ . $url );

	}

	/**
	 * Set cache
	 *
	 * @since 0.3.0
	 *
	 * @access protected
	 *
	 * @param string $url
	 */
	protected static function cache_set( $url ) {
		$key = self::cache_key( $url );
		pods_view_set( $key, self::$cache_mode );
	}
	
	/**
	 * Set up path array for groups.
	 *
	 * @since 0.3.0
	 *
	 * @access protected
	 *
	 * @param object $post Post object.
	 * @param array $new_path Array for constructing path, as is.
	 *
	 * @return array Array for constructing path.
	 */
	protected static function group( $post, $new_path ) {
		$g          = ht_dms_group_class();
		$org_id     = $g->get_organization( $post->ID );
		$new_path[] = get_the_title( $org_id );
		$new_path[] = $post->post_title;

		return $new_path;

	}

	/**
	 * Setup path for decisions
	 *
	 * @since 0.3.0
	 *
	 * @access protected
	 *
	 * @param object $post Post object.
	 * @param array $new_path Array for constructing path, as is.
	 *
	 * @return array Array for constructing path.
	 */
	protected static function decision( $post, $new_path ) {
		$d          = ht_dms_group_class();
		$org_id     = $d->get_organization( $post->ID );
		$group_id   = $d->get_group( $post->ID );
		$new_path[] = get_the_title( $org_id );
		$new_path[] = get_the_title( $group_id );
		$new_path[] = $post->post_title;

		return $new_path;

	}

	/**
	 * Holds the instance of this class.
	 *
	 * @since  0.3.0
	 *
	 * @access private
	 * @var    object
	 */
	private static $instance;


	/**
	 * Returns the instance.
	 *
	 * @since  0.3.0
	 * @access public
	 * @return permalinks
	 */
	public static function init() {

		if ( !self::$instance )
			self::$instance = new self;

		return self::$instance;

	}

}
