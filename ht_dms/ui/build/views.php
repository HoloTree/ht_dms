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

class views {

	/**
	 * Get the models class
	 *
	 * @return object|models
	 *
	 * @since 0.0.1
	 */
	function models() {
		include_once( trailingslashit( HT_DMS_UI_DIR ).'build/models.php' );

		return models::init();
	}

	/**
	 * Get the users_groups view - All groups a user is a member of.
	 *
	 * @param	Pods|null	$obj		Optional. A Pods object.
	 * @param 	int|null  	$uID        Optional. ID of user to get. If null, the default, current user's groups are shown.
	 * @param 	int|null  	$oID        Optional. ID of organization to limit groups to. If null, the default, groups in all organizations are returned.
	 * @param 	int       	$limit      Optional. Number of groups to return. Default is 5.
	 * @param null|string 	$return		Optional. What to return. If used, overrides $args[ 'return'] Options: template|Pods|JSON|urlstring
	 * @param int|bool		$page		Optional. Page of results to show.
	 *
	 * @return string|JSON          Either HTML for the view, or a Pods object, or a JSON object of the posts, or a URL string to get those posts via REST API.
	 *
	 * @since   0.0.2
	 */
	function users_groups( $obj = null, $uID = null, $oID = null, $limit = 5, $return = 'template', $page = false  ) {

		if ( ! is_array( $obj ) ) {
			$args = array (
				'obj'     => $obj,
				'mine'    => $uID,
				'in'      => $oID,
				'limit'   => $limit,
				'page'    => $page,
				'preview' => true,
				'return'  => $return,
			);
		}
		else {
			$args = $obj;
		}

		return $this->models()->group( $args );

	}

	/**
	 * Get the public_groups view - All public groups
	 *
	 * @param    Pods|null 	$obj 		Optional. A Pods object.
	 * @param 	int|null  	$oID        Optional. ID of organization to limit groups to. If null, the default, groups in all organizations are returned.
	 * @param 	int       	$limit      Optional. Number of groups to return. Default is 5.
	 * @param null|string 	$return		Optional. What to return. If used, overrides $args[ 'return'] Options: template|Pods|JSON|urlstring
	 * @param int|bool		$page		Optional. Page of results to show.
	 *
	 * @return string|JSON          	Either HTML for the view, or a Pods object, or a JSON object of the posts, or a URL string to get those posts via REST API.
	 *
	 * @since   0.0.2
	 */
	function public_groups( $obj = null, $oID = null, $limit = 5, $return = 'template', $page = false  ) {

		if ( ! is_array ( $obj ) ) {
			$args = array (
				'obj'     => $obj,
				'in'      => $oID,
				'limit'   => $limit,
				'preview' => true,
				'return'  => $return,
				'page'    => $page,
			);
		}
		else {
			$args = $obj;
		}

		return $this->models()->group( $args );

	}

	/**
	 * Get the assigned_tasks view - All tasks a user is assigned to.
	 *
	 * @param	Pods|null	$obj		Optional. A Pods object.
	 * @param 	int|null  	$uID        Optional. ID of user to get tasks for. If null, the default, current user's tasks are shown.
	 * @param 	int|null  	$oID        Optional. ID of organization to limit tasks to. If null, the default, tasks in all organizations are returned.
	 * @param 	int       	$limit      Optional. Number of tasks to return. Default is 5.
	 * @param  	null|string $return		Optional. What to return. If used, overrides $args[ 'return'] Options: template|Pods|JSON|urlstring
	 * @param int|bool		$page		Optional. Page of results to show.
	 *
	 * @return string|JSON          Either HTML for the view, or a Pods object, or a JSON object of the posts, or a URL string to get those posts via REST API.
	 *
	 * @since   0.0.2
	 */
	function assigned_tasks( $obj = null, $uID = null, $oID = null, $limit = 5, $return = 'template', $page = false  ) {

		if ( ! is_array( $obj ) ) {
			$args = array(
				'obj' 		=> $obj,
				'mine' 		=> $uID,
				'in'		=> $oID,
				'limit' 	=> $limit,
				'preview' 	=> true,
				'return'	=> $return,
				'page'    	=> $page,
			);
		}
		else {
			$args = $obj;
		}

		return $this->models()->task( $args );

	}

	/**
	 * Get the users_organizations view - All organizations a user is a member of.
	 *
	 * @param    Pods|null $obj Optional. A Pods object.
	 * @param 	 int|null  	$uID        Optional. ID of user to get organizations for. If null, the default, current user's organizations are shown.
	 * @param 	 int       	$limit      Optional. Number of organizations to return. Default is 5.
	 * @param 	null|string $return		Optional. What to return. If used, overrides $args[ 'return'] Options: template|Pods|JSON|urlstring
	 *
	 * @return string|JSON          Either HTML for the view, or a Pods object, or a JSON object of the posts, or a URL string to get those posts via REST API.
	 *
	 * @since   0.0.2
	 */
	function users_organizations( $obj = null, $uID = null, $limit = 5, $return = 'template', $page = false  ) {

		if ( ! is_array( $obj ) ) {
			$args = array (
				'obj'     => $obj,
				'mine'    => $uID,
				'limit'   => $limit,
				'preview' => true,
				'return'  => $return,
				'page'    => $page,
			);
		}
		else {
			$args = $obj;
		}

		return $this->models()->organization( $args );

	}

	/**
	 * Get the decisions_tasks view - All (or some) tasks for a decision.
	 *
	 * @todo normalize this one
	 *
	 * @param   Pods|null $obj 			Optional. A Pods object.
	 * @param 	int|null  $id          ID of decision to get tasks from.
	 * @param 	int       $limit       Optional. Number of tasks to return. Default is 5. Use -1 for all.
	 * @param 	null|string $return		Optional. What to return. If used, overrides $args[ 'return'] Options: template|Pods|JSON|urlstring
	 * @param int|bool		$page		Optional. Page of results to show.
	 *
	 * @return string|JSON          Either HTML for the view, or a Pods object, or a JSON object of the posts, or a URL string to get those posts via REST API.
	 *
	 * @since   0.0.2
	 */
	function decisions_tasks( $obj = null, $id, $limit = 5, $return = 'template', $page = false  ) {
		$params[ 'where' ] = 'decision.ID = "'.$id.'"';
		$params[ 'limit'] = $limit;
		if ( $page ) {
			$params[ 'page' ] = $page;
		}
		$obj = pods( HT_DMS_TASK_CT_NAME, $params );

		if ( $return === 'template' ) {
			if ( $obj->total() > 0 ) {
				$view_loaders = ht_dms_ui()->view_loaders();
				$view         = ht_dms_ui()->models()->path( 'task', true );

				return $view_loaders->magic_template( $view, $obj );

			}

			return __( 'This decision has no tasks', 'holotree' );

		}
		elseif ( $return === 'Pods' ) {

			return $obj;

		}
		else {

			return holotree_error( '<a href="https://github.com/HoloTree/ht_dms/issues/25">ISSUE!</a>' );

		}

	}

	/**
	 * Get single organization view.
	 *
	 * @param   Pods|null 	$obj 		Optional. A Pods object.
	 * @param 	int       	$id         ID of organization to get
	 * @param 	null|string $return		Optional. What to return. If used, overrides $args[ 'return'] Options: template|Pods|JSON|urlstring
	 *
	 * @return string|JSON          Either HTML for the view, or a Pods object, or a JSON object of the posts, or a URL string to get those posts via REST API.
	 *
	 * @since   0.0.2
	 */
	function organization( $obj = null, $id, $return = 'template'  ) {

		$args = array(
			'obj' 		=> $obj,
			'id'		=> $id,
			'preview' 	=> false,
			'return'	=> $return,
		);

		return $this->models()->organization( $args );
		
	}

	/**
	 * Get single group view.
	 *
	 * @param   Pods|null $obj Optional. A Pods object.
	 * @param   int  		$id
	 * @param 	null|string $return		Optional. What to return. If used, overrides $args[ 'return'] Options: template|Pods|JSON|urlstring
	 *
	 * @return string|JSON          Either HTML for the view, or a Pods object, or a JSON object of the posts, or a URL string to get those posts via REST API.
	 *
	 * @since   0.0.2
	 */
	function group( $obj = null, $id, $return = 'template'  ) {

		$args = array(
			'obj' 		=> $obj,
			'id'		=> $id,
			'preview' 	=> false,
			'return'	=> $return,
		);

		return $this->models()->group( $args );

	}

	/**
	 * Get single decision view.
	 *
	 * @param   Pods|null $obj Optional. A Pods object.
	 * @param   int  		$id
	 * @param 	null|string $return		Optional. What to return. If used, overrides $args[ 'return'] Options: template|Pods|JSON|urlstring
	 *
	 * @return string|JSON          Either HTML for the view, or a Pods object, or a JSON object of the posts, or a URL string to get those posts via REST API.
	 *
	 * @since   0.0.2
	 */
	function decision( $obj = null, $id, $return = 'template'  ) {

		$args = array(
			'obj' 		=> $obj,
			'id'		=> $id,
			'preview' 	=> false,
			'return'	=> $return,
		);

		return $this->models()->decision( $args );

	}

	/**
	 * Get task decision view.
	 *
	 * @param   Pods|null $obj Optional. A Pods object.
	 * @param   int  		$id
	 * @param 	null|string $return		Optional. What to return. If used, overrides $args[ 'return'] Options: template|Pods|JSON|urlstring
	 *
	 * @return string|JSON          Either HTML for the view, or a Pods object, or a JSON object of the posts, or a URL string to get those posts via REST API.
	 *
	 * @since   0.0.2
	 */
	function task( $obj = null, $id, $return = 'template'  ) {

		$args = array(
			'obj' 		=> $obj,
			'id'		=> $id,
			'preview' 	=> false,
			'return'	=> $return,
		);

		return $this->models()->task( $args );

	}

	/**
	 * Get a user's notifications
	 *
	 * @param null|obj|pods|array $obj Optional. A notification Pods object. Could also be an array of arguments, which overrides all other params.
	 * @param null|int  	$uID Optional. User ID. Defaults to current user ID.
	 * @param bool   		$un_viewed_only Optional. To show only unviewed items. Default is true.
	 * @param int    		$limit		Optional. Total number to show default is 5.
	 * @param string 		$return		Optional. Either HTML for the view, or a Pods object, or a JSON object of the posts, or a URL string to get those posts via REST API.
	 * @param bool   		$page		Optional. Page of results to return. If false, the default, first page is returned.
	 *
	 * @return bool|JSON|Pods|null|string
	 *
	 * @since 0.0.3
	 */
	function users_notifications( $obj = null, $uID = null, $un_viewed_only = true, $limit = 5, $return = 'template', $page = false  ) {
		if ( ! is_array( $obj ) ) {
			$args = array (
				'obj'     => $obj,
				'uID'	  => $uID,
				'limit'   => $limit,
				'preview' => true,
				'return'  => $return,
				'page'    => $page,
				'un_viewed_only' => $un_viewed_only,
			);
		}
		else {
			$args = $obj;
		}


		return $this->models()->notification( $args );

	}

	/**
	 * Get a single notification.
	 *
	 * @param null|obj|Pods|array $obj Optional. A notification Pods object. Could also be an array of arguments, which overrides all other params.
	 * @param bool   		$id  ID of  notification to get. Technically optional, but if $obj is not an array, it must be used.
	 * @param string 		$return		Optional. Either HTML for the view, or a Pods object, or a JSON object of the posts, or a URL string to get those posts via REST API.
	 *
	 * @return bool|JSON|Pods|null|string
	 *
	 * @since 0.0.3
	 */
	function notification( $obj = null, $id = false, $return = 'template' ) {
		if ( ! is_array( $obj ) ) {
			$args = array (
				'obj'     => $obj,
				'id'	  => $id,
				'preview' => false,
				'return'  => $return,
				'un_viewed_only' => false,
			);
		}
		else {
			$args = $obj;
		}

		return $this->models()->notification( $args );

	}

	/**
	 * View or edit user's profile or notification preferences
	 *
	 * @param int|null 	$uID
	 * @param bool 		$edit
	 * @param bool 		$notification
	 *
	 * @return string
	 *
	 * @since 0.0.3
	 */
	function preferences( $uID = null, $edit = false, $notification = false ) {
		$class = ht_dms_preferences_class();
		if ( $edit ) {
			$out = $class->edit_form( null, $uID, null, $notification );
		}
		else {
			$out = $class->profile_list( $uID );
		}

		return $out;
	}

	/**
	 * Show a task or decision documents.
	 *
	 * @param null $obj
	 * @param      $id
	 * @param      $type
	 *
	 * @return bool|string
	 */
	function docs( $obj = null, $id, $type ) {
		if ( false ) {
			//@todo ensure this really isn't needed and cut
		//$todo figure out why tasks keep flipping args making this needed.
			if ( is_int( $type ) ) {
				$i    = $type;
				$type = $id;
				$id   = $i;
			}
		}

		$args = array(
			'obj' 		=> $obj,
			'id'		=> $id,
			'preview' 	=> false,
			'return'	=> 'Pods',
		);

		$type = ht_dms_prefix_remover( $type );


		if ( $type === 'task' ) {
			$obj =  $this->models()->task( $args );

		} elseif ( $type === 'decision' ) {
			$obj = $this->models()->decision( $args );

		}
		else {
			holotree_error( __( sprintf( '%1s is not a valid type for %2s.', $type, __METHOD__ ), 'holotree' ) );

		}

		$docs = $obj->field( 'documents' );

		$expires = 85321;
		$cache_type = 'object';
		if ( defined( 'HT_DEV_MODE' ) && HT_DEV_MODE ) {
			$cache_type = $expires = false;
		}

		$form = $this->ui()->add_modify()->add_doc( $id );

		return pods_view( HT_DMS_VIEW_DIR.'/partials/documents.php', compact( array( 'docs', 'type', 'form' ) ), $expires, $cache_type, true );


	}

	function action_buttons( $obj, $id, $what ) {

		if ( $what === 'task' || $what === HT_DMS_TASK_CT_NAME ) {

			return $this->ui()->build_elements()->task_actions( $id, $obj );

		}
		else {

			return $this->ui()->build_elements()->action_buttons( $what, $id, $obj );

		}

	}

	/**
	 * View proposed modifications to current decision.
	 *
	 * @param 	int     		$id		ID of decision to see proposed changes of.
	 * @param 	Pods|obj|null 	$obj	Optional. Single Pods Object
	 *
	 * @return string					Decision view.
	 *
	 * @since 0.0.2
	 */
	function proposed_modifications( $id, $obj = null ) {
		$decision_class = ht_dms_decision_class();
		$obj = $decision_class->null_object( $obj, $id );
		if ( $decision_class->has_proposed_modification( $id, $obj, true ) ) {

			$ids = $decision_class->proposed_modifications( $id, null, true );
			if ( empty( $ids ) ) {
				return false;
			}
			
			if ( is_array( $ids ) && ! isset( $ids[0] ) ) {
				$x = $ids[0];
				unset( $ids );
				$ids = $x;
			}

			if ( is_array( $ids ) ) {

				foreach ( $ids as $id ) {
					$pObj = ht_dms_decision( $id );
					$args = array(
						'obj' 		=> $pObj,
						'id'		=> $id,
						'preview' 	=> true,
						'return'	=> 'template',
					);


					$proposed_changes[ $id ] = $this->ui()->models()->decision( $args );
				}

				if ( isset( $proposed_changes ) && is_array( $proposed_changes ) ) {
					$proposed_changes = implode( '<br>', $proposed_changes );

					return $proposed_changes;
				}
			}
		}
	}

	/**
	 * Adds additional markup for notifications view to allow AJAX-based UI.
	 *
	 * @uses ht_dms_models_template_output filter
	 *
	 * @param $view
	 * @param $type
	 *
	 * @return string
	 *
	 * @#since 0.0.3
	 */
	function after_notification_preview( $out, $view ) {
		if ( $view === 'users_notifications' ) {

			$single_view = '<div id="notification-single-view"> </div>';

			$header = sprintf(
				'<div id="notifications-header"><h3 style="float:left">%1s</h3> <span id="notification-options" class="button" style="float:right"><a href="#" id="unviewed-only">%2s</a></span></div>',

				__( 'Notifications', 'ht_dms' ),
				__( 'Show New Notifications Only' , 'ht_dms' )

			);


			$out = sprintf( '<div id="notification-viewer">%0s %1s %2s</div>', $header,  $out, $single_view );

		}

		return $out;

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

	/**
	 * Get instance of UI class
	 *
	 * @return 	\holotree\ui
	 *
	 * @since 	0.0.1
	 */
	function ui(){
		$ui = ht_dms_ui();

		return $ui;

	}
} 
