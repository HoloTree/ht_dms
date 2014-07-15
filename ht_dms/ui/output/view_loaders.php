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

	/**
	 * Loads the HoloTree DMS Content
	 *
	 * @param $view
	 *
	 * @return bool|mixed|null|string|void
	 */
	function view_loaders( $view ) {
		$post_type = get_post_type();
		$context = $this->view_context( $post_type );

		return $this->view_cache( $context, $post_type );

	}

	/**
	 * View loaders based on the content filter.
	 *
	 * Not used if app_starter is current theme.
	 *
	 * @TODO Clean up using $this->view_context() or delete.
	 *
	 * @param $content
	 *
	 * @return mixed|string
	 */
	function generic_view_loader( $content ) {


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
				$taxonomy = $wp_query->query_vars[ 'taxonomy' ];
				if ( $taxonomy === 'task' ) {
					$content = include( trailingslashit( HT_DMS_VIEW_DIR ) . 'task-list.php' );
				}
			}

		}


		return $content;

	}

	/**
	 * Gets views from the view cache or gets view and caches it.
	 *
	 * @param        $context
	 * @param string $cache_mode
	 * @param null   $id
	 *
	 * @return bool|mixed|null|string|void
	 */
	function view_cache( $context, $post_type, $cache_mode = 'cache', $id = null ) {
		if ( is_null( $id ) ) {
			if ( is_home() || is_front_page() ) {
				$id = 00;
			}
			else {
				$id = get_queried_object_id();
			}
		}

		$uID = get_current_user_id();

		$key = array( $context, $id, $uID );
		$key = implode( '_', $key );

		$group = 'ht_dms_front_end_views';

		if ( false === ( $value = pods_view_get( $key, $cache_mode, $group ) ) ) {
			$value = $this->view_get( $context, $post_type );
			pods_view_set( $key, $value, 0, $cache_mode, $group );
		}

		return $value;

	}

	/**
	 * Loads the view based on context and post type
	 *
	 * @param $context
	 * @param $post_type
	 *
	 * @return string
	 */
	function view_get( $context, $post_type ) {
		
		if ( $context === 'home' ) {
			return $this->content_wrap( include( trailingslashit( HT_DMS_VIEW_DIR ) . 'home.php' ) );
		}

		if ( $context === 'single' ) {
			$post_type = str_replace( HT_DMS_PREFIX.'_', '', $post_type );

			return $this->content_wrap( include( trailingslashit( HT_DMS_VIEW_DIR ) . $post_type . '-' . $context . '.php' ) );
		}

		if ( $context === 'task' ) {
			return $this->content_wrap( include( trailingslashit( HT_DMS_VIEW_DIR ) . 'task-single.php' ) );
		}

	}

	/**
	 * Determine view context for DMS
	 *
	 * @return 	null|string single|home|null
	 *
	 * @since 	0.0.1
	 */
	function view_context( $post_type ) {
		if ( ! $post_type  ) {
			$post_type = get_post_type();
		}
		if ( HT_DEV_MODE ) {
			echo $post_type;
		}

		if ( is_home() || is_front_page() ) {
			$context = 'home';

		}
		elseif ( $post_type === HT_DMS_GROUP_CPT_NAME || $post_type === HT_DMS_DECISION_CPT_NAME || HT_DMS_ORGANIZATION_NAME ) {
			if ( is_singular( $post_type ) ) {
				$context = 'single';
			}
			elseif ( is_post_type_archive( $post_type ) ) {
				//@TODO DO we ever use these views?
				//$context = 'list';
				$context = null;
			}
			else {
				$context = null;
			}
		}
		elseif( is_tax( HT_DMS_TASK_CT_NAME ) ) {
			$context = 'task';
		}
		else {
			$context = null;
		}

		return $context;

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
		$out .= do_action( 'ht_after_ht' );

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


			if ( is_object( $obj ) && is_pod( $obj ) ) {
				if ( $obj->total() > 1 ) {
					$out = '';
					while ( $obj->fetch() ) {

						//reset id
						$obj->id = $obj->id();

						$out .= $this->template( $view, $obj );
					}


				}
				elseif ( is_int( $obj->id() ) ) {

					if ( (int)$obj->id() < 1 ) {

						return $no_items;

					}

					$out = $this->template( $view, $obj );
				}
			}
			else {
				if ( HT_DEV_MODE ) {
					return 'No object for template loader...';
				}
				return $no_items;
			}

			if ( ! empty( $out ) ) {
				return $out;
			}



		}
		//pods_error( __METHOD__.' can not load view - '.$view);

	}

	function type_view( $type, $args ) {
		$model = $this->ui()->models();

		for ( $i = 0; $i < 7; $i++ ) {
			if ( !isset( $args[ $i ]) ) {
				$args[ $i ] = null;
			}
		}

		ksort( $args );

		return call_user_func_array( array( $model, $type ), $args );

	}

	private function template( $view, $obj ) {
		$template = '<div class="ht_dms_template" style="">';
		if ( HT_DEV_MODE ) {
			$template .= '<span style="float:right">'.$obj->ID().'</span>';
		}
		$template .= \Pods_Templates::do_template( $view, $obj );
		//$template = $obj->template( '', $view );
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
