<?php
/**
 * Load templates and partials, and output the UI
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\ui\output;

class loaders implements \Filter_Hook_SubscriberInterface {


	/**
	 * Set filters
	 *
	 * @since 0.0.3
	 *
	 * @return array
	 */
	public  static function get_filters() {
		return array(
			'app_starter_content_part_view' => 'view_loaders',
			'app_starter_alt_main_view' => 'task_view',

		);

	}

	/**
	 * Loads the HoloTree DMS Content
	 *
	 * @since 0.0.1
	 *
	 * @param $view
	 *
	 * @return bool|mixed|null|string|void
	 */
	public function view_loaders( $view ) {
		$post_type = get_post_type();
		if ( defined( 'HT_NEW_VIEW' ) && HT_NEW_VIEW  ) {
			return $this->new_view();
		}
		$action =  pods_v_sanitized( 'dms_action', 'get', false, true );

		if ( in_array( $action, array_keys( $this->special_views() ) ) ) {

			return $this->special_view_loader( $action );

		}
		else {
			$context = $this->view_context( $post_type );
			if ( $context !== 'task' ) {
				$output = $this->view_cache( $context, $post_type );

				return $output;

			}
		}

	}

	/**
	 * Load special views
	 *
	 * @since 0.0.3
	 * @param 	string $action Action ($_GET[ 'dms_action' ]) to trigger view.
	 *
	 * @return string			The action
	 */
	public function special_view_loader( $action ) {
		$special_views = $this->special_views();

		if ( isset( $special_views[ $action ] ) ) {
			$view = $special_views[ $action ];
			if ( ! is_file( $view ) ) {
				$view = trailingslashit( HT_DMS_VIEW_DIR ) . $view . '.php';
			}

			$view = $this->content_wrap( include( $view ), false, $action );

			return $view;

		}

	}

	/**
	 * Allowed special views
	 *
	 * @since 0.0.3
	 *
	 * @return array
	 */
	public function special_views() {

		$special_views = array(
			'notifications' => 'notifications',
			'new-message' => 'notifications',
			'preferences' => 'preferences',
			'propose-change' => 'propose-change',
			'user-profile' 	=> 'preferences',
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



	/**
	 * Creates the single view template for tasks.
	 *
	 * @since 0.0.x
	 */
	public function task_view( $view ) {
		if ( get_query_var( 'taxonomy' ) === HT_DMS_TASK_POD_NAME ) {
			return $this->view_cache( 'task', null );
		}

		return $view;
	}

	/**
	 * Mobile-device detection
	 *
	 * @since 0.0.2
	 *
	 * @return bool True if phone view false if not
	 */
	public function mobile_detect() {
		if (  ( defined( 'HT_DEVICE' ) && HT_DEVICE === 'phone' ) || ( function_exists( 'is_phone' ) && is_phone() ) || wp_is_mobile() ) {

			return true;

		}

	}


	/**
	 * Gets views from the view cache or gets view and caches it.
	 *
	 * @since 0.0.x
	 *
	 * @param        $context
	 * @param string $cache_mode
	 * @param null   $id
	 *
	 * @return bool|mixed|null|string|void
	 */
	public function view_cache( $context, $post_type, $cache_mode = 'cache', $id = null ) {
		//bypass cache in dev mode
		if ( HT_DEV_MODE ) {
			return $this->view_get( $context, $post_type );
		}

		$uID = get_current_user_id();

		$key = array( $context, $id, $uID );
		$key = implode( '_', $key );
		$key = md5( $key );

		$group = 'ht_dms_front_end_views';

		if (  false === ( $value = pods_view_get( $key, $cache_mode, $group ) ) ) {
			$value = $this->view_get( $context, $post_type );
			pods_view_set( $key, $value, 0, $cache_mode, $group );
		}

		return $value;

	}

	/**
	 * Loads the view based on context and post type
	 *
	 * @since 0.0.x
	 * @param $context
	 * @param $post_type
	 *
	 * @return string
	 */
	public function view_get( $context, $post_type ) {

		if ( $context === 'home' ) {
			return $this->content_wrap( include( trailingslashit( HT_DMS_VIEW_DIR ) . 'home.php' ), false, 'home' );
		}

		if ( $context === 'single' ) {
			$post_type = str_replace( HT_DMS_PREFIX.'_', '', $post_type );

			return $this->content_wrap( include( trailingslashit( HT_DMS_VIEW_DIR ) . $post_type . '-' . $context . '.php' ), false, $post_type );
		}

		if ( $context === 'task' ) {
			return $this->content_wrap( include( trailingslashit( HT_DMS_VIEW_DIR ) . 'task-single.php' ), true, HT_DMS_TASK_POD_NAME );
		}

	}

	/**
	 * Determine view context for DMS
	 *
	 * @since 	0.0.1
	 *
	 * @return 	null|string single|home|null
	 */
	public function view_context( $post_type ) {
		if ( ! $post_type  ) {
			$post_type = get_post_type();
		}


		if ( is_home() || is_front_page() ) {
			$context = 'home';

		}
		elseif ( $post_type === HT_DMS_GROUP_POD_NAME || $post_type === HT_DMS_DECISION_POD_NAME || HT_DMS_ORGANIZATION_POD_NAME ) {
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
		elseif( is_tax( HT_DMS_TASK_POD_NAME ) ) {
			$context = 'task';
		}
		else {
			$context = null;
		}

		return $context;

	}

	/**
	 * Prepares content for output
	 *
	 * @since 0.0.2
	 *
	 * @param 	string  $content 	The content to wrap.
	 * @param 	bool 	$task		Optional. If is task or not. Default is false.
	 * @param   string  $post_type
	 *
	 * @return string
	 */
	public function content_wrap( $content, $task = false, $post_type ) {
		$id = $this->id();
		$container_id = $id;
		if ( $id == 00 ) {
			$container_id = 'home';
		}

		$container_id = sprintf( '%1s-%2s', HT_DMS_PREFIX, $container_id );

		$out = sprintf( '<div class="holotree %1s" id="holotree-dms">', $container_id );

		/**
		 * Output something or trigger something before HoloTree DMS main content happens.
		 *
		 * Output occurs inside the main HoloTree DMS container.
		 *
		 * @since 0.0.1
		 */
		do_action( 'ht_dms_before_output' );
		$out .= $this->ui()->output_elements()->hamburger( ht_dms_mini_menu_items() );
		$out .= $this->alert();

		$out .= sprintf( '<div id="holotree-dms-title-section">%1s</div>', $this->main_title( $id, $task ) );

		$out .= sprintf( '<div id="holotree-dms-content"  >%1s</div>', $content );

		/**
		 * Third UI element is bypassed for now
		 *
		 * @see https://github.com/HoloTree/ht_dms/issues/70
		 */
		if ( 1==2 ) {
			if ( $post_type == 'home' ) {
				$type = 'network';
			} elseif ( in_array( $post_type, array_keys( $this->special_views() ) ) ) {
				$type = 'user';
			} else {
				if ( $post_type !== ht_dms_prefix_remover( HT_DMS_DECISION_POD_NAME ) ) {
					$type = ht_dms_prefix_remover( $post_type );
				} else {
					$type = 'consensus_ui';
				}

			}

			$out .= $this->ui()->output_elements()->third_element( $type, $id );
		}

		/**
		 * Output something or trigger something after HoloTree DMS main content happens.
		 *
		 * Output occurs inside the main HoloTree container.
		 *
		 * @since 0.0.1
		 */
		do_action( 'ht_dms_after_output' );

		$out .= '</div>';

		return $out;
	}

	/**
	 * Get the main template element.
	 *
	 * @since 0.0.x
	 * @param $id
	 * @param bool $task
	 *
	 * @return string
	 */
	public function main_title( $id, $task = false ) {
		/**
		 * Show or nor show the main title section
		 *
		 * @since 0.0.x
		 *
		 * @param bool $show To show or not.
		 */
		if ( apply_filters( 'ht_dms_view_title', true ) ) {
			return $this->ui()->output_elements()->breadcrumbs();

		}

	}

	/**
	 * Get the container ID for the main UI wrapping container
	 *
	 * @since 0.0.x
	 *
	 * @return int
	 */
	protected function id() {
		if ( is_home() || is_front_page() ) {
			$id = 00;
		}
		else {
			$id = get_queried_object_id();
		}

		return $id;

	}

	/**
	 * Load a "magic template" IE one that uses Pods Templates syntax, but is not a Pods Template, and parse with a Pods object.
	 *
	 * @since 0.x.x
	 *
	 * @param string $template_file The path to the template file.
	 * @param \Pods $obj Pods object to parse with template.
	 * @param int|bool $page Optional. Page of results from object to show. Default is false, which shows first
	 * @param array|null $cache_args Optional. The cache args for caching.
	 *
	 * @return mixed|string|void
	 */
	public function magic_template( $template_file, $obj, $page = false, $cache_args = null ) {
		$no_items = __( 'No items to display', 'ht_dms' );
		if ( $obj->total() > 0 ) {
			$view = pathinfo( $template_file );
			$view = pods_v( 'filename', $view );
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

							$out .= $this->template( $template_file, $obj, $view );
						}

					}
					elseif ( is_int( $obj->id() ) ) {
						if ( (int)$obj->id() < 1 ) {

							return $no_items;

						}

						$out = $this->template( $template_file, $obj, $view );
					}
				}
				else {
					if ( HT_DEV_MODE ) {
						return 'No object for template loader...';
					}
					return $no_items;
				}

				if ( ! empty( $out ) ) {

					$out = ht_dms_ui()->build_elements()->icon_substitution( $out );
					$before = apply_filters( 'ht_dms_before_magic_templates', '', $view );
					$after = apply_filters( 'ht_dms_after_magic_templates', '', $view );
					$out = $before . $out . $after;
					return $out;
				}


			}
			else {
				ht_dms_error( sprintf( 'The view %1s could not be loaded.', $template_file ) );
			}
		}
		else {
			return $no_items;
		}


	}

	/**
	 * Get a view from the views class
	 *
	 * @todo rm?
	 *
	 * @since 0.x.x
	 *
	 * @param string $type The type of view to get
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function type_view( $type, $args ) {
		$model = $this->ui()->models();

		for ( $i = 0; $i < 7; $i++ ) {
			if ( !isset( $args[ $i ]) ) {
				$args[ $i ] = null;
			}
		}

		ksort( $args );

		return call_user_func_array( array( $model, $type ), $args );

	}


	/**
	 * Render template on an individual item
	 *
	 * @since 0.x.x
	 *
	 * @access protected
	 *
	 * @param string $template_file Template file's contents, not path.
	 * @param \Pods $obj The Pods object, single row pre-fetch, to render with
	 * @param string $view
	 *
	 * @return string
	 */
	protected function template( $template_file, $obj, $view ) {
		$template = '<div class="ht_dms_template" style="">';
		$id = $obj->id();
		if ( HT_DEV_MODE ) {
			$template .= '<span style="float:right">'.$id.'</span>';
		}
		$template .= \Pods_Templates::do_template( $template_file, $obj );

		$template .= '</div>';

		$before = apply_filters( 'ht_dms_before_magic_template', '', $view, $id );
		$after = apply_filters( 'ht_dms_after_magic_template', '', $view, $id );

		return $before . $template . $after;
	}

	/**
	 * Outputs an alert if 'dms-alert' get var is set and true.
	 *
	 * @since 0.0.2
	 *
	 * @acces private
	 *
	 * @uses get_option( 'ht_dms_action_message')
	 *
	 * @return string
	 *
	 * @since 0.0.2
	 */
	private function alert() {
		if ( pods_v( 'dms-alert', 'get', false, true ) ) {
			return ht_dms_ui()->elements()->alert( get_option( 'ht_dms_action_message', '' ), 'success' );

		}

	}

	/**
	 * Load markup for Handlebars Template rendering
	 *
	 * @since 0.2.0
	 *
	 * @param string $file Path to template file.
	 * @param string|bool $id Optional. ID attribute to use.
	 * @param string|bool $js Optional. Extra JS to add.
	 * @param string $class Optional. Class attribute to use.
	 * @param string $container_type Optional. Type of container to use. Default is "div".
	 * @param bool $partial Optional. If it is a partial or not. Default is true, which is only way this will work currently.
	 *
	 * @return string
	 */
	public function handlebars( $file, $id = false, $js = false, $class='', $container_type = 'div', $partial = true ) {
		$out = array();


		if ( is_string( $id ) ) {
			$out[] =$this->handlebars_container( $id, $class, $container_type );
			holotree_enqueue_handlebar( $id,  $this->handlebars_template_file_location( $file, true ) );

		}
		else {
			$out[] = $template = $this->handlebars_template( $file, $partial );
		}

		if ( is_string( $js ) ) {
			$out[] = '<script type="text/javascript">' . $js . '</script>';
		}


		$out = implode( $out );

		return $out;


	}

	/**
	 * Return a Handlebars template.
	 *
	 * @since 0.0.3
	 *
	 * @param string $file File name
	 * @param bool $partial Optional. If is partial. Default is true.
	 *
	 * @return bool|string
	 */
	public function handlebars_template( $file, $partial = true ) {
		$template = $this->handlebars_template_file_location( $file, $partial );
		if ( is_string( $template ) ) {
			$template = pods_view( $template, null, HOUR_IN_SECONDS, 'cache', true );

			return $template;
		}

	}

	/**
	 * Return full file path for a Handlebars template
	 *
	 *
	 * @since 0.0.3
	 *
	 * @param string $file File name
	 * @param bool $partial Optional. If is partial. Default is true.
	 *
	 * @return bool|string
	 */
	public function handlebars_template_file_location( $file, $partial = true ) {
		$template = trailingslashit( HT_DMS_VIEW_DIR ) . 'handlebars/';
		if ( $partial ) {
			$template .= 'partials/';
		}
		$template .= $file . '.html';
		if ( file_exists( $template ) ) {
			return $template;

		}

	}

	/**
	 * Container for loading handlebars rendered views into.
	 *
	 * @since 0.0.3
	 *
	 * @param string $id Id attribute
	 * @param string $class Optional. Class attribute.
	 * @param string $container_type Optional. type of container. Default is 'div'
	 *
	 * @return string
	 */
	public function handlebars_container(  $id, $class='', $container_type = 'div' ) {
		if( $class ) {
			$class = 'class="'.esc_attr( $class).'"';
		}

		return "<{$container_type} id=\"".esc_attr( $id ). "\" {$class} ></{$container_type}>";

	}

	/**
	 * Prepare group members for output via JSON
	 *
	 * @since 0.x.x
	 *
	 * @param int $id Group ID
	 *
	 * @return bool|string
	 */
	function group_prepare( $id ) {
		$key = "group_handlebars_prepare_{$id}";
		if ( HT_DEV_MODE ) {
			pods_cache_clear( $key );
		}

		if ( false == ( $js = pods_cache_get( $key )  ) ) {
			$members = \ht_dms\groups\members::all_members( $id );

			if ( is_array( $members ) ) {
				$members = implode( $members, ',' );

				$js = 'loadUsers( [' . $members . '], "#group-members-'.$id.'", "#user-mini" )';

				if ( is_string( $js ) ) {
					pods_cache_set( $key, $js );
				}

			}

		}

		return $js;

	}



	/**
	 * Get instance of UI class
	 *
	 * @return 	\ht_dms\ui\ui
	 *
	 * @since 	0.0.1
	 */
	public function ui(){
		$ui = ht_dms_ui();

		return $ui;

	}

} 
