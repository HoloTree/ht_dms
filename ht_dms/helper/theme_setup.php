<?php
/**
 * Actions and filters from the HoloTree DMS theme.
 *
 * @package   @holotree_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\helper;

class Theme_Setup {

	function __construct() {
		$prefix = $this->prefix();
		add_filter( "{$prefix}_no_sidebar", '__return_true' );

		add_filter(  "{$prefix}_use_off_canvas_right", '__return_false' );

		add_filter( "{$prefix}_get_sidebar", array( $this, 'sidebars' ) );

		add_action( "{$prefix}_tab_bar_middle", array( $this, 'title_in_tab_bar' ) );

		add_action( "{$prefix}_ht_dms_site_info", array( $this, 'footer_text' ) );

		add_action( "{$prefix}_main_class", array( $this, 'main_class') );

		add_filter( "{$prefix}_use_off_canvas_menu_left", '__return_false' );

		add_action( "{$prefix}_after_off_canvas_left", array( $this, 'left_menu' ) );

		if ( ! is_user_logged_in() ) {
			add_filter( "{$prefix}_use_off_canvas_left", '__return_false' );
		}
	}

	/**
	 * Initializes the HoloTree_DMS_Theme_Setup() class
	 *
	 * Checks for an existing HoloTree_DMS_Theme_Setup() instance
	 * and if it doesn't find one, creates it.
	 */
	public static function init() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new Theme_Setup();
		}

		return $instance;
	}

	function right_widgets( $widget_area ) {
		$widget_area = 'sidebar-1';
		return $widget_area;
	}

	function sidebars( $name ) {
		if ( is_singular( HT_DMS_GROUP_CPT_NAME ) ) {
			$name = 'sidebar-group';
		}
		else {
			$name = 'sidebar-dms';
		}

		return $name;
	}

	function title_in_tab_bar() {
		$out = '';

		if ( is_singular( HT_DMS_GROUP_CPT_NAME ) || is_singular( HT_DMS_DECISION_CPT_NAME ) ) {
			global $post;
			$title = $post->post_title;
			if ( $title != '' || empty( $title ) ) {
				if ( is_singular( HT_DMS_GROUP_CPT_NAME ) ) {
						$gID= $post->ID;
						$gTitle = $title;

				}
				elseif ( is_singular( HT_DMS_DECISION_CPT_NAME ) ) {
					$decision_title = $title;
					$dID = $post->ID;
					$dTitle = $title;
					$obj = holotree_decision( $dID );
					$gID = $obj->field( 'group.ID' );
					$gID = $gID[ 0 ];
					$gTitle = get_the_title( $gID );
				}
				elseif( is_tax( HT_DMS_TASK_CT_NAME ) ) {
					$task = holotree_task( get_queried_object_id(), true, true );
					$tID = $task[ 'term_id' ];
					$tTitle = $task[ 'name' ];
					$dID = $task[ 'decision' ][ 'ID' ];
					$d = holotree_decision( $dID );
					$dTitle = $d[ 'post_title' ];
					$gID = $d[ 'group' ][ 0 ][ 'ID' ];
					$gTitle = get_the_title( $gID );
				}


				//$out = '<span class="group-title-in-header" id="group-title-in-header-'.$gID.'">'.holotree_link( $gID, 'permalink', $gTitle, $gTitle ).'</span>';
				if ( is_singular( HT_DMS_DECISION_CPT_NAME ) || is_tax( HT_DMS_TASK_CT_NAME ) ) {
					$out .= '<span class="decision-title-in-header" id="decision-title-in-header-'.$dID.'">'.holotree_link( $dID, 'permalink', $dTitle, $dTitle ).'</span>';
				}
				if ( is_tax( HT_DMS_TASK_CT_NAME ) ) {
					$out .= '<span class="decision-title-in-header" id="task-title-in-header-'.$tID.'">'.holotree_link( $tID, 'tax', $tTitle, $tTitle ).'</span>';
				}
				return $out;
			}	//endif $title has a value

		} //endif is group/ decision singular

	}

	function footer_text() {
		$prefix = $this->prefix();
		if ( false !== ( $text = apply_filters( "{$prefix}_footer_text", false ) ) ) {
			return $text;

		}

	}

	function main_class( ) {
		return 'large-12 small-12 columns';
	}

	function left_menu() {
		echo holotree_dms_ui()->build_elements()->menu();
	}

	function prefix() {
		if ( ( $stylesheet = get_stylesheet() ) == 'ht_dms_theme' ) {
			return 'htdms';
		}

		return $stylesheet;

	}
}
