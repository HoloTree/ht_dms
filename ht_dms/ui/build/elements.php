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
		$dObj = holotree_decision( false, $dObj );
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

	function foo() {
		return 'fo';
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
