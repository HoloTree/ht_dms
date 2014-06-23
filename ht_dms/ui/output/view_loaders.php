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

namespace ht_dms\ui\output;

class view_loaders {

	function view_loader( $content ) {


		$post_type = get_post_type();
		if ( HT_DEV_MODE ) {
			echo $post_type;
		}

		if ( is_home() || is_front_page() ) {
			$content = $this->content_wrap( include( trailingslashit( HT_DMS_VIEW_DIR ) . 'home.php' ) );

		}
		elseif ( $post_type === HT_DMS_GROUP_CPT_NAME || $post_type === HT_DMS_DECISION_CPT_NAME || HT_DMS_ORGANIZATION_NAME ) {
			if ( is_singular( $post_type ) ) {
				$context = 'single';
			}
			elseif ( is_post_type_archive( $post_type ) ) {
				//$context = 'list';
				$context = null;
			}
			else {
				$context = null;
			}

			if ( HT_DEV_MODE ) {
				echo $context;
			}


			if ( !is_null( $context ) ) {
				$post_type = str_replace( 'ht_dms_', '', $post_type );
				$content = $this->content_wrap( include( trailingslashit( HT_DMS_VIEW_DIR ) . $post_type . '-' . $context . '.php' ) );
			}
		}
		else {
			global $wp_query;
			if ( isset( $wp_query->query_vars[ 'taxonomy'] ) ) {
				$taxonomy = $wp_query->query_vars[ 'taxonomy'];
				if ( $taxonomy === 'task' ) {
					$content = include( trailingslashit( HT_DMS_VIEW_DIR ) . 'task-list.php' );
				}
			}

		}
//$content = '<div style="margin-top:200px">ff</div>';


		return $content;
	}

	/**
	 * Creates the single view template for tasks.
	 *
	 * @param $template
	 *
	 * @uses 'tempalte_include' filter
	 *
	 * @return string
	 *
	 * @since 0.0.1
	 */
	function task_view( $template ) {
		$v = pods_v( 'ht_dms_task', 'get', false, true );
		if ( $v != false ) {
			$template = trailingslashit( HT_DMS_VIEW_DIR ).'task-single.php';
		}

		return $template;

	}

	function sidebar( $name ) {
		$name = apply_filters( 'ht_sidebar', $name );
		echo $this->sidebar_loader( $name );
	}

	function sidebar_loader( $name ) {
		if ( file_exists( trailingslashit( get_stylesheet_directory_uri() ).'sidebar-'.$name.'.php' ) ) {
			$view = trailingslashit( get_stylesheet_directory_uri() ).'sidebar-'.$name.'.php';
		}
		elseif ( file_exists( trailingslashit( get_template_directory_uri() ).'sidebar-'.$name.'.php' ) ) {
			$view = trailingslashit( get_template_directory_uri() ).'sidebar-'.$name.'php';
		}
		else {
			$view = trailingslashit( HT_DMS_VIEW_DIR ).'views/'.'sidebar-'.$name.'.php';
		}

		include_once( $view );

	}

	/**
	 * Wrap HoloTree DMS content in div and add title
	 *
	 * @param   string 	$content What to wrap
	 *
	 * @return 	string			Wrapped content
	 *
	 * @since	0.0.1
	 */
	function _content_wrap( $content  ) {


		$obj = get_queried_object();
		if( !is_object( $obj) ) {

			print_c3( get_queried_object() );
		}
		$id = $obj->ID;
		if ( isset( $obj->term_id ) ) {
			$name = $obj->name;
			$type = 'tax';
		}
		else {
			$name = $obj->post_title;
			$type = $obj = 'post';
		}
		if ( is_home() || is_front_page() ) {
			$name = __( 'HoloTree', 'holotree' );
		}

		$out = '<div class="holotree" id="'.$id.'">';
		if ( apply_filters( 'ht_dms_view_title', true ) ) {
			$class = 'entry-title';
			$class = apply_filters( 'ht_dms_entry_title_class', $class );
			$out .= '<h2 class="' . $class . '">' . $name . '</h2>';
		}
		$out .= $content;
		$out .= '</div>';

		return $out;

	}

	function content_wrap( $content ) {
		global $post;
		$id = $post->ID;


		$out = '<div class="holotree" id="'.$id.'">';
		/**
		 * Output something or trigger something before HoloTree Main content happens.
		 *
		 * Output occurs inside the main HoloTree container.
		 *
		 * @since 0.0.1
		 */
		$out .= do_action( 'ht_before_ht' );

		if ( apply_filters( 'ht_dms_view_title', true ) ) {
			$name = $this->ui()->elements()->title( $id, null );
			$class = 'entry-title';

			/**
			 * Change or add to title class
			 *
			 * @param string $class
			 *
			 * @since 0.0.1
			 */
			$class = apply_filters( 'ht_dms_entry_title_class', $class );

			$out .= '<h2 class="' . $class . '">' . $name . '</h2>';
		}
		$out .= $content;

		/**
		 * Output something or trigger something after HoloTree Main content happens.
		 *
		 * Output occurs inside the main HoloTree container.
		 *
		 * @since 0.0.1
		 */
		$out .=do_action( 'ht_after_ht' );

		$out .= '</div>';

		return $out;
	}

	function magic_template( $view, $obj, $cache_args = null ) {
		$no_items = __( 'No items to display', 'holotree' );
		if (  file_exists( $view ) && class_exists( 'Pods_Templates' ) ) {
			$view = file_get_contents( $view );

			if (  is_null( $cache_args )  || !is_array( $cache_args ) ) {
				$expires = DAY_IN_SECONDS;
				$cache_mode = 'transient';
			}
			else {
				extract( $cache_args );
			}

			$total = $obj->total();

			if ( $total > 0 ) {
				$out = '';
				while ( $obj->fetch() ) {

					//reset id
					$obj->id = $obj->id();

					$out .= $this->template( $view, $obj );
				}


			}
			else {
				$obj->id = $obj->id();
				if ( (int) $obj->id() < 1 ) {

					return $no_items;

				}

				$out =  $this->template( $view, $obj );
			}

			if ( empty( $out ) ) {
				return $no_items;
			}

			return $out;

		}
		//pods_error( __METHOD__.' can not load view - '.$view);

	}

	function type_view( $type, $args ) {
		$model = $this->ui()->models();

		for ( $i = 0; $i < 5; $i++ ) {
			if ( !isset( $args[ $i ]) ) {
				$args[ $i ] = null;
			}
		}

		ksort( $args );

		return call_user_func_array( array( $model, $type ), $args );

	}

	private function template( $view, $obj ) {
		$template = '<div class="ht_dms_template" style="border:1px solid black">';
		if ( HT_DEV_MODE ) {
			$template .= '<span style="float:right">'.$obj->ID().'</span>';
		}
		$template .= \Pods_Templates::do_template( $view, $obj );
		$template .= '</div>';

		return $template;
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
