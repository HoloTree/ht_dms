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
	 * Preview a decision.
	 *
	 * @param int $id ID of Item to view
	 * @param string $what What type of view to use
	 *
	 * @return string
	 */
	function decision_preview( $item, $what ) {
		if ( $item != FALSE && !empty( $item )  ) {
			$id = $item[ 'id' ];
			$out = '<div class="view-preview-'.$what.'-view" id="'.$what.'-view-' . $id . '">';
			$out .= '<h5>'.holotree_link( $id, 'post', $item[ 'post_title' ], $item[ 'post_title' ] ).'</h5>';
			$out .= '<div class="'.$what.'-status view-preview-status" id="'.$what.'' . $id . '" style ="float:right;">';
			$out .= $item[ 'decision_status' ];
			if ( HT_DEV_MODE ) {
				$out .= $id;
			}
			$out .= '</div><!--.view-preview-status-->';
			if (  $item[ 'id' ]  !== $item[ 'change_to' ]['id']  ) {
				$out .= $this->change_to( $change_to = $item[ 'change_to' ] );
				if ( HT_DEV_MODE ) {
					$out .= $item[ 'change_to' ][ 'id' ] .'-' . $item[ 'id' ];
				}
			}
			$out .= '<div class="'.$what.'-description view-preview-description decision-description" id="'.$what.'->' . $id . '">';
			$out .= $item[ 'decision_description' ];
			$out .= '</div><!--.view-preview-description-->';
			$out .= '<div class="'.$what.'-links view-preview-links">';
			if ( is_singular( HT_DMS_DECISION_CPT_NAME  ) ) {
				$out .= $this->action_buttons( $what, $id );
			}
			else {
				$out .= holotree_link( $id, 'post', 'View', $item[ 'post_title' ], true );
			}
			$out .= '</div><!--.view-preview-links-->';
			$out .= '</div><!--.view-preview-view-->';
			return $out;
		}
	}

	/**
	 * View for all public fields of a decision.
	 *
	 * Note: both params are optional, but at least one must be set.
	 *
	 * @TODO Nwed a way to detect if $obj is a <em>Single</em> Pods object.
	 *
	 * @param 	int|null $dID 	ID of a decision.
	 * @param 	obj|null $obj 	A Pods object for a single decision.
	 *
	 * @return 	string	 $out	The output as a long string.
	 */
	function decision( $dID = null, $obj = null ) {
		if ( is_null( $obj ) && is_null( $dID ) ) {
			holotree_error( 'Either decision id ($dID) or a valid pods object for a single decision object ($obj) must be given for ', __METHOD__ );
		}
		else {

			if ( is_null( $obj ) ) {
				$obj = holotree_decision_class()->single_decision_object( $dID );
			}

			$fields = $obj->fields();
			unset( $fields[ 'consensus' ] );
			unset( $fields[ 'change_to'] );

			if ( $obj->field( 'decision_type' ) !== 'change'  ) {
				unset( $fields[ 'reason_for_change' ] );
			}

			$out = '<div class="view-decision" id="-view-' . $obj->id() . '">';
			$out .= '<h5>' . holotree_link( $obj->id(), 'post', $obj->field( 'post_title' ) ) . '</h5>';
			if ( HT_DEV_MODE ) {
				$out .= $obj->id();
			}
			if ( $obj->field( 'change_to' ) !== false ) {
				$out .= $this->change_to( $change_to = $obj->field( 'change_to' ) );
			}

			$out .= '<ul>';

			foreach ( $fields as $k => $v ) {

				if ( $obj->field( $k ) !== FALSE ) {
					$out .= '<li><span class="decision-view-field-label">';
					$out .= $v[ 'label' ]. ': ';
					$out .= '</span>';
					$this_field = $obj->field( $k );
					if ( !is_array( $this_field ) ) {

						$out .= $obj->field( $k );
					}
					else {

						if ( $k === 'proposed_by' || $k === 'manager' ) {
							if ( $k === 'proposed_by' ) {
								$user = $obj->field( 'proposed_by' );
							}
							elseif ( $k === 'manager' ) {
								$user = $obj->field( 'manager' );
							}

							$out .= holotree_link( $user[ 'ID' ], 'user', $user[ 'display_name' ], $user[ 'display_name' ] );

						}
						elseif ( $k === 'facilitators' ) {
							$facilitators = $obj->field( 'facilitators' );
							$out .= '<ul>';
							foreach ( $facilitators as $user ) {
								$out .= '<li>' . holotree_link( $user[ 'ID' ], 'user', $user[ 'display_name' ], $user[ 'display_name' ] ) . '</li>';
							}
							$out .= '</ul>';
						}
						elseif ( $k === 'change_to' ) {
							$field = $obj->field( 'change_to' );
							$out .= holotree_link( $field[ 'ID' ], 'permalink', $field[ 'post_title' ], $field[ 'post_title' ] );


						}
						elseif( $k === 'group' ) {
							$field = $obj->field( 'group' );
							$field = $field[ 0 ];
							$out .= holotree_link( $field[ 'ID' ], 'permalink', $field[ 'post_title' ], $field[ 'post_title' ] );
						}
						elseif ($k === 'tasks' ) {
							$tasks = $obj->field( 'tasks' );
							if ( is_array( $tasks  ) ) {
								$out .= '<ul class="task-list-in-decision-info">';
								foreach ( $tasks as $task ) {
									$id = (int) $task[ 'term_id' ];
									if ( holotree_task_class()->task_exists( $id ) ) {
										$out .= '<li>'.holotree_link( $id, 'tax', $task[ 'name' ], $task[ 'name' ] ).'</li>';
									}
								}

								$out .= '</ul><!--.task-list-in-decision-info-->';
							}

						}
						else {
							$this_field = $obj->field( $k );
							if ( isset( $this_field[ 0 ][ 'ID' ] ) ) {
								$this_field = (int) $this_field[ 0 ][ 'ID' ];
								$out .= $this_field;
							}
						}

					}

					$out .= '</li>';

				}
			}
			$out .= '</ul></div>';


			return $out;
		}
	}

	/**
	 * Creates tabs for decisions by status
	 * @param 	array|null	$statuses
	 * @param 	int			$gID
	 * @param 	obj|null 	$dObj		Optional. A full decision object.
	 *
	 * @return	array					Tabs array to pass to the tab maker.
	 */
	function decisions_by_status_tabs( $statuses = null, $gID, $dObj= null  ) {
		$dObj = holotree_decision( false, true, false, $dObj );
		if ( is_null( $statuses ) || ! is_array( $statuses ) ) {
			$statuses = array ( 'New', 'Blocked', 'Passed' );
		}

		$ui = $this->ui();

		foreach ( $statuses as $status  ) {
			//@TODO limit/ pagination for individual statuses: How to do that?
			$params = array (
				'where' => 'd.decision_type <> "accepted_change" AND group.ID = " ' . $gID. ' "  AND d.decision_status = "'. strtolower( $status ) .'" ',
				'limit'	=> -1,
			);
			$dObj = $dObj->find( $params );
			$total = $dObj->total();
			if ( HT_DEV_MODE ) {
				echo $status . ':'. $total. ' ';
			}

			if ( $dObj->total() > 0 ) {
				$decisions[ $status ] = '<div id="' . $status . '-decisions-list" class="decisions-list">';
				$heading = $status.' Decisions';
				$decisions[ $status ] = '<h3>' . $heading . '</h3>';
				while ( $dObj->fetch() ) {
					$item = array();
					$fields = $ui->views()->decision_output_fields();
					foreach ( $fields as $field ) {
						$item[ $field ] = $dObj->field( $field );
					} //endforeach fields
					$decision_fields[] =  $item;
					$decisions[ $status ] = $decision_fields;
				} //endwhile pods loop
			} //endif have pods

			$decision_fields = array();

		}

		$tabs = array( );
		foreach ( $statuses as $status  ) {
			if ( isset( $decisions[ $status ] ) ) {
				$ds = $decisions[ $status ];
				$content = '';
				foreach ( $ds as $item ) {
					$content .= $ui->views()->decision_preview( $item, $status );
				}
				$tabs[] = array(
					'label'		=> __( $status. ' Decisions', 'holotree' ),
					'content'	=> $content,
				);

			}

		}

		return $tabs;

	}


	function change_to( $change_to ) {
		if ( $change_to !== false ) {
			$out = '<div class="decision-view-change-to">Proposed Change To: ';
			$out .= holotree_link( $change_to[ 'ID' ], 'post', $change_to[ 'post_title' ], $change_to[ 'post_title' ] );
			if ( HT_DEV_MODE ) {
				$out .= $change_to[ 'ID' ];
			}
			$out .= '</div>';

			return $out;
		}
	}

	/**
	 * View preview all tasks for a decision or for a user.
	 *
	 * @TODO Assigned tasks for a decision.
	 *
	 * @TODO Pagination.
	 *
	 * @param 	int			$dID		ID of decision.
	 * @param 	int|null 	$dOBJ		Optional. Single decision Pods object.
	 * @param	int|null	$uID		Optional. A user ID. If set only tasks assigned to a user will be returned.
	 * @param 	int			$limit		Optional. Number of tasks to return per page. Default is 5.
	 * @param 	bool		$completed	Optional. If true (the default) completed task will be included.
	 * @param	int|array|bool	$oID	Optional. Organization ID or array of IDs. Show groups of certain organization(s) only. If used, $dID and $dObj are ignored.
	 *
	 * @return 	string				The view.
	 *
	 * @since	0.0.1
	 */
	function all_tasks( $dID, $dObj = null, $uID = false, $limit = 5, $completed = true, $oID = false ) {
		$view = '';
		if ( $uID && $oID === false ) {
			$tasks =  holotree_task_class()->users_tasks( $uID, $dID, $limit, 'array', $completed );
		}
		else {
			$params = null;
			if ( $oID === false ) {
				$obj = holotree_decision( $dID, true, false, $dObj );
				unset( $dObj );

			}
			else {
					if ( is_array( $oID ) ) {
						$where = 'organization.ID = "IN( ' . implode( ',', $oID ) . ')" ';
					}
					else {
						$where = 'organization.ID = "' . $oID . '" ';

					}

			}

			if ( $uID ) {
				$where .= ' AND assigned_user.ID = "'.$uID.'"';
			}

			if ( isset( $where ) ) {
				$params = array( 'where' => $where );
			}

			$obj = holotree_pods_object( 'task', null, HOUR_IN_SECONDS, true, $params  );

		}

		if ( !isset( $tasks ) ) {
			if ( !is_object( $obj )  ) {
				holotree_error( 'No object!',__METHOD__ );
			}
			else {
				$tasks = $obj->field( 'tasks' );
			}

		}

		if ( is_array( $tasks ) ) {
			if ( $uID ) {
				$view = '<h5>Total Tasks Assigned: ' . holotree_task_class()->number_assigned_tasked( $uID ) . '<h5>';
			}
			foreach ( $tasks as $task ) {
				$view .= $this->task( $task[ 'term_id' ], true, null );
			}

			//@todo this
			$view .= '<div class="button">See All Assigned Tasks</div>';

			return $view;
		}
		else {
			if ( $uID ) {
				return '<h5>'.__( 'No Assigned Tasks', 'holotree' ).'</h5>';
			}
			else {
				return '<h5>'.__( 'This decision has no tasks.', 'holotree' ).'</h5>';
			}
		}

	}

	/**
	 * Creates view (preview or complete) for a task.
	 *
	 * @param 	array     		$task		Array of task fields returned from the tasks field in decisions cpt.
	 * @param 	bool 	  		$preview	Optional. If true only preview view outputted. Default is false, which outputs ocmplete view.
	 * @param 	array|null 		$blockers	Optional. Array returned from tasks CT field 'blockers'.
	 * @param 	array|null 		$blocking	Optional. Array returned from tasks CT field 'blocking'.
	 *
	 * @return 	string						The view or view prview.
	 *
	 * @since 	0.0.1
	 */
	function task( $id, $preview = false, $obj = null ) {
		$obj = holotree_task( $id, false, false, $obj );
		if ( (int) $obj->id() != (int) $id ) {
			holotree_error( __LINE__, print_c3 (array( $id, $obj->id() )) );
		}
		holotree_task_class()->status_decider( $id, $obj );
		if ( $preview && $id !== false ) {
			$part = 'task_preview';
		}
		else {
			$part = 'task';
		}

		return $this->ui()->elements()->template( $part, $obj, true );

	}

	/**
	 * View for blockers or blocking
	 *
	 * Used by $this->task for blockers & blocking view.
	 *
	 * @param	array   $block_array	Field array for blockers or blocking fields in task CT
	 * @param 	bool 	$li				Whether to add li tags
	 *
	 * @return 	string					The view.
	 *
	 * @since 	0.0.1
	 */
	function block( $block_array, $li = true ) {
		if ( is_array( $block_array ) ) {
			$out = '';
			foreach ( $block_array as $block ) {
				if ( $li ) {
					$out .= '<li>';
				}
				$out .= $this->ui()->elements()->task_link( intval( $block[ 'term_id' ] ),  $block['name'] );

				if ( HT_DEV_MODE ) {
					$out .= '<span style="float:right">' . $block[ 'term_id' ] . '</span>';
				}
				if ( $li ) {
					$out .= '</li>';
				}

			}

			return $out;
		}
	}

	//@TODO
	function task_docs( $tID, $obj = null ) {
		$obj = holotree_dms_class()->null_obj( 'task', $tID, $obj );
		//@TODO
		return ':)';
	}

	function action_buttons( $what, $id, $obj = null ) {
		$obj = holotree_decision( $id, true, false, $obj );

		$is_change = false;
		if ( $obj->field( 'decision_type')  === 'change' ) {
			$is_change = true;
		}

		$classes = 'view-action button';

		if ( $what == 'open-decision' || $what === 'blocked-decision'  ) {
			$respond_label = 'Respond';
		}
		else {
			$respond_label = 'Comment';
		}
		$view = '<a href=" ' . get_permalink( $id ) . '" class="action-view '.$classes.'" >View</a>';
		$accept = array(
			'label' => 'Accept',
			'value'	=> 'accept',
		);
		if ( $is_change ) {
			$accept = array(
				'label' => 'Accept Proposed Modification',
				'value'	=> 'accept-change',
			);
		}
		$change = array(
			'label'	=> 'Propose Change',
			'value'	=> 'propose-change',
		);
		$respond = array(
			'label'	=> $respond_label,
			'value'	=> 'respond',
		);
		$block = array(
			'label'	=> 'Block',
			'value' => 'block',
		);
		$unblock = array(
			'label'	=> 'Unblock',
			'value'	=> 'unblock',
		);


		if ( $what == 'open-decision' ) {
			$options = array( $accept, $change, $respond, $block );
		}
		elseif ( $what === 'blocked-decision' ) {
			$options = array( $accept, $change, $respond );
			$decision = holotree_decision_class();
			if ( $decision->is_blocking( $id ) ) {
				$options[] = $unblock;
			}
			else {
				$options[] = $block;
			}
		}
		elseif ( $what === 'task' ) {
			$options = array();
		}
		else {
			$options = array(  $respond );
		}

		if ( !is_singular() ) {
			$out = $view;
		}
		else {
			$out = '';
		}

		if ( is_array( $options )   ) {
			
			$form = '<form action="' . $this->ui()->elements()->current_page_url() . '" method="get" id="dms-actions-form">';
			$form .= '<select name="dms_action">';
			foreach ( $options as $option ) {
				$form .= '<option value="' . $option[ 'value' ] . '">' . $option[ 'label' ] . '</option>';
			}
			$form .= '</select>';

			$form .= '<input type="hidden" name="dms_id" value="' . $id . '">';

			$form .= '<input class="'.$change['value'].'" type="submit" />';
			$form .= '</form>';

			$out .= '&nbsp;&nbsp;';
			$out .= $form;
		}

		$action_buttons = $out;
		return $action_buttons;
	}

	/**
	 * The task actions form
	 * 
	 * @param      $tID
	 * @param null $obj
	 *
	 * @return mixed
	 */
	function task_actions( $tID, $obj = null ) {
		$elements = $this->ui()->elements();
		$id = $tID;
		$obj = holotree_task( $id, true, false, $obj );

		$fields = array(
			'blockers'	=> array( 'label' => __( 'Add tasks that must be completed before this task is completed.', 'holotree' ),
			),
			'blocking'	=> array( 'label' => __( 'Add tasks that can only be completed after this task is completed.', 'holotree' ) ),
		);

		//only allow marking complete if isn't blocked.
		if ( ! holotree_task_class()->is_blocked( $id, $obj ) ) {
			$fields[] = 'completed';
		}

		/**
		 * Change which fields are outputted for task actions
		 *
		 * @params array $fields
		 *
		 * @since 0.0.1
		 */
		$fields = apply_filters( 'ht_dms_task_action_fields', $fields );

		$url = $elements->current_page_url();
		$url = $elements->action_append( $url, 'task-updated', $tID );

		$form = $obj->form( $fields, 'Update', $url );

		$modal_id = "modify-{$tID}";

		return $elements->modal( $form, $modal_id, __( 'Task Actions', 'holotree' ) );

	}

	function _task_actions( $tID, $obj = null ) {
		$obj = holotree_task( $tID, true, false, $obj );
		$t = holotree_task_class();
		$options = $form = false;

		$done = array(
			'label' => __( 'Mark Complete', 'holotree' ),
			'value' => 'completed'
		);
		$add_block = array(
			'label'	=> __( 'Add Blocking Task', 'holotree' ),
			'value' => 'add-blocking'
		);
		$add_blocker = array(
			'label'	=> __( 'Add Blocker Task', 'holotree' ),
			'value' => 'add-blocker'
		);

		if ( $t->possible( $tID, $obj ) ) {
			$options = array( $done, $add_block, $add_blocker );
		}
		elseif ( $options === false ) {
			$options = array(  $add_block, $add_blocker );
		}

		if ( is_array( $options ) ) {
			$dID = $obj->field( 'decision.ID' );
			//new object. All tasks
			$obj = holotree_pods_object( 'task' );
			$obj = $obj->find( array ( 'where' => 'decision.ID = "' . $dID . '"' ) );
			$tasks = FALSE;
			if ( $obj->total > 0 ) {
				while ( $obj->fetch() ) {
					$tasks[ $obj->field( 'term_id' ) ] = $obj->field( 'name' );
				}
			}

			$form = '<form action="' . $this->ui()->elements()->current_page_url() . '" method="get" id="dms-task-action-form">';
			$form .= '<select name="dms_action">';
			foreach ( $options as $option ) {
				$form .= '<option value="' . $option[ 'value' ] . '">' . $option[ 'label' ] . '</option>';
			}
			$form .= '</select>';

			if ( is_array( $tasks ) ) {
				$form .= '<select name="dms_action">';
				foreach ( $task as $k => $v ) {
					$form .= '<option value="' . $k . '">' . $v . '</option>';
				}
			}
			$form .= '</select>';
			$form .= '<input type="hidden" name="dms_id" value="' . $tID . '">';
			$form .= '<input class="" type="submit" />';
			$form .= '</form>';
		}


		return $form;


	}

	/**
	 * All of the group psuedo-widgets.
	 *
	 * @param 	int		$gID	ID of group.
	 *
	 * @return 	string	$out	Content
	 *
	 * @since	0.0.1
	 */
	function group_sidebar_widgets( $gID ) {
		$out = do_action( 'ht_dms_before_group_widgets' );
		$out .= do_action( 'ht_dms_before_widgets' );

		if ( HT_DEV_MODE ) {
			$out .= "gID = ". $gID;
		}
		if ( ! holotree_group_class()->is_member( $gID ) ) {
			$out .= $this->ui()->group_widget()->join_group_widget( $gID );
		}

		$out .= $this->ui()->group_widget()->group_members_widget( $gID );
		if ( holotree_dms_class()->is_facilitator( null, $gID, null ) ) {
			$out .= $this->ui()->group_widget()->group_approve_widget( $gID );
		}

		$out .= do_action( 'ht_dms_after_widgets' );
		$out .= do_action( 'ht_dms_after_group_widgets' );

		$output = $out;

		/**
		 * Set the content of the group sidebar
		 *
		 * @param	$string	$output The content
		 *
		 * @since	0.0.1
		 */
		$output = apply_filters( 'ht_dms_display_group_sidebar', $output );

		return $output;

	}


	function default_sidebar_widgets( $uID = null ) {
		$out = do_action( 'ht_dms_before_widgets' );
		$out .= $this->ui()->my_stuff()->view( $uID );
		$out .= do_action( 'ht_dms_after_widgets' );

		$output = $out;

		/**
		 * Set the content of the default dms sidebar
		 *
		 * @param	$string	$output The content
		 *
		 * @since	0.0.1
		 */
		$output = apply_filters( 'ht_dms_sidebar', $output );

		return $output;
	}

	/**
	 * Loop through group items.
	 *
	 * Note: if using pre-built object, all other params are not used.
	 *
	 * @TODO Test pagination.
	 *
	 * @param 	null|obj		$obj	Optional. Use to supply a pre-built object
	 * @param 	int				$limit	Optional. Items per page. Defaults to 5.
	 * @param 	bool			$mine	Optional. If true only groups that current user is a member of will be shown. Defaults to false.
	 * @param 	bool 			$public Optional. If true, only publicly listed groups will be shown. Defaults to true.
	 * @param	int|array|bool	$oID	Optional. Organization ID or array of IDs. Show groups of certain organization(s) only.
	 *
	 * @return 	string					The loop output
	 *
	 * @since 0.0.1
	 */
	function group_loop( $obj = null, $limit = 5, $mine = false, $public = true, $oID = false, $obj = null ) {
		if ( $mine ) {
			$obj = holotree_group_class()->users_groups_obj( get_current_user_id(), $obj, $limit, $oID );
		}
		else {
			$params = array( 'limit' => $limit );
			if ( $oID !== false ) {
				if ( is_array( $oID ) ) {
					$where = 'organization.ID = "IN( ' . implode( ',', $oID ) . ')" ';
				}
				else {
					$where = 'organization.ID = "' . $oID . '" ';

				}

			}

			if ( $public === true ) {
				$visible_where = 'd.group_visibility = "public"';
				if ( $oID !== false ) {
					$where .= ' AND '.$visible_where;
				}
				else {
					$where = $visible_where;
				}
			}

			if ( isset( $where ) ) {
				$params[ 'where' ] = $where;
			}

			//@TODO use supplied $obj once group null object is refactored.
			$obj = holotree_group_class()->pods_object()->find( $params );

		}

		if ( !is_object( $obj ) ) {
			holotree_error( 'No Object in: ', __METHOD__ );
		}

		else {
			if ( $obj->total() > 0 ) {
				$out  = '';


				while ( $obj->fetch() ) {
					$out .= $this->group( $obj );
				}

				$out .= $obj->pagination();

				return $out;
				
			}
			else {
				return _( 'Not a member of any groups', 'holotree' );
			}

		}


	}

	/**
	 * Group view
	 *
	 * Goes inside a Pods while loop of groups.
	 *
	 * @TODO Preview vs full view (needs decisions?)
	 *
	 * @param 	obj		$obj	Pods object in Groups CPT
	 *
	 * @return string	$Out	Output.
	 *
	 * @since 0.0.1
	 */
	function group( $obj ) {
		$id = (int) $obj->ID();
		$out = '<div class="group-view" id="group-view-'.$id.'">';
		$out .= '<h3>'.holotree_link( $id, $type = 'post', $obj->field( 'post_title' ) ).'</h3>';
		if ( HT_DEV_MODE ) {
			$out .= '<span style="float:right">'.$id.'</span>';
		}
		$out .= $obj->field( 'group_description' );
		$title = 'View '.$obj->field( 'post_title' );
		$out .= '<br />'.holotree_link( $id, 'post', 'View', $title, true );
		if ( holotree_group_class()->is_pending( get_current_user_id(), $id, $obj ) ) {
			$out .= $this->ui()->elements()->alert( __( 'Your Membership To This Group Is Pending', 'holotree' ) );
		}
		elseif ( ! holotree_group_class()->is_member( $id, get_current_user_id(), $obj ) ) {

			$title = 'Click to Join '.$obj->field( 'post_title' );
			$append = array( 'action' => 'join-group', 'ID' => $id );
			$out .= ' '.holotree_link( site_url(), 'url', 'Join', $title,  true, false, false, $append );
		}

		$out .= '</div>';

		return $out;
	}


	function message_template( $preview = true ) {

		if ( $preview ) {
			$template = file_get_contents( trailingslashit( HT_DMS_VIEW_DIR ) . 'partials/message_preview.html' );
		}
		else {
			$template = file_get_contents( trailingslashit( HT_DMS_VIEW_DIR ) . 'partials/message.html' );
		}

		return $template;
	}

	function message_all( $uID, $status = false ) {


	}

	function notifications( ) {
		return file_get_contents( trailingslashit( HT_DMS_VIEW_DIR ).'messages.php' );

	}
	function _notifications( $uID ) {


		$messages = $this->notification_loop( $uID, true, false, 'pm' );
		if ( $messages !== '') {
			$content = $messages;
		}
		else {
			$content = 'No Messages:(';
		}

		$tabs[] = array (
			'label' => 'Messages',
			'content' => $content,
		);

		$messages = $this->notification_loop( $uID, true, false, 'notification' );
		if ( $messages !== '') {
			$content = $messages;
		}
		else {
			$content = 'No Messages:(';
		}

		$tabs[] = array (
			'label' => 'Notifications',
			'content' => $content,
		);

		$tabs[] = array (
			'label' => 'Create',
			'content' => $this->create_notification(),
		);

		return $this->ui()->elements()->accordion( $tabs );
	}

	function notification_loop( $uID = null, $preview = false, $status = false, $type = false, $single = false, $obj = null ) {
		if ( is_null( $obj ) ) {
			if ( $single === FALSE ) {
				$obj = holotree_notification_class()->get_all( $uID, $status, $type, NULL, FALSE );
			}
			else {
				$obj = holotree_notification_class()->notification( $single );
			}
		}

		if ( ! $single ) {
			$single = null;
		}

		$out = '';
		$out .= $this->notification( $obj, $single, $preview );

		return $out;

	}

	function notification( $obj, $single, $preview = true, $actions = true ) {
		$obj = holotree_notification( false, true, false, $obj );
		$out = '';
		if ( $obj->total() > 0 ) {
			while ( $obj->fetch( $single ) )  {
				$out .= \Pods_Templates::do_template( $this->message_template( $preview ), $obj );
				if ( $actions ) {
					$out .= $this->notification_actions( $obj, $preview );
				}
			}
		}

		return $out;
	}

	/**
	 * Actions form for notifications
	 *
	 * @param $obj
	 * @param $preview
	 *
	 * @return string
	 *
	 * @since 0.0.1
	 */
	function notification_actions( $obj, $preview ) {
		$cURL = $this->ui()->elements()->current_page_url();
		$id = $obj->id();
		$out = '<div class="notification-actions">';
		if ( $preview && 3 ==76 ) {
			$out .=  $this->ui()->elements()->modal( $this->notification( $obj, $id, $preview ), rand( 1, 666 ), 'View' );
		}


		$link = holotree_action_append( $cURL, 'mark-notification', $id );
		$text = $title = 'Mark '.$obj->display( 'status' );
		$out .= holotree_link( $link, '', $text, $title, true, 'notification-action' );

		$link = holotree_action_append( $cURL, 'archive-notification', $id );
		$text = $title = 'Archive';
		$out .= holotree_link( $link, '', $text , $title, true, 'notification-action' );

		return $out;
	}


	//@todo figue out why this is here and/or delete
	function create_notification() {
		return holotree_notification_class()->create();
	}


	/**
	 * Array of fields that decisions output
	 *
	 * @return array
	 *
	 * @sicne 0.0.1
	 */
	function decision_output_fields() {
		$fields = array(
			'id',
			'post_title',
			'decision_status',
			'decision_description',
			'change_to',
		);
		return $fields;
	}

	/**
	 * This is the menu that goes in the left slide-in
	 */
	function menu() {
		$items = array(
			'Home' => site_url(),
			'My Groups' => null,
			'Preferences' => null,
			'Messages' => null,
			'Logout' => wp_logout_url(),
		);
		/**
		 * Override the left menu items
		 *
		 * @param array $item The items as 'label' => 'link'
		 *
		 * @since 0.0.1
		 */
		$items = apply_filters( 'ht_dms_menu_items', $items );

		$out = '<ul>';
		foreach( $items as $label => $link ) {
			if ( is_null( $link ) || !is_string( $link ) ) {
				$link = '#';
			}
			$out .= '<li><a href="'.$link.'">'.$label.'</a></li>';
		}
		$out .= '</ul>';

		return $out;
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
