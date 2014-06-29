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
	 * object|transient|site-transient
	 * @var string
	 *
	 * @since 0.0.1
	 */
	static public $cache_mode = 'object';

	/**
	 * PARAMS FOR ALL MODELS
	 *
	 * @param $args['obj'] Pods|obj|null Default is null.
	 * @param $args['id'] int|null. Set an ID to show single item. Default is false.
	 * @param $args['preview'] bool Default is false
	 * @param $args['in'] Default is null.
	 * @param $args['mine'] int|bool Only for one user if true( current user) or use a user ID. Default is false
	 * @param $args['limit'] int Default is 5.
	 * @param $args['status'] string|null decision/task status. Default is null.
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

		}

		$params = $this->cache_args( $params );

		if ( is_null( $obj) || ! is_pod( $obj ) ) {
			$obj = pods( HT_DMS_ORGANIZATION_NAME, $params );
		}


		$view = $this->path( 'organization', $preview );

		return $this->ui()->view_loaders()->magic_template( $view, $obj, true );

	}

	function group( $args ) {
		$g = holotree_group_class();

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

			if ( is_array( $in ) || is_int( $in ) ) {
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
		}

		$params = $this->cache_args( $params );

		if ( is_null( $obj) || ! is_pod( $obj ) ) {
			$obj = pods( HT_DMS_GROUP_CPT_NAME, $params );
		}


		$view = $this->path( 'group', $preview );

		return $this->ui()->view_loaders()->magic_template( $view, $obj );

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
				if ( $in[ 'what' ] === 'group' || $in[ 'what' ] === HT_DMS_GROUP_CPT_NAME ) {
					$in_where = 'group.ID =';
				}
				elseif ( $in[ 'what' ] === 'organization' || $in[ 'what' ] === HT_DMS_ORGANIZATION_NAME ) {
					$in_where = 'organization.ID =';
				}
				else {
					holotree_error();
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

		}

		$params = $this->cache_args( $params );

		if ( is_null( $obj) || ! is_pod( $obj ) ) {
			$obj = pods( HT_DMS_DECISION_CPT_NAME, $params );
		}

		$view = $this->path( 'decision', $preview );

		return $this->ui()->view_loaders()->magic_template( $view, $obj );

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
					if ( $in[ 'what' ] === 'group' || $in[ 'what' ] === HT_DMS_GROUP_CPT_NAME ) {
						$in_where = 'group.ID =';
					}
					elseif ( $in[ 'what' ] === 'organization' || $in[ 'what' ] === HT_DMS_ORGANIZATION_NAME ) {
						$in_where = 'organization.ID =';
					}
					elseif( $in[ 'what' ] === 'decision' || $in[ 'what' ] === HT_DMS_DECISION_CPT_NAME ) {
						$in_where = 'decision.ID =';
					}
					else {
						holotree_error();
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

		}

		$params = $this->cache_args( $params );

		if ( is_null( $obj) || ! is_pod( $obj ) ) {
			$obj = pods( HT_DMS_TASK_CT_NAME, $params );
		}


		$view = $this->path( 'task', $preview );

		return $this->ui()->view_loaders()->magic_template( $view, $obj );

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

		//return trailingslashit( HT_DMS_VIEW_DIR ).'partials/foo.php';
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
	 * @return 	\holotree\ui
	 *
	 * @since 	0.0.1
	 */
	function ui(){
		$ui = holotree_dms_ui();

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
	 * @return object
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
		);

		for ( $i = 0; $i < 8; $i++ ) {
			$key = $params[ $i ];
			if ( !isset( $args[ $i ]) ) {

				if ( in_array( $key, array( 'obj', 'id', 'in', 'status' ) )  ) {
					$value = null;
				}
				elseif( $key === 'limit' ) {
					$value = 5;
				}
				else {
					$value = false;
				}

				if ( !isset( $args[ $key ] ) ) {

					$args[ $key ] = $value;
				}

			}



		}

		if ( isset( $args) && is_array( $args ) ) {

			return $args;
			
		}

	}
	
} 
