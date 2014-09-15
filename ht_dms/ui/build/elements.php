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


class elements {

	/**
	 * Creates tabs for decisions by status
	 * @param 	array|null	$statuses
	 * @param 	int			$gID
	 * @param 	obj|null 	$dObj		Optional. A full decision object.
	 *
	 * @return	array					Tabs array to pass to the tab maker.
	 */
	function decisions_by_status_tabs( $statuses = null, $gID, $dObj= null  ) {
		if ( is_null( $dObj ) || !is_object( $dObj ) ) {
			$dObj = pods( HT_DMS_DECISION_CPT_NAME );
		}
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

				$view_loaders = holotree_dms_ui()->view_loaders();
				$view =  holotree_dms_ui()->models()->path( 'decision', true  );
				$d_s = $view_loaders->magic_template( $view, $dObj );
				$decisions[ $status ] = $d_s;


			} //endif have pods



		}

		$tabs = array( );

		if ( isset( $decisions ) && is_array( $decisions ) ) {
			$content = '';
			foreach ( $statuses as $status  ) {

				if ( isset( $decisions[ $status ] ) ) {
					$content = '';
					$content .= '<div id="' . $status . '-decisions-list" class="decisions-list">';
					$heading = $status . ' Decisions';
					$content .= '<h3>' . $heading . '</h3>';
					$content .= $decisions[ $status ];
					$content .= '</div>';

					$tabs[ ] = array (
						'label'   => __( $status . ' Decisions', 'holotree' ),
						'content' => $content,
					);

					unset( $content );
				}

				}


		}

		if ( isset( $tabs ) && is_array( $tabs ) ) {

			return $tabs;

		}

	}

	/**
	 * Decisions action form based on Caldera forms
	 *
	 * @param string $caldera_id ID of form
	 *
	 * @return string
	 *
	 * @since 0.0.3
	 */
	function decision_actions( $caldera_id = false ) {
		if ( ! $caldera_id ) {

			$caldera_id = \ht_dms\helper\caldera_actions::$decision_actions_form_id;

		}
		$after = '<div id="dms-action-result" style="display:none;"></div>';
		return ht_dms_caldera_loader( $caldera_id, '', $after );

	}

	function action_buttons( $what, $id, $obj = null ) {
		if ( class_exists( 'Caldera_Forms' ) ) {

			return $this->decision_actions();

		}

		$obj = holotree_decision( $id, $obj );

		$is_change = holotree_decision_class()->is_proposed_modification( $id, $obj );
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
			$options = array( $accept, $change, $block );
		}
		elseif ( $what === 'blocked-decision' ) {
			$options = array( $accept, $change, $respond );
			$options = array( $accept, $change );
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
			$options = array();
		}

		if ( !is_singular() ) {
			$out = $view;
		}
		else {
			$out = '';
		}

		if ( is_array( $options )   ) {



			$form = sprintf( '<form action="%1s" method="get" id="dms-actions-form">', ht_dms_home() );
			$form .= '<select id="dms_action" name="dms_action">';
			foreach ( $options as $option ) {
				$form .= '<option value="' . $option[ 'value' ] . '">' . $option[ 'label' ] . '</option>';
			}
			$form .= '</select>';

			$form .= '<input type="hidden" name="dms_id" value="' . $id . '">';

			$form .= '<input class="'.$change['value'].'" type="submit" />';
			$form .= '</form>';
			$form .= '<div id="dms-action-result"></div>';
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
		$obj = holotree_task( $id, $obj );

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

	/**
	 * This is the menu that goes in the left slide-in
	 */
	function menu() {
		$items = array(
			'Home' => ht_dms_home(),
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
	function block( $block_array, $li = true, $before = null, $after = null ) {
		if ( is_array( $block_array ) ) {

			$out = '';
			if ( !is_null( $before ) ) {
				$out .= $before;
			}

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

			if ( !is_null( $after ) ) {
				$out .= $after;
			}

			if ( !empty( $out ) ) {
				return $out;
			}
		}
	}

	function group_membership( $gID, $obj = null ) {
		$uID = get_current_user_id();
		$obj = holotree_group( $gID, $obj  );
		$g = holotree_group_class();
		$out = false;
		$membership = $this->ui()->membership();
		if ( $g->is_member( $gID, $uID, $obj ) ) {
			$out[] = $membership->leave();

		}
		else {
			if ( $g->is_pending( $uID, $gID, $obj ) ) {
				$out[] = __( 'Your Membership in this group is pending approval', 'ht_dms' );
			}
			else {
				$out[ ] = $membership->join( $gID, $obj );
			}
		}



		if ( $g->is_facilitator( $gID, $uID, $obj ) ) {
			if ( is_array(  $g->get_pending( $gID, $obj ) ) ) {
				$out[ ] = $membership->pending();
			}
		}

		if ( $g->is_public( $gID, $obj ) || $g->is_member( $gID, $obj ) || $g->is_facilitator( $gID, $obj ) ) {
			$out[] = $membership->view( $gID );
		}

		if ( is_array( $out ) ) {

			return sprintf( '<div id="group-membership">%1s</div>', implode( $out ) );

		}

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
		if ( holotree_common_class()->is_facilitator( null, $gID, null ) ) {
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

	function ajax_pagination_buttons( $obj, $view, $page  ) {
		$total_pages = $obj->total_found() / $obj->total();
		$total_pages = ceil( $total_pages );

		$previous = false;
		if ( $page > 1 ) {
			$previous_page = $page-1;
			$attr = "page=\"{$previous_page}\"";
			$previous = sprintf( '<a href="#" id="previous-%0s" class="pagination-previous button" %2s>%3s</a>', $view, $attr, __( 'Previous', 'holotree' ) );
		}

		$next_page = $page+1;
		if ( $next_page < $total_pages ) {
			$attr = "page=\"{$next_page}\"";
			$next = sprintf( '<a href="#" id="next-%0s" class="pagination-next button" %2s>%3s</a>', $view, $attr,  __( 'Next', 'holotree' ) );
		}
		else {
			$next = false;
		}

		$buttons = array( $previous, $next );

		$out = sprintf( '<div class="pagination %1s-pagination">%2s</div>', HT_DMS_PREFIX, implode( $buttons ) );

		$out .= $this->pagination_inline_js( $previous, $view );

		return $out;

	}

	private function pagination_inline_js( $previous, $view )  {
		if ( $previous ) {
			$script[] = "jQuery( '#previous-{$view}' ).click( function() {
			 	paginate( '#{$view}', jQuery( '#previous-{$view}' ).attr( 'page' ) );
			 });";
		}
		$script[]  = "jQuery( '#next-{$view}' ).click( function() {
				paginate( '#{$view}', jQuery( '#next-{$view}' ).attr( 'page' ) );
			});";

		$script = sprintf( '<script type="text/javascript">%2s</script>', implode( $script ) );

		return $script;

	}

	/**
	 * Get markup for an icon
	 *
	 * @param string $icon
	 *
	 * @return string
	 *
	 * @since 0.0.3
	 */
	function icon( $icon ) {
		$icons = array(
			'organization' => '<i class="fa fa-university"></i>',
			'group'	=> '<i class="fa fa-users"></i>',
			'decision' => '<i class="fa fa-check"></i>',
			'task'	=> '<i class="fa fa-tag"></i>',
			'notification' => '<i class="fa fa-inbox"></i>',
			'preferences' => '<i class="fa fa-cogs"></i>',
			'close' => '<i class="fa fa-times"></i>',
			'doc' => '<i class="fa fa-file"></i>',
			'star' => '<i class="fa fa-star"></i>',
			'trash' => '<i class="fa fa-trash"></i>',

		);

		/**
		 * Change one or more of the icons
		 *
		 * @param array $icons The icons 'icon' => 'markup'
		 * @param string $icon Current icon being outputted.
		 *
		 * @since 0.0.3
		 */
		$icons = apply_filters( 'ht_dms_icons', $icons, $icon );

		return pods_v( $icon, $icons );

	}


	/**
	 * Get instance of UI class
	 *
	 * @return 	\ht_dms\ui\ui
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
