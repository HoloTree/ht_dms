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

namespace ht_dms\ui\build;


class models {

	/**
	 * The length to cache Pods Objects
	 *
	 * Default is 85321 seconds ~ 1 Day
	 *
	 * @var int
	 *
	 * @since 0.0.1
	 */
	static public $cache_length = 85321;

	/**
	 * Cache mode for Pods Objects
	 *
	 * cache|transient|site-transient
	 * @var string
	 *
	 * @since 0.0.1
	 */
	static public $cache_mode = 'cache';

	/**
	 * PARAMS FOR ALL MODELS
	 *
	 * @param $args['obj'] Pods|obj|null|string Pods object. Default is null. If is string, value will be swapped into $args['return'] and $args['obj'] will be set to null. If is null or string, an object will be created.
	 * @param $args['id'] int|null. Set an ID to show single item. Default is false.
	 * @param $args['preview'] bool Default is false
	 * @param $args['in'] Default is null.
	 * @param $args['mine'] int|bool Only for one user if true( current user) or use a user ID. Default is false
	 * @param $args['limit'] int Default is 5.
	 * @param $args['status'] string|null decision/task status. Default is null.
	 * @param $args['return'] string What to return. Either the results as a template, as a Pods object, as JSON object via the REST API, or URL string to pass to the REST API  template|Pods|JSON|urlstring
	 * @param $arg['page'] int|bool Page of results to return or false to not use paging arg.
	 * @param $arg['un_viewed_only'] bool If true, the default, only un viewed notifications are returned. Has no effect on other models.
	 */



	function organization( $args ) {
		$args = $this->args( $args );
		extract( $args );

		$params = null;
		if ( is_int( $id ) || (int) $id > 1 ) {
			$params = $this->id_params( $id );
		}
		else {
			if ( $mine ) {
				$where = 'members.ID = "'.$mine.'"';
			}
			if ( $public ) {
				$public_params = 'd.visibility = "public"';
				if ( isset( $where  ) ) {
					$where = $where. ' AND '.$public_params;
				}
				else {
					$where = $public_params;
				}

			}

			if ( isset( $where ) ) {
				$params[ 'where' ] = $where;
			}

			$params[ 'limit' ] = $limit;
			$params[ 'page' ] = $page;

		}

		$params = $this->cache_args( $params );

		return $this->output( $return, HT_DMS_ORGANIZATION_POD_NAME, $params, $preview, $obj );

	}

	function group( $args ) {

		$g = ht_dms_group_class();

		$args = $this->args( $args );
		extract( $args );

		$params = null;
		if ( is_int( $id ) || (int) $id > 1 ) {
			$params = $this->id_params( $id );
		}
		else {

			$params = null;
			$where = array();

			if ( $mine ) {
				$where[] = 'members.ID = "'.$mine.'"';
			}

			if ( is_array( $in ) || ht_dms_integer( $in ) ) {
				if ( is_array( $in ) ) {
					if ( isset( $in[ 'ID' ] ) ) {
						$in = $in[ 'ID' ];
					}
					else {
						$in = $in[ 0 ];
					}
				}
				$where[]= 'organization.ID = "'.$in.'"';


			}


			if ( $public ) {
				$where[] = 'd.visibility = "public"';
			}

			if ( count( $where ) > 1 ) {
				$params[ 'where' ] = implode( ' AND ', $where );
			}
			else {
				$params[ 'where' ] = $where;
			}

			$params[ 'limit' ] = $limit;
			$params[ 'page' ] = $page;

		}

		$params = $this->cache_args( $params );

		return $this->output( $return, HT_DMS_GROUP_POD_NAME, $params, $preview, $obj );

	}

	function decision( $args ) {
		$args = $this->args( $args );
		extract( $args );

		$params = null;
		if ( is_int( $id ) || (int) $id > 1 ) {
			$params = $this->id_params( $id );
		}
		else {
			if ( !is_null( $status ) ) {
				$where = 'd.decision_status = "' . $status . '" ';

			}

			if ( !is_null( $in ) ) {

				if ( $in[ 'what' ] === 'group' || $in[ 'what' ] === HT_DMS_GROUP_POD_NAME ) {
					$in_where = 'group.ID =';
				}
				elseif ( $in[ 'what' ] === 'organization' || $in[ 'what' ] === HT_DMS_ORGANIZATION_POD_NAME ) {
					$in_where = 'organization.ID =';
				}
				else {

					ht_dms_error();
				}

				$in_where .= ' "'.$in[ 'ID' ].'"';

				if ( isset ( $where ) ) {
					$where = $where . ' AND ' . $in_where;
				}
				else {
					$where = $in_where;
				}
			}

			if ( isset( $where ) ) {
				$params[ 'where' ] = $where;
			}

			$params[ 'limit' ] = $limit;
			$params[ 'page' ] = $page;

		}

		$params = $this->cache_args( $params );

		return $this->output( $return, HT_DMS_DECISION_POD_NAME, $params, $preview, $obj );

	}

	function task( $args ) {
		$args = $this->args( $args );
		extract( $args );

		$params = null;
		if ( is_int( $id ) || (int) $id > 1 ) {
			$params = $this->id_params( $id, true );
		}
		else {
			if ( $mine  ) {
				if ( is_null( $mine ) || !is_int( $mine ) || intval( $mine ) == 0 ) {
					$mine = get_current_user_id();
				}

				$where = 'assigned_user.ID = "'.$mine.'"';
			}
			elseif ( !is_null( $in ) ) {
				if (  $in === 'multi'   ) {
					//@TODO in more than one thing
				}
				else {
					if ( $in[ 'what' ] === 'group' || $in[ 'what' ] === HT_DMS_GROUP_POD_NAME ) {
						$in_where = 'group.ID =';
					}
					elseif ( $in[ 'what' ] === 'organization' || $in[ 'what' ] === HT_DMS_ORGANIZATION_POD_NAME ) {
						$in_where = 'organization.ID =';
					}
					elseif( $in[ 'what' ] === 'decision' || $in[ 'what' ] === HT_DMS_DECISION_POD_NAME ) {
						$in_where = 'decision.ID =';
					}
					else {
						ht_dms_error();
					}

					$in_where = $in_where.' "'.$in[ 'id' ].'"';

					if ( isset ( $where ) ) {
						$where = $where . ' AND ' . $in_where;
					}
					else {
						$where = $in_where;
					}

				}
			}

			if ( !is_null( $status ) ) {
				$status_where = 'd.task_status = "'.$status.'"';
				if ( isset ( $where ) ) {
					$where = $where . ' AND ' . $status_where;
				}
				else {
					$where = $status_where;
				}
			}

			if ( isset( $where ) ) {
				$params[ 'where' ] = $where;
			}

			$params[ 'limit' ] = $limit;
			$params[ 'page' ] = $page;

		}

		$params = $this->cache_args( $params );

		if ( is_null( $obj) || ! is_pod( $obj ) ) {
			$obj = ht_dms_task_class()->object( true, $params );
		}


		$view = $this->path( 'task', $preview );

		return $this->output( $return, HT_DMS_TASK_POD_NAME, $params, $preview, $obj );

	}

	function notification( $args ) {
		$args = $this->args( $args );
		extract( $args );

		if ( ht_dms_integer( $id ) ) {
			$params[ 'where' ] = 't.id = "' . $id . '"';
		}
		else {
			if ( $un_viewed_only ) {
				$params[ 'where' ] = 't.viewed = 0';
			}
			if ( ! is_null( $uID ) ){
				$where = ' t.to_id = "'.$uID.'"';
				if ( isset( $params[ 'where' ] ) ) {
					$params[ 'where' ] = $params['where'] . ' AND ' . $where;
				}
				else {
					$params[ 'where' ] = $where;
				}
			}

		}

		if ( $limit ) {
			$params['limit'] = $limit;
		}

		$params = $this->cache_args( $params );

		return $this->output( $return, HT_DMS_NOTIFICATION_POD_NAME, $params, $preview, $obj );

	}

	/**
	 * @TODO Translate from constants?
	 *
	 * @param        $view
	 * @param bool   $preview
	 * @param bool   $partial
	 * @param string $extension
	 *
	 * @return string
	 */
	function path( $view, $preview = false, $partial = true, $extension = 'php' ) {

		$dir = trailingslashit( HT_DMS_VIEW_DIR );
		if ( $partial ) {
			$dir = trailingslashit( $dir . 'partials' );
		}

		$view = $dir.$view;
		if ( $preview && file_exists( $view.'_preview.'.$extension ) ) {
			$view = $view.'_preview'.'.'.$extension;
		}
		else {
			$view = $view.'.'.$extension;
		}

		return $view;

	}

	/**
	 * Used to add cache args to Pods query.
	 *
	 * @param $params
	 *
	 * @return array
	 *
	 * @since 0.0.1
	 */
	private function cache_args( $params ) {
		if ( self::$cache_mode ) {
			$params[ 'cache_mode' ] = self::$cache_mode;
			$params[ 'expire' ] = self::$cache_length;
		}

		return $params;

	}

	/**
	 * Sets pods:;find() params for single item query.
	 *
	 * @param      $id
	 * @param bool $task
	 *
	 * @return string
	 */
	private function id_params( $id, $task = false ) {
		if ( ! $task  ) {
			$params[ 'where' ] = 't.ID = "'.$id.'" ';
		}
		else {
			$params[ 'where' ] = 't.term_id = "'.$id.'" ';
		}

		return $params;

	}

	/**
	 * Get instance of UI class
	 *
	 * @return 	\ht_dms\ui
	 *
	 * @since 	0.0.1
	 */
	function ui(){
		$ui = ht_dms_ui();

		return $ui;

	}

	/**
	 * Holds the instance of this class.
	 *
	 * @since  0.0.1
	 * @access private
	 * @var    object
	 */
	private static $instance;


	/**
	 * Returns the instance.
	 *
	 * @since  0.0.1
	 * @access public
	 * @return \ht_dms\ui\build\models
	 */
	public static function init() {
		if ( !self::$instance )
			self::$instance = new self;

		return self::$instance;

	}

	private function args( $args ) {
		$params = array(
			0 => 'obj',
			1 => 'id',
			2 => 'preview',
			3 => 'in',
			4 => 'mine',
			5 => 'limit',
			6 => 'public',
			7 => 'status',
			8 => 'return',
			9 => 'page',
			10 => 'un_viewed_only',
		);


		for ( $i = 0; $i < count( $params ); $i++ ) {
			$key = $params[ $i ];
			if ( !isset( $args[ $i ]) ) {

				if ( in_array( $key, array( 'obj', 'id', 'in', 'status' ) )  ) {
					$value = null;
				}
				elseif ( $key === 'return' ) {
					$value = 'template';
				}
				elseif( $key === 'limit' ) {
					$value = 5;
				}
				elseif( $key === 'page' ) {
					$value = 1;
				}
				elseif( $key === 'un_viewed_only' ) {
					$value = true;
				}
				else {
					$value = false;
				}

				if ( !isset( $args[ $key ] ) ) {

					$args[ $key ] = $value;
				}

			}



		}

		if ( is_string( $args[ 'obj' ] ) ) {
			$args[ 'return' ] = $args[ 'obj' ];
			$args[ 'obj' ] = null;
		}

		if ( isset( $args) && is_array( $args ) ) {

			return $args;
			
		}

	}

	/**
	 * Handles Model output
	 *
	 * @param 	string|array $return 	What to return. Either the results as a template, as a Pods object, as JSON object via the REST API, or URL string to pass to the REST API  template|Pods|JSON|urlstring May also be an array, which must have a key called 'view' with the name of a view partial to load.
	 * @param 	string  	$type		Content type
	 * @param 	array   	$params		Pods::find() params
	 * @param 	bool 		$preview	Optional. Defaults to false. Used if returning a template.
	 * @param 	null|Pods 	$obj		Optional. Used if returning template or Pods object. If null, an object will be built.
	 *
	 * @since 	0.0.2
	 *
	 * @return null|string|bool|Pods|JSON
	 */
	function output( $return, $type, $params, $preview = false, $obj = null ) {
		//make sure params['where' ] is string, not array
		if ( is_array( $params ) && isset( $params[ 'where' ] ) && is_array( isset( $params[ 'where' ] ) ) ) {
			$params[ 'where' ] = (string) reset( $params[ 'where' ] );
		}
		if ( is_array( $return ) ) {

			if( ! is_null( $view = pods_v( 'view', $return ) ) ) {
				$return = 'template';
				$view = trailingslashit( HT_DMS_VIEW_DIR ) . 'partials/' . $view;
			}

		}
		elseif ( ! is_string( $return ) || intval( $return ) > 0 ) {
			$return = 'template';
		}
		if ( $return === 'template' || 'Pods' || 'simple_json' ) {
			$short_type = strtolower( $type );

			$short_type = ht_dms_prefix_remover( $short_type );

			if ( is_null( $obj) || ! is_pod( $obj ) ) {
				if ( function_exists( "ht_dms_{$short_type}_class" ) ) {
					$class = call_user_func( "ht_dms_{$short_type}_class" );
					$obj   = $class->null_object( null, $obj );
					$obj   = $obj->find( $params );

				}
				else{
					ht_dms_error( __( sprintf( 'Object can not be built for %1s view', $type ), 'ht_dms' ) , __METHOD__ );
				}


			}

			//store total and total found in cache
			$this->store_total( $obj, $short_type );

			if ( $return === 'simple_json' ) {
				$data = false;
				if ( $obj->total() > 0 ) {
					$type = $obj->pod;
					$type = ht_dms_prefix_remover( $type );
					while( $obj->fetch() )  {

						$datum = call_user_func( array( '\ht_dms\helper\json', $type  ),  $obj->id(), $obj );

						$data[ $obj->id() ] =  $datum;

					}

				}


				if ( is_array( $data ) ) {

					$data = json_encode( $data );
				}

				return $data;

			}


			if ( $return === 'Pods' ) {

				return $obj;

			}

			elseif ( $return === 'template' ) {

				if ( ! isset( $view) ) {
					$view = $this->path( $short_type, $preview );
				}

				$view = $this->ui()->view_loaders()->magic_template( $view, $obj, false );

				return apply_filters( 'ht_dms_models_template_output', $view, $type );

			}
		}

		if ( $return === 'JSON' || $return === 'urlstring' ) {
			if ( ! function_exists( 'json_url' ) ) {
				return false;

			}
			$type = strtolower( $type );
			$url = json_url( "/pods/{$type}?" );
			$url .= http_build_query( $params );

			if ( $return === 'urlstring' ) {
				return $url;

			}

			global $wp_rewrite;

			if ( $return === 'JSON' && defined( 'JSON_API_VERSION' ) && defined( 'PODS_JSON_API_VERSION' ) && $wp_rewrite->permalink_structure === '/%postname%/' ) {

				$response = wp_remote_get( $url );
				if ( is_wp_error( $response ) ) {
					ht_dms_error( __METHOD__, 'invalid remote get response' );
				}

				$data = wp_remote_retrieve_body( $response );

				if ( ! is_wp_error( $data )  ) {

					return $data;

				}

			}
			else {
				ht_dms_error( array( $return,JSON_API_VERSION,PODS_JSON_API_VERSION  ));
			}

		}

		return ht_dms_error( sprintf( 'The model you requested oculd not be returned as either %1s is an invalid value for $return or the return type you requested was unreachable', $return ) );

	}

	/**
	 * Cache total and total_found for last query by type
	 *
	 * @since 0.1.0
	 *
	 * @param \Pods $obj
	 * @param string $short_type Type of query
	 */
	private function store_total( $obj, $short_type ) {
		$total = (int) $obj->total();
		$total_found = (int) $obj->total_found();
		$key = "last_{$short_type}_total";
		wp_cache_set( $key, $total );
		$key .= '_found';
		wp_cache_set( $key, $total_found );
	}

	/**
	 * Get the total and total_found for last query by type.
	 *
	 * @since 0.1.0
	 *
	 * @param string $short_type Type of query
	 *
	 * @return array
	 */
	public static function get_total( $short_type ) {
		$key = "last_{$short_type}_total";
		$totals[ 'total' ] = wp_cache_get( $key );
		$key .= '_found';
		$totals[ 'total_found' ] = wp_cache_get( $key );
		return $totals;

	}
	
} 
