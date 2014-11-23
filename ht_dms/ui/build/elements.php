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
	 *
	 * @param 	array|null	$statuses
	 * @param 	int			$gID
	 *
	 * @return	array					Tabs array to pass to the tab maker.
	 */
	function decisions_by_status_tabs( $statuses = null, $gID, $dObj= null  ) {
		if ( is_null( $statuses )  ) {
			$statuses = array ( 'New', 'Blocked', 'Passed' );
		}

		if ( is_string( $statuses ) ) {
			$statuses = array( $statuses );
		}

		$tabs = array();
		if ( is_array( $statuses ) && ! empty( $statuses ) ) {
			foreach ( $statuses as $status ) {
				$content = $this->decisions_by_status_tab_content( $status, $gID  );

				$tabs[] = array (
					'label'   => ht_dms_add_icon( $status . __( ' Decisions', 'ht_dms' ), strtolower( $status ) ),
					'content' => $content,
				);
			}
		}

		return $tabs;

	}

	/**
	 * Get tab content for a decision by status.
	 *
	 * @since 0.2.0
	 *
	 * @param string $status
	 * @param int $gID
	 *
	 * @return string
	 */
	private function decisions_by_status_tab_content( $status, $gID ) {
		$status = strtolower( $status );
		$args = array(
			'limit'     => 5,
			'status'    => $status,
			'gID'       => $gID,
			'page'      => 1

		);

		$html_id = "decision-{$status}-container";
		$content = ht_dms_ui()->view_loaders()->handlebars_container( $html_id );

		return ht_dms_paginated_view_container( 'decision', $args, $content  );

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

			$caldera_id = ht_dms_ui()->caldera_actions()->decision_actions_form_id;

		}
		$before = '<div id="dms-action-result" style="display:none;"></div>';
		return ht_dms_caldera_loader( $caldera_id, $before, '' );

	}

	function action_buttons( $what, $id, $obj = null ) {
		if ( class_exists( 'Caldera_Forms' ) ) {

			return $this->decision_actions();

		}

		$obj = ht_dms_decision( $id, $obj );

		$is_change = ht_dms_decision_class()->is_proposed_modification( $id, $obj );
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
			$decision = ht_dms_decision_class();
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
		$obj = ht_dms_task( $id, $obj );

		$fields = array(
			'blockers'	=> array( 'label' => __( 'Add tasks that must be completed before this task is completed.', 'ht_dms' ),
			),
			'blocking'	=> array( 'label' => __( 'Add tasks that can only be completed after this task is completed.', 'ht_dms' ) ),
		);

		//only allow marking complete if isn't blocked.
		if ( ! ht_dms_task_class()->is_blocked( $id, $obj ) ) {
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

		return $elements->modal( $form, $modal_id, __( 'Task Actions', 'ht_dms' ) );

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


		$link = ht_dms_action_append( $cURL, 'mark-notification', $id );
		$text = $title = 'Mark '.$obj->display( 'status' );
		$out .= ht_dms_link( $link, '', $text, $title, true, 'notification-action' );

		$link = ht_dms_action_append( $cURL, 'archive-notification', $id );
		$text = $title = 'Archive';
		$out .= ht_dms_link( $link, '', $text , $title, true, 'notification-action' );

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
		$obj = ht_dms_group( $gID, $obj  );
		$g = ht_dms_group_class();
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

		if ( $g->is_public( $gID, $obj ) || $g->is_member( $gID, $uID, $obj ) || $g->is_facilitator( $gID, $uID, $obj ) ) {
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

		if ( ! ht_dms_group_class()->is_member( $gID ) ) {
			$out .= $this->ui()->group_widget()->join_group_widget( $gID );
		}

		$out .= $this->ui()->group_widget()->group_members_widget( $gID );
		if ( ht_dms_common_class()->is_facilitator( null, $gID, null ) ) {
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

	function ajax_pagination_buttons( $obj, $view, $page, $type = null  ) {
		if ( is_object( $obj )&& is_pod( $obj ) ) {
			$total = $obj->total();
			$total_found = $obj->total_found();

		}
		elseif( ! is_null( $type ) && is_string( $obj ) && false != ( $decoded_json = json_decode( $obj )  ) ) {
			$totals = models::get_total( $type );
			$total = pods_v( 'total', $totals );
			$total_found = pods_v( 'total_found', $totals );
		}
		else {
			ht_dms_error();
		}

		if ( is_null( $page ) ) {
			$page = 1;
		}

		$total_pages = (int) $total_found / (int) $total;
		$total_pages = ceil( $total_pages );
		$page = (int) $page;

		$previous = false;
		if ( $page > 1 ) {
			$previous_page = $page-1;
			$attr = "page=\"{$previous_page}\"";
			$previous = sprintf( '<a href="#" id="previous-%0s" class="pagination-previous button" %2s>%3s</a>', esc_attr( $view ),  $attr, __( 'Previous', 'ht_dms' ) );
		}

		$next_page = $page+1;
		if ( $next_page >= $total_pages && $total_pages != $page ) {
			$attr = "page=\"{$next_page}\"";
			$next = sprintf( '<a href="#" id="next-%0s" class="pagination-next button" %2s>%3s</a>', esc_attr( $view ), $attr ,  __( 'Next', 'ht_dms' ) );
		}
		else {
			$next = false;
		}

		$buttons = array( $previous, $next );

		$out = sprintf( '<div class="pagination %1s-pagination" id="%2s-pagination">%3s</div>', HT_DMS_PREFIX, $view, implode( $buttons ) );

		$out .= $this->pagination_inline_js( $previous, $view );

		return $out;

	}

	private function pagination_inline_js( $previous, $view )  {
		if ( $previous ) {
			$script[] = "jQuery( '#previous-{$view}' ).click( function() {
			 	ht_dms_paginate( '#{$view}', jQuery( '#previous-{$view}' ).attr( 'page' ) );
			 });";
		}
		$script[]  = "jQuery( '#next-{$view}' ).click( function() {
				ht_dms_paginate( '#{$view}', jQuery( '#next-{$view}' ).attr( 'page' ) );
			});";

		$script = implode( $script );
		//$script = esc_js( $script );
		$script = sprintf( '<script type="text/javascript">%1s</script>', $script  );

		return $script;

	}

	/**
	 * Get markup for an icon
	 *
	 * @param string|array 	$icon	Icon to get. Ignored if $all == true. Can also be an array of a group of specific icons to get.
	 * @param string $extra_class Optional. Additional class to add to icon.
	 * @param bool 		$all  	Optional. If true returns array of all icons.
	 *
	 * @return string|array The markup for the icon, or an array of all or some icons.
	 *
	 * @since 0.0.3
	 */
	function icon( $icon, $extra_class = false, $all = false  ) {

		$icons = array(
			'organization' => '<i class="fa fa-university"></i>',
			'group'	=> '<i class="fa fa-users"></i>',
			'decision' => '<i class="fa fa-check"></i>',
			'task'	=> '<i class="fa fa-tag"></i>',
			'notifications' => '<i class="fa fa-inbox"></i>',
			'preferences' => '<i class="fa fa-cogs"></i>',
			'close' => '<i class="fa fa-times"></i>',
			'doc' => '<i class="fa fa-file"></i>',
			'docs' => '<i class="fa fa-file"></i>',
			'star' => '<i class="fa fa-star"></i>',
			'trash' => '<i class="fa fa-trash"></i>',
			'home' => '<i class="fa fa-home"></i>',
			'logout' => '<i class="fa fa-sign-out"></i>',
			'new' => '<i class="fa fa-plus"></i>',
			'discussion' => '<i class="fa fa-comments"></i>',
			'modification' => '<i class="fa fa-code-fork"></i>',
			'details' => '<i class="fa fa-info"></i>',
			'blocked' => '<i class="fa fa-stop"></i>',
			'completed' => '<i class="fa fa-birthday-cake"></i>',
			'members' => '<i class="fa fa-child"></i>',
			'profile' => '<i class="fa fa-user"></i>',
			'edit' => '<i class="fa fa-pencil-square-o"></i>',
			'public' => '<i class="fa fa-tree"></i>',
			'spinner' => '<i class="fa fa-spinner fa-spin"></i>',
			'silence' => '<i class="fa fa-circle-o"></i>',
			'accepted' => '<i class="fa fa-check"></i>',
			'notification' => '<i class="fa fa-envelope-o"></i>',
            'user'  => '<i class="fa fa-user"></i>',
			'consensus' => '<i class="fa fa-fire"></i>',

		);

		$icons[ 'passed' ] = $icons[ 'accepted' ];

		/**
		 * Change one or more of the icons
		 *
		 * @param array $icons The icons 'icon' => 'markup'
		 * @param string $icon Current icon being outputted.
		 *
		 * @since 0.0.3
		 */
		$icons = apply_filters( 'ht_dms_icons', $icons, $icon );

		if ( $all ) {
			return $icons;
		}

		$false_return = '';
		if ( HT_DEV_MODE ) {
			$false_return = '[]';
		}

		if ( is_string( $icon ) ) {
			$icon = pods_v( $icon, $icons, $false_return, true );

			if ( $extra_class && $icon !== $false_return ) {
				$replace = sprintf( '%1s %2s ', 'class="', $extra_class );
				$icon    = str_replace( 'class="', $replace, $icon );
			}

			return $icon;

		}

		if ( is_array( $icon ) ) {
			foreach( $icon as $i ) {
				$return_array[ $i ] = pods_v( $i, $icons );
			}

			return $return_array;

		}

		return $false_return;

	}

	/**
	 * Substitute {{icon}} markup in partials for the actual icons
	 *
	 * @param $string
	 *
	 * @return mixed
	 */
	function icon_substitution( $string ) {

		foreach( $this->icon( null, '', true ) as $icon => $markup ) {
			$search = '{{' . $icon . '}}';
			$string = str_replace( $search, $markup, $string );
		}

		return $string;
	}

	function visualize_hierarchy_icon( $type ) {
		if ( in_array( $type, ht_dms_content_types() ) ) {

			$text = ht_dms_prefix_remover( $type );

			$icon = ht_dms_add_icon( '', $text );

			$out = sprintf(
				'
					<div class="visualize-hierarchy" id="visualize-hierarchy-%1s">
						<div class="visualize-hierarchy-icon">%2s</div>
						<div class="visualize-hierarchy-text">%3s</div>
					</div>
				',
				$text, $icon, $text
			);

			return $out;
		}


	}


	/**
	 * Individual member details.
	 *
	 * Designed to be passed to be used in output_views()->members_details_view()
	 *
	 * @param null|int $uID Optional. User Id or null for current user.
	 * @param int  $avatar_size Optional. Avatar size. Default is 256
	 *
	 * @return mixed|void
	 */
	function member_details( $uID = null, $avatar_size = 96 ) {
		$uID = ht_dms_null_user( $uID );

		$details[] = array (
			'name'   => ht_dms_display_name( $uID ),
			'avatar' => get_avatar( $uID, $avatar_size, ht_dms_fallback_avatar() )
		);

		return apply_filters( 'ht_dms_member_details', $details, $uID, $details );



	}

	/**
	 * Returns an icon for consensus code
	 *
	 * @param int $status_code 0|1|2
	 *
	 * @return mixed
	 *
	 * @since 0.0.3
	 */
	function consensus_icons( $status_code ) {
		$class = 'fa-2x';
		$icons = array(
			0 => $this->icon( 'silence', $class ),
			1 => $this->icon( 'accepted', $class ),
			2 => $this->icon( 'blocked', $class ),
		);

		$icons = apply_filters( 'ht_dms_consensus_icons', $icons );

		return pods_v( $status_code, $icons, false, false );

	}

	/**
	 * Header for consensus tabs (or other use)
	 *
	 * Shows icon-status-count
	 *
	 * @param int $status_code 0|1|2
	 * @param int $count number of users with that status
	 *
	 * @return string
	 */
	function consensus_tab_header( $status_code, $count ) {
		$status = ht_dms_consensus_status_readable( $status_code, false, true );
		$icon = $this->consensus_icons( $status_code );
		if ( ! ht_dms_integer( $count ) ) {
			$count = 0;
		}

		if ( $status ) {

			return sprintf( '<div class="consensus-tab-label">%1s<span class="status">%2s</span><span="count">%3s</span></div>', $icon, $status, $count );

		}

	}




	/**
	 * Get instance of UI class
	 *
	 * @return 	\ht_dms\ui\ui
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
	 * @return object
	 */
	public static function init() {
		if ( !self::$instance )
			self::$instance = new elements();

		return self::$instance;

	}
} 
