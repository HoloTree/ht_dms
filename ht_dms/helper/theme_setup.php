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
		if ( wp_is_mobile() ) {
			add_filter( 'htdms_theme_no_sidebar', '__return_true' );
			if ( is_singular( HT_DMS_GROUP_CPT_NAME ) ) {
				add_action( 'htdms_theme_before_off_canvas_right', array( $this, 'group_sidebar' ) );


			}
			else {
				add_filter( 'htdms_theme_offcanvas_right_widget_area', array ( $this, 'right_widgets' ) );
			}

		}
		else {
			add_filter(  'htdms_theme_use_off_canvas_right', '__return_false' );
		}
		add_filter( 'htdms_theme_get_sidebar', array( $this, 'sidebars' ) );

		add_action( 'init', array( $this, 'no_right_oc_widgets') );

		add_action( 'htdms_theme_after_title', array( $this, 'after_title') );

		add_action( 'ht_dms_site_info', array( $this, 'footer_text' ) );

		add_action( 'htdms_theme_main_class', array( $this, 'main_class') );

		add_filter( 'htdms_theme_use_off_canvas_menu_left', '__return_false' );

		add_action( 'htdms_theme_after_off_canvas_left', array( $this, 'left_menu' ) );

		if ( ! is_user_logged_in() ) {
			add_filter( 'htdms_theme_use_off_canvas_left', '__return_false' );
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

	/**
	 *
	 * @TODO Why not just remove widget area from theme?
	 */
	function no_right_oc_widgets() {
		remove_action( 'htdms_theme_after_off_canvas_right', 'htdms_theme_off_canvas_widgets_right' );
	}

	function after_title() {
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
				echo $out;
			}	//endif $title has a value

		} //endif is group/ decision singular

	}

	function footer_text() {
		if ( false !== ( $text = apply_filters( 'ht_dms_footer_text', false ) ) ) {
			return $text;

		}

	}

	function main_class( ) {
		return 'large-12 small-12 columns';
	}

	function left_menu() {
		echo holotree_dms_ui()->build_elements()->menu();
	}
}
