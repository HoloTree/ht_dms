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
		if ( defined( 'HT_NEW_VIEW' ) && HT_NEW_VIEW  ) {
			return $this->new_view();
		}
		$action =  pods_v( 'dms_action', 'get', false, true );

		if ( in_array( $action, array_keys( $this->special_views() ) ) ) {

			return $this->special_view_loader( $action );

		}
		else {
			$context = $this->view_context( $post_type );
			if ( $context !== 'task' ) {
				return $this->view_cache( $context, $post_type );
			}
		}

	}

	/**
	 * Load special views
	 *
	 *
	 * @param 	string $action Action ($_GET[ 'dms_action' ]) to trigger view.
	 *
	 * @return string			The action
	 *
	 * @since 0.0.3
	 */
	function special_view_loader( $action ) {
		$special_views = $this->special_views();

		if ( isset( $special_views[ $action ] ) ) {
			$view = $special_views[ $action ];
			if ( ! is_file( $view ) ) {
				$view = trailingslashit( HT_DMS_VIEW_DIR ) . $view . '.php';
			}

			$view = $this->content_wrap( include( $view ) );

			return $view;

		}

	}

	/**
	 * Allowed special views
	 *
	 * @return array
	 *
	 * @since 0.0.3
	 */
	function special_views() {

		$special_views = array(
			'notifications' => 'notifications',
			'new-message' => 'notifications',
			'preferences' => 'preferences',
			'propose-change' => 'propose-change',
		);

		/**
		 * Change/ add to the array of special views to override the main view with, based on value of $_GET[ 'dms_action' ]
		 *
		 * @params array $special views. Must be in form of GET Var => path to view. Path view should be full file path or the name of a file, without extension in HT_DMS_VIEW_DIR
		 *
		 * @since 0.0.3
		 */

		return apply_filters( 'ht_dms_special_views', $special_views );

	}

	function special_view_loaders() {

	}

	/**
	 * Creates the single view template for tasks.
	 */
	function task_view( $view ) {
		if ( get_query_var( 'taxonomy' ) === HT_DMS_TASK_CT_NAME ) {
			return $this->view_cache( 'task', null );
		}

		return $view;
	}

	/**
	 * Outputs layout for new JS-based UI
	 *
	 * @return string
	 */
	function new_view() {
		$phone = $this->mobile_detect();

		$main_view = 'dms-tabs.html';
		if ( $phone ) {
			$main_view = 'dms-accordion.html';
		}

		$main_view = trailingslashit( HT_DMS_VIEW_DIR ).$main_view;

		/**
		 * Change which view file is used.
		 *
		 * @param string $main_view Path to file to be loaded.
		 * @param bool $phone Whether mobile detection returned as phone view or not.
		 *
		 * @return string Path to view to load.
		 *
		 * @since 0.0.2
		 */
		$main_view = apply_filters( 'ht_dms_main_view', $main_view, $phone  );

		if ( file_exists( $main_view ) ) {
			$main_view = file_get_contents( $main_view );
			$main_view = str_replace( '{{main_title}}', $this->main_title( $this->id() ), $main_view );
			$out = $main_view;
			if ( is_string( $out ) ) {
				return $out;
			}
		} else {
			holotree_error();
		}

	}


	/**
	 * Mobile-device detection
	 *
	 * @return bool True if phone view false if not
	 * @since 0.0.2
	 */
	function mobile_detect() {
		if (  ( defined( 'HT_DEVICE' ) && HT_DEVICE === 'phone' ) || ( function_exists( 'is_phone' ) && is_phone() ) || wp_is_mobile() ) {

			return true;

		}

	}

	function inline_data() {


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
		//bypass cache in dev mode
		if ( HT_DEV_MODE ) {
			return $this->view_get( $context, $post_type );
		}

		$uID = get_current_user_id();

		$key = array( $context, $id, $uID );
		$key = implode( '_', $key );
		$key = md5( $key );

		$group = 'ht_dms_front_end_views';

		pods_transient_set( $key, false );
		if (  false === ( $value = pods_view_get( $key, $cache_mode, $group ) ) ) {
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
			return $this->content_wrap( include( trailingslashit( HT_DMS_VIEW_DIR ) . 'task-single.php' ), true );
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
	 * Prepares content for output
	 *
	 * @param 	string  $content 	The content to wrap.
	 * @param 	bool 	$task		Optional. If is task or not. Default is false.
	 *
	 * @return string
	 *
	 * @since 0.0.2
	 */
	function content_wrap( $content, $task = false ) {
		$id = $this->id();
		$container_id = $id;
		if ( $id == 00 ) {
			$container_id = 'home';
		}

		$container_id = sprintf( '%1s-%2s', HT_DMS_PREFIX, $container_id );

		$out = sprintf( '<div class="holotree %1s" id="holotree-dms">', $container_id );

		/**
		 * Output something or trigger something before HoloTree Main content happens.
		 *
		 * Output occurs inside the main HoloTree container.
		 *
		 * @since 0.0.1
		 */
		$out .= do_action( 'ht_before_ht' );
		$out .= $this->ui()->output_elements()->hamburger( ht_dms_mini_menu_items() );
		$out .= $this->alert();

		$out .= sprintf( '<div id="holotree-dms-title-section">%1s</div>', $this->main_title( $id, $task ) );

		$out .= sprintf( '<div id="holotree-dms-content" data-equalizer >%1s</div>', $content );

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

	function main_title( $id, $task = false ) {
		if ( apply_filters( 'ht_dms_view_title', true ) ) {
			$name = $this->ui()->output_elements()->title( $id, null, $task );
			$class = 'entry-title';

			/**
			 * Change or add to title class
			 *
			 * @param string $class
			 *
			 * @since 0.0.1
			 */
			$class = apply_filters( 'ht_dms_entry_title_class', $class );

			$out = '<h2 class="' . $class . '">' . $name . '</h2>';
		}

		if ( isset( $out ) ) {
			return $out;
		}
	}

	private function id() {
		if ( is_home() || is_front_page() ) {
			$id = 00;
		}
		else {
			$id = get_queried_object_id();
		}

		return $id;

	}

	function magic_template( $template_file, $obj, $page = false, $cache_args = null ) {
		$no_items = __( 'No items to display', 'holotree' );
		if ( $obj->total() > 0 ) {
			if ( file_exists( $template_file ) && class_exists( 'Pods_Templates' ) ) {

				$template_file = file_get_contents( $template_file );

				if ( is_null( $cache_args ) || !is_array( $cache_args ) ) {
					$expires = DAY_IN_SECONDS;
					$cache_mode = 'transient';
				}
				else {
					extract( $cache_args );
				}


				if (  is_object( $obj ) && is_pod( $obj ) ) {

					if ( $page ) {
						$obj->find( array( 'page' => $page ) );
					}

					if ( $obj->total() > 0 ) {

						$out = '';


						while ( $obj->fetch() ) {

							//reset id
							$obj->id = $obj->id();

							$out .= $this->template( $template_file, $obj );
						}

					}
					elseif ( is_int( $obj->id() ) ) {
						if ( (int)$obj->id() < 1 ) {

							return $no_items;

						}

						$out = $this->template( $template_file, $obj );
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
			else {
				holotree_error( sprintf( 'The view %1s could not be loaded.', $template_file ) );
			}
		}
		else {
			return $no_items;
		}


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

	function template( $template_file, $obj ) {
		$template = '<div class="ht_dms_template" style="">';
		if ( HT_DEV_MODE ) {
			$template .= '<span style="float:right">'.$obj->ID().'</span>';
		}
		$template .= \Pods_Templates::do_template( $template_file, $obj );

		$template .= '</div>';

		return $template;
	}

	/**
	 * Outputs an alert if 'dms-alert' get var is set and true.
	 *
	 * @uses get_option( 'ht_dms_action_message')
	 *
	 * @return string
	 *
	 * @since 0.0.2
	 */
	private function alert() {
		if ( pods_v( 'dms-alert', 'get', false, true ) ) {
			return holotree_dms_ui()->elements()->alert( get_option( 'ht_dms_action_message', '' ), 'success' );
		}
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
