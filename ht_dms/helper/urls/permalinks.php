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

namespace ht_dms\helper\urls;


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
			'post_type_link' => array( 'post_type_link', 15, 2 ),
			'pre_get_posts' => 'set_qvars',
		);

	}

	static public function set_qvars( $query ) {
		global $wp_rewrite;
		if( $query->is_main_query() ){

			if ( empty( $query->query ) || ( isset( $query->query ) && isset( $query->query->name ) && $query->query->name = 'ht-dms-internal-api' ) ) {

				return;
			}

			if ( isset( $query->query ) && 'ht-dms' === $query->query[ 'name'] ) {
				$slug = $query->query[ 'page' ];

				$slug = str_replace( '/', '', $slug );
				$query->set( 'ht_dms_organization_name', $slug );
				$post_type = HT_DMS_ORGANIZATION_POD_NAME;
				$query->set( 'post_type', $post_type );
				$query->set( $post_type, $slug );
				$query->set( 'name', $slug );
				$query->set( 'page', '' );

				return;
			}
			else{
				return;
			}

			$post_type = $query->query['post_type'];
			if ( HT_DMS_DECISION_POD_NAME === $post_type ) {
				$class = ht_dms_decision_class();
				$name = pods_v( 'name', $query->query );
				if ( $name ) {
					$query->set( 'ht_dms_decision_name', $name );
				}
			} elseif( HT_DMS_GROUP_POD_NAME === $post_type ) {
				$class = ht_dms_group_class();
			} elseif( HT_DMS_ORGANIZATION_POD_NAME == $post_type ) {
				$name = pods_v( 'name', $query->query );
				if ( $name ) {
					$query->set( 'ht_dms_organization_name', $name );
				}

				return;

			}
			else{
				return;
			}

			$slug = pods_v( 'name', $query->query );
			if ( $slug ) {
				$matches = $class->find_parents_by_slug( $slug, false );
				if ( is_array( $matches ) && ! empty( $matches ) ) {
					$parents = current( $matches );
					if( ! is_null( $parents )  ) {
						$query->set( 'ht_dms_group_name', pods_v( 'group', $parents ) );
						$query->set( 'ht_dms_organization_name', pods_v( 'organization', $parents ) );
					}

				}

			}

		}

		$x = 1;

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
	public static function post_type_link( $url, $post) {
		if ( $url && ht_dms_is_dms_type( $post->post_type )  ) {
			if ( false == ( $out_url = self::try_cache( $url ) ) ) {

				$fail       = false;
				$parsed_url = parse_url( $url );
				$path       = pods_v( 'path', $parsed_url );
				$path       = explode( '/', $path );
				$new_path   = array( 'ht-dms' );
				if ( is_array( $path ) & ! empty( $path ) ) {
					if ( ht_dms_is_organization( $post->ID ) ) {
						$new_path[] = $post->post_name;

					} elseif ( ht_dms_is_group( $post->ID ) ) {
						$new_path[] = self::group( $post, $new_path );
						$new_path[] = $post->post_name;

					} elseif ( ht_dms_is_decision( $post->ID ) ) {
						$new_path[] = self::decision( $post, $new_path );
						$new_path[] = $post->post_name;

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
					if ( ! isset( $parsed_url[ 'scheme' ] ) ) {
						if ( is_ssl() ) {
							$parsed_url[ 'scheme' ] = 'https';
						}else{
							$parsed_url[ 'scheme' ] = 'http';
						}

					}

					if ( isset( $parsed_url[ 'scheme' ] ) ) {
						$parsed_url[ 'scheme' ] = $parsed_url['scheme'] . ':/';
					}

					if ( ! isset( $parsed_url[ 'host' ] ) ) {
						$parsed_url[ 'host' ] = trailingslashit( home_url() );
					}

					$parsed_url[ 'path' ]   = implode( '/', $new_path );

					$out_url = implode( '/', $parsed_url );



				}

			}

			if ( $out_url ) {
				self::cache_set( $out_url );
				$url = $out_url;
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
		return false;
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
		$g = ht_dms_group_class();
		$org_id = $g->get_organization( $post->ID );
		$new_path[] = get_the_title( $org_id );

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
