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
	function organization( 	$obj = null, $preview = false, $in = false, $mine = false, $limit = 5, $public = true ) {
		$params = null;

		if ( $preview && intval( $preview ) !== 0 ) {
			$params = (int) $preview;
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

		$obj = holotree_organization_class()->null_object( $obj, $params );

		$view = $this->path( 'organization', $preview );

		return $this->ui()->view_loaders()->magic_template( $view, $obj, true );

	}

	function group( 		$obj = null, $preview = false, $in = false, $mine = false, $limit = 5, $public = true ) {
		$g = holotree_group_class();


		if ( $preview && intval( $preview ) !== 0 ) {
			$params = (int) $preview;
		}
		elseif ( $mine ) {
			$obj = $g->users_groups_obj( $mine, $obj, $limit, $in );
		}
		else {
			$params = null;
			if ( is_array( $in ) || is_int( $in ) ) {
				if ( is_array( $in ) ) {
					if ( isset( $in[ 'ID' ] ) ) {
						$in = $in[ 'ID' ];
					}
					else {
						$in = $in[ 0 ];
					}
				}

				$params[ 'where' ] = 'organization.ID = "'.$in.'"';
			}
			else {

				if ( $public ) {
					$params[ 'where' ] = 'd.visibility = "public"';
				}
			}

			$params[ 'limit' ] = $limit;
		}

		$obj = $g->null_object( $obj, $params );

		$view = $this->path( 'group', $preview );

		return $this->ui()->view_loaders()->magic_template( $view, $obj, true );



	}

	function decision( 		$obj = null, $preview = false, $in = null, $mine = false, $limit = 5, $status = null ) {
		$id = null;
		$params = null;

		if ( $preview && intval( $preview ) !== 0 ) {
			$params = (int) $preview;
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

		$obj = holotree_decision_class()->null_object( $obj, $params );

		$view = $this->path( 'decision', $preview );

		return $this->ui()->view_loaders()->magic_template( $view, $obj );

	}

	function task( 			$obj = null, $preview = false, $in = null, $mine = false, $limit = 5, $status = null ) {

		$params = null;

		if ( $preview && intval( $preview ) !== 0 ) {
			$params = (int) $preview;
		}
		else {
			if ( $mine  ) {
				if ( !is_int( $mine ) || intval( $mine ) == 0 ) {
					$mine = get_current_user_id();
				}
				$where = 'assigned_user.ID = "'.$mine.'"';
			}
			elseif ( !is_null( $in ) ) {
				if ( $in[0] === 'multi' ) {
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

					$in_where = $in_where.' "'.$in[ 'ID' ].'"';

					if ( isset ( $where ) ) {
						$where = $where . ' AND ' . $in_where;
					}
					else {
						$where = $in_where;
					}

				}
			}

			if ( is_null( $status ) ) {
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

		$obj = holotree_task_class()->null_object( $obj, $params );

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
	private function path( $view, $preview = false, $partial = true, $extension = 'php' ) {
		$dir = trailingslashit( HT_DMS_VIEW_DIR );
		if ( $partial ) {
			$dir = trailingslashit( $dir . 'partials' );
		}

		$view = $dir.$view;
		if ( $preview && file_exists( $view.'_preview.'.$extension ) ) {
			$view = $view.'_preview';
		}
		else {
			$view = $view.'.'.$extension;
		}

		return $view;

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

} 
