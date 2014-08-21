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
	 *
	 * @return string|JSON          Either HTML for the view, or a Pods object, or a JSON object of the posts, or a URL string to get those posts via REST API.
	 *
	 * @since   0.0.2
	 */
	function users_groups( $obj = null, $uID = null, $oID = null, $limit = 5, $return = 'template'  ) {
		$args = array(
			'obj' 		=> $obj,
			'mine' 		=> $uID,
			'in'		=> $oID,
			'limit' 	=> $limit,
			'preview' 	=> true,
			'return'	=> $return,
		);

		return $this->models()->group( $args );

	}

	/**
	 * Get the public_groups view - All public groups
	 *
	 * @param    Pods|null 	$obj 		Optional. A Pods object.
	 * @param 	int|null  	$oID        Optional. ID of organization to limit groups to. If null, the default, groups in all organizations are returned.
	 * @param 	int       	$limit      Optional. Number of groups to return. Default is 5.
	 * @param null|string 	$return		Optional. What to return. If used, overrides $args[ 'return'] Options: template|Pods|JSON|urlstring
	 *
	 * @return string|JSON          	Either HTML for the view, or a Pods object, or a JSON object of the posts, or a URL string to get those posts via REST API.
	 *
	 * @since   0.0.2
	 */
	function public_groups( $obj = null, $oID = null, $limit = 5, $return = 'template'  ) {

		$args = array(
			'obj' 		=> $obj,
			'in'		=> $oID,
			'limit' 	=> $limit,
			'preview' 	=> true,
			'return'	=> $return,
		);

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
	 *
	 * @return string|JSON          Either HTML for the view, or a Pods object, or a JSON object of the posts, or a URL string to get those posts via REST API.
	 *
	 * @since   0.0.2
	 */
	function assigned_tasks( $obj = null, $uID = null, $oID = null, $limit = 5, $return = 'template'  ) {

		$args = array(
			'obj' 		=> $obj,
			'mine' 		=> $uID,
			'in'		=> $oID,
			'limit' 	=> $limit,
			'preview' 	=> true,
			'return'	=> $return,
		);

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
	function users_organizations( $obj = null, $uID = null, $limit = 5, $return = 'template'  ) {

		$args = array(
			'obj' 		=> $obj,
			'mine' 		=> $uID,
			'limit' 	=> $limit,
			'preview' 	=> true,
			'return'	=> $return,
		);

		return $this->models()->organization( $args );

	}

	/**
	 * Get the decisions_tasks view - All (or some) tasks for a decision.
	 *
	 * @param   Pods|null $obj 			Optional. A Pods object.
	 * @param 	int|null  $id          ID of decision to get tasks from.
	 * @param 	int       $limit       Optional. Number of tasks to return. Default is 5. Use -1 for all.
	 * @param 	null|string $return		Optional. What to return. If used, overrides $args[ 'return'] Options: template|Pods|JSON|urlstring
	 *
	 * @return string|JSON          Either HTML for the view, or a Pods object, or a JSON object of the posts, or a URL string to get those posts via REST API.
	 *
	 * @since   0.0.2
	 */
	function decisions_tasks( $obj = null, $id, $limit = 5, $return = 'template'  ) {
		$params[ 'where' ] = 'decision.ID = "'.$id.'"';
		$params[ 'limit'] = $limit;
		$obj = pods( HT_DMS_TASK_CT_NAME, $params );

		if ( $obj->total() > 0 ) {
			$view_loaders = holotree_dms_ui()->view_loaders();
			$view =  holotree_dms_ui()->models()->path( 'task', true  );

			return $view_loaders->magic_template( $view, $obj );

		}

		return __( 'This decision has no tasks', 'holotree' );

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
	 * Show a task or decision documents.
	 *
	 * @param null $obj
	 * @param      $id
	 * @param      $type
	 *
	 * @return bool|string
	 */
	function docs( $obj = null, $id, $type ) {
		//$todo figure out why tasks keep flipping args making this needed.
		if ( is_int( $type ) ){
			$i = $type;
			$type = $id;
			$id = $i;
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

		return pods_view( HT_DMS_VIEW_DIR.'/partials/documents.php', compact( array( 'docs', 'type' ) ), $expires, $cache_type, true );


	}

	function action_buttons( $obj, $id, $what ) {

		if ( $what === 'task' || $what === HT_DMS_TASK_CT_NAME ) {

			return $this->ui()->build_elements()->task_actions( $id, $obj );

		}
		else {

			return $this->ui()->build_elements()->action_buttons( $what, $id, $obj );

		}

	}

	function notifications() {
		return '@TODO === this:(';
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
		$decision_class = holotree_decision_class();
		$obj = $decision_class->null_object( $obj, $id );
		if ( $decision_class->has_proposed_modification( $id, $obj, true ) ) {

			$ids = $decision_class->proposed_modifications( $id, $obj, true );

			if ( is_array( $ids ) ) {

				foreach ( $ids as $id ) {
					$pObj = holotree_decision( $id );
					$proposed_changes[ $id ] = $this->decision( $pObj, $id );
				}

				if ( isset( $proposed_changes ) && is_array( $proposed_changes ) ) {
					$proposed_changes = implode( '<br>', $proposed_changes );

					return $proposed_changes;
				}
			}
		}
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
		$ui = holotree_dms_ui();

		return $ui;

	}
} 
