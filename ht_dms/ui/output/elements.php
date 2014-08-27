<?php
/**
 * Various elements that make up the UI.
 *
 * @package   @TODO
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\ui\output;

class elements {
	/**
	 * Show comments and comment form.
	 *
	 * Comment form is in a modal.
	 *
	 * @param   int 	$id 		Post ID.
	 * @param 	int 	$per_page	Optional. Number of comments per page. Default is ten.
	 * @param 	bool	$form		Optional. Whether to show new comment form or not. Default is true.
	 *
	 * @return 	string	$out		The comments and form.
	 *
	 * @since 0.0.1
	 */
	function discussion( $id, $per_page = 10, $form = true ) {
		$out = '<ol class="commentlist">';

		//Gather comments for a specific page/post
		$comments = get_comments(array(
			'post_id'	=> $id,
			'status' 	=> 'approve' //Change this to the type of comments to be displayed
		));

		//Display the list of comments
		$out .= wp_list_comments(
			array(
				'per_page' 			=> $per_page,
				'reverse_top_level' => false,
				'echo'				=> false,
				),
			$comments
		);


		$out .=  '</ol>';
		if ( $form !== false ) {
			$out .= $this->modal( $this->comment_form( $id ), 'large', 'Add Comment', 'Leave Comment' );
		}

		return $out;

	}

	/**
	 * The comment form
	 *
	 * @param 	int 	$id	ID of post to add comment to.
	 *
	 * @return 	string		The comment form.
	 *
	 * @since 0.0.1
	 */
	function comment_form( $id ) {
		$form = '<form action="' . $this->ui()->elements()->current_page_url() . '" method="POST" id="dms-comment-form">';
		$form .= '<input type="hidden" name="dms_id" value="' . $id. '">';
		$form .= '<input type="hidden" name="dms_action" value="add-comment">';
		$form .= '<label>Comment Text
						<textarea name="dms_comment_text" placeholder=""></textarea>
		  		</label>';
		$form .= '<input type="submit" />';
		$form .= '</form>';
		return $form;
	}

	/**
	 * Create a Foundation Reveal modal.
	 *
	 * @TODO Come up with a way to output modal content in footer.
	 *
	 * @param 	string		$content		Content of the modal itself.
	 * @param 	string      $modal_id		ID for modal.
	 * @param 	string      $trigger_text	Text for link that triggers modal.
	 * @param 	string 		$size 			Optional. Size of modal. tiny|small|medium|large|xlarge Default is large.
	 * @param 	bool   		$button			Optional. Whether to make the trigger link a button or not. Default is false--not a button.
	 *
	 * @see		http://foundation.zurb.com/docs/components/reveal.html
	 *
	 * @return 	string						Modal + Trigger
	 *
	 * @since	0.0.1
	 */
	function modal( $content, $modal_id, $trigger_text, $size= 'large', $button = true ) {
		if ( $button !== false ) {
			$class = 'button';
		}
		$trigger = '<a href="#" data-reveal-id="'.$modal_id.'" class="'.$class.'" data-reveal>'.$trigger_text.'</a>';
		$modal = '<div id="'.$modal_id.'" class="reveal-modal '.$size.'" data-reveal>';
		$modal .= $content;
		$modal .= '</div>';

		return $trigger.$modal;
	}

	/**
	 * Creates tabbed UI
	 *
	 * @TODO Deal with dependency on foundation?
	 *
	 * @param	array	 $tabs {
	 *     For each tab, label and content. First tab will be active by default.
	 *
	 *     @type string $label 		Label for tab.
	 *     @type string $content 	Content of tab.
	 * }
	 *
	 * @return 	string
	 *
	 * @since 	0.0.1
	 */
	function tab_maker( $tabs, $tab_prefix = 'tab_', $class = '' ) {
		if ( ! $tab_prefix ) {
			$tab_prefix = 'tab_';
		}

		if ( HT_FOUNDATION ) {
			return $this->tab_maker_foundation( $tabs, $tab_prefix );

		}
		else {
			return $this->tab_maker_jUI( $tabs, $tab_prefix, $class );

		}
	}

	function tab_maker_jUI( $tabs, $tab_prefix = 'tab_', $class = '' ) {
		$out = '<div id="tabs"';
		if ( $class !== '' ) {
			$out .= ' class="'.$class.'"';
		}
		$out .='>';
		$out .= '<ul>';
		$i = 1;
		foreach ( $tabs as $tab ) {
				$out .= '<li><a href="#'.$tab_prefix.''.$i.'">'.$tab[ 'label' ].'</a></li>';;
				$i++;
		}

		$out .= '</ul>';
		$i = 1;
		foreach ( $tabs as $tab ) {
				$out .= '<div class="content" id="'.$tab_prefix.$i.'">';
				$out .= $tab[ 'content' ];
				$out .= '</div><!--#'.$tab_prefix.$i.'-->';
				$i++;
		}

		$out .= '</div>';

		return $out;
	}

	function tab_maker_foundation( $tabs, $tab_prefix = 'tab_', $class = '', $vertical = false ) {
		$class = $class.' tabs';

		/**
		 * Filter to change value of $vertical to force vertical tabs.
		 *
		 * @param bool $vertical True for vertical tabs, false for horizontal tabs.
		 *
		 * @return bool
		 *
		 * @since 0.0.2
		 */
		$vertical = apply_filters( 'ht_dms_foundation_vertical_tabs', $vertical );

		if ( $vertical ) {
			$vertical = 'vertical';
		}

		$equalizer = false;

		if ( $vertical ) {
			$class = $class. ' '.$vertical;
			//$equalizer = true;
		}
		else {
			$vertical = '';
		}

		$attr = '';
		if ( $equalizer ) {
			$attr = 'data-equalizer-watch';
		}

		$out = sprintf( '<ul class="%1s" data-tab %2s>', $class, $attr );
		$out .=  '<li class="tab-title active"><a href="#'.$tab_prefix.'1">'.$tabs[ 0 ][ 'label' ].'</a></li>';
		$i = 2;
		foreach ( $tabs as $key => $value ) {
			if ( $key != 0 ) {
				$out .= '<li class="tab-title"><a href="#'.$tab_prefix.''.$i.'">'.$value[ 'label' ].'</a></li>';
				$i++;
			}

		}
		$out .= '</ul>';

		if ( $equalizer ) {
			$attr = 'data-equalizer-watch';
		}

		$i = 1;

		$out .= sprintf( '<div id="tabs" class="tabs-content %1s" %2s >', $vertical, $attr );


		foreach ( $tabs as $key => $tab) {
			if ( $key === 0 ) {
				$out .= sprintf( '<div class="content active" id="%1s">%2s</div>', $tab_prefix.$i, $tab[ 'content']  );
				$i++;
			}
			else {
				$out .= sprintf( '<div class="content" id="%1s">%2s</div>', $tab_prefix.$i, $tab[ 'content']  );
				$i++;
			}

		}


		$out .= '</div><!--#tabs-->';

		return $out;
	}


	function accordion(  $panels, $prefix = 'panel_', $class = '' ) {
		if ( ! $prefix  ) {
			$prefix = 'panel_';
		}
		
		if ( HT_FOUNDATION ) {
			return $this->accordion_foundation( $panels, $prefix, $class );

		}
		else {
			return $this->accordion_jUI( $panels, $class );

 		}
	}

	function accordion_jUI(  $panels,  $class = '' ) {
		$out = '<div id="accordion"';
		if ( $class !== '' ) {
			$out .= ' class="'.$class.'"';
		}
		$out .= '>';
		foreach ( $panels as $panel ) {
			$out .= '<h3>'.$panel[ 'label'].'</h3>';
			$out .= '<div>';
			$out .= $panel[ 'content' ];
			$out .= '</div>';
		}

		$out .= '</div><!--#accordion';
		if ( $class !== '' ) {
			$out .= ' '.$class;
		}
		$out .= '-->';

		return $out;
	}
	function accordion_foundation( $panels, $prefix= 'panel', $class = '' ) {

			$out = '<dl class="accordion ' . $class . '" data-accordion>';
			$i = 0;
			foreach ( $panels as $panel ) {
				if ( isset( $panel[ 'content' ] )  && isset( $panel[ 'label' ] ) ) {


					$out .= '<dd>';
					$out .= '<a href="#' . $prefix . $i . '">' . $panel[ 'label' ] . '</a>';
					$out .= '<div id="' . $prefix . $i . '" class="content';
					if ( $i === 0 ) {
						$out .= ' accActive';
					}
					$out .= '">';
					$out .= $panel[ 'content' ];
					$out .= '</div></dd><!---' . $prefix . $i . '-->';

					$i++;
				}
			}
			$out .= '</dl><!--' . $class . ' accordion-->';

			return $out;


	}

	/**
	 * The URL of current page.
	 *
	 * @return 	string	The URL of current page.
	 *
	 * @since	0.0.1
	 */
	function current_page_url() {
		$pageURL = 'http';
		if( isset($_SERVER["HTTPS"]) ) {
			if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		}
		$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	}

	function project_managment_list( $id ) {

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

	/**
	 * Wrap a sidebar.
	 *
	 * @param 	string      	$content	The sidebar content.
	 * @param 	bool|string	 	$wrap		Optional. The opening wrapping container for the sidebar.
	 *
	 * @return 	string
	 *
	 * @since	0.0.1
	 */
	function sidebar_wrapper( $content, $wrap = false ) {
		if ( !$wrap ) {
			$fclass = htdms_theme_sidebar_class( true );
			$wrap = '<div id="secondary" class="widget-area '.$fclass.'" role="complementary">';
			/**
			 * Override the default sidebar wrapper.
			 *
			 * Only add the opening containers. Closing tags are automatically appended.
			 *
			 * @param 	string $wrap The opening container(s).
			 *
			 * @since 	0.0.1
			 */
			$wrap = apply_filters( 'ht_dms_sidebar_wrapper', $wrap );
		}

		$n = substr_count( $wrap, '<div>' );
		if ( $n === 1 ) {
			$end = '</div>';
		}
		else {
			$end = '';
			for ($i = 1; $i <= $n; $i++) {
				$end .= '</div>';
			}
		}

		return $wrap.$content.$end;
	}

	/**
	 * Get title
	 *
	 * @todo notification
	 *
	 * @param 	int  		$id		ID of item to get title of
	 * @param 	null|obj	$obj	Optional. Single item object of current item. Not used for groups.
	 * @param 	bool		$task	Optional. Set to true if getting title for a task. Default is false.
	 *
	 * @return	string
	 *
	 * @since	0.0.1
	 */
	function title( $id, $obj = null, $task = false, $separator = ' - ' ) {
		remove_filter( 'the_title', '__return_false' );
		$name = apply_filters( 'ht_dms_name', 'HoloTree' );

		$logo = apply_filters( 'ht_dms_logo_instead_of_name_in_title', false );

		if ( $logo  ) {
			$name = sprintf( '<img src="%1s" alt="Home" height="50" width="50" />', $logo );
		}
		$name = $this->link( null, 'front', $name );

		if ( get_post_type( $id ) === HT_DMS_GROUP_CPT_NAME ) {
			$title = get_the_title( $id );
			$decision = $this->link( $id, 'permalink', $title );
			$name .= $separator .$decision;
		}
		elseif( get_post_type( $id ) === HT_DMS_DECISION_CPT_NAME ) {
			$obj = holotree_decision( $id, $obj );
			$group = $obj->field( 'group' );
			$title = get_the_title( $id );
			$decision = $this->link( $id, 'permalink', $title );
			$title = get_the_title( (int) $group[0]['ID'] );
			$group = $this->link((int) $group[0]['ID'], 'permalink', $title );

			$name .= $separator . $decision . $separator . $group ;
		}
		elseif ( $task ) {
			$obj = holotree_task( $id, $obj );
			$name .= $separator . $obj->field( 'name' );
			$dID = $obj->field( 'decision' );
			$dID = $dID[ 'ID' ];
			$name .= $separator . get_the_title( $dID );
		}
		add_filter( 'the_title', '__return_false' );

		$name = apply_filters( 'ht_dms_title_override', $name, $id );
		return $name;

	}

	/**
	 * @param 	string	$file		The location of the file to render, relative to HT_DMS_VIEW_DIR. Unless $other_dir === true.
	 * @param 	obj		$obj		Pods object to use
	 * @param 	bool	$partial	Optional. If file is a partial (ie it is in  HT_DMS_VIEW_DIR.'/partials/' ). Default is false.
	 * @param 	string	$other_dir	Optional. Dir to load file from, if not in HT_DMS_VIEW_DIR. Specify directory only. Put file name in $file.
	 *
	 * @return	string				The rendered template, if it source file exists, else false.
	 */
	function template( $file, $obj, $partial = false, $other_dir = false ) {
		if ( $other_dir === false ) {
			if ( $partial ) {
				$part = trailingslashit( HT_DMS_VIEW_DIR ) . 'partials/' . $file . '.php';
			}
			else {
				$part = trailingslashit( HT_DMS_VIEW_DIR ) . $file . '.php';
			}
		}
		else {
			$part = trailingslashit( $file );
		}

		if ( file_exists( $part ) ) {
			return \Pods_Templates::do_template( file_get_contents( $part ), $obj );
		}
		else {
			holotree_error( 'template could not be loaded', print_c3( array( '$file' => $file, '$part' => $part )));
		}

	}

	/**
	 * For safely appending variables to urls
	 *
	 * @TODO Impliment this throughout.
	 *
	 * @param 	string			$url	Base URL
	 * @param 	string|array	$action	Variable to append. If string should be value for 'dms_action'. To set action and value pass array.
	 *   Array arguments {
	 * 		@type string var 	The name of the variable to append.
	 * 		@type string value	The value of the variable.
	 *   }
	 *
	 * @return 	string					URL
	 *
	 * @since 	0.0.1
	 */
	function action_append( $url, $action, $id = false ) {
		if ( is_array( $action ) ) {
			$action_name = pods_v( 'var', $action, false, true );
			$action = pods_v( 'value', $action, false, true );
			if ( ! $action || ! $action_name ) {
				holotree_error();
			}
		}
		else {
			$action_name = apply_filters( 'ht_dms_action_name', 'dms_action' );
		}
		$action_name = $action_name.'=';
		if ( strpos( $url, '?' ) !== false ) {
			$url = $url.'&'.$action_name.$action;
		}
		else {
			$url = $url.'?'.$action_name.$action;
		}

		if ( $id !== false ) {
			$id_var = apply_filters( 'ht_dms_action_id_var', 'dms_id' );
			$id_var = '&'.$id_var.'=';
			$url .= $id_var.$id;
		}

		return $url;
	}

	/**
	 * For creating links with optional button, class and ID.
	 *
	 * @param int|string    $id			ID of post, post type or taxonomy to get link to or a complete URL, as a string.
	 * @param string 		$type		Optional. Type of content being linked to. post|post_type_archive|taxonomy|user. Not used if $id is a string. Default is post.
	 * @param bool|string 	$text		Optional. Text Of the link. If false, post title will be used.
	 * @param null|string	$title		Optional. Text for title attribute. If null none is used.
	 * @param bool|string   $button		Optional. Whether to output as a button or not. Defaults to false.
	 * @param bool|string   $classes	Optional. Any classes to add to link. Defaults to false.
	 * @param bool|string   $link_id	Optional. CSS ID to add to link. Defaults to false.
	 * @param bool|array	$append		Optional. Action and ID to append to array. should be action, id. If ID isn't set $id param is used. Default is true.
	 *
	 *
	 * @return null|string
	 */
	function link( $id, $type = 'permalink', $text = 'view', $title = null, $button = false, $classes = false, $link_id = false, $append = false  ) {
		if ( is_object( $type ) ) {
			$type = 'post';
		}

		if ( is_object( $id ) ) {
			return false;
		}
		elseif( $type === 'home' || $type === 'front' ) {
			$url = site_url();
		}
		elseif ( intval( $id ) !== 0 ) {
			if ( $type === 'permalink' || $type === 'post' ) {
				$url = get_permalink( $id );
			}
			elseif ( $type === 'post_type_archive' || $type === 'cpt-archive' ) {
				$url = get_post_type_archive_link( $id );
			}
			elseif ( $type === 'taxonomy' || $type === 'tax' ) {
				if ( term_exists( $id, HT_DMS_TASK_CT_NAME ) ) {
					$url = get_term_link( $id, HT_DMS_TASK_CT_NAME );
				}
				else {
					$url = '#';
				}


			}
			elseif ( $type === 'user' ) {
				$url = get_author_posts_url( $id );
			}
			else {
				holotree_error( $type . ' Is an unacceptable $type for', __FUNCTION__ );
			}
		}
		else {
			$url = $id;
		}

		if ( ( $text === 'view' || is_null( $text ) ) && ( $type === 'permalink' || $type === 'post' ) ) {
			$post = get_post( $id );
			if ( is_object( $post ) && is_string( $post->post_title ) && !empty( $post->post_title ) ) {
				$text = $post->post_title;
			}
		}

		$class = '';
		if ( $classes !== FALSE ) {
			$class = $classes;
		}
		if ( $button !== FALSE ) {
			$class .= 'button';
		}

		$stuff = '';
		if ( $class !== '' ) {
			$stuff .= ' class="'. $class . '" ';
		}
		if ( $link_id !== false ) {
			$stuff .= ' id="' . $link_id . '" ';
		}
		if ( $title !== false  ) {
			$title = get_the_title( $id );
			$stuff .= ' title=" ' . $title . ' " ';
		}

		if ( $append !== false && isset( $append[ 'action' ] ) ) {
			$action = $append[ 'action' ];
			if ( isset( $append[ 'ID' ] ) ) {
				$id = $append[ 'ID' ];
			}

			$url = $this->action_append( $url, $action, $id );
		}

		$link = '<a href="' . $url . '"' . $stuff . ' >' . $text . '</a>';



		return $link;
	}

	/**
	 * Create an alert using the foundation class.
	 *
	 * @see http://foundation.zurb.com/docs/components/alert_boxes.html
	 *
	 * @param 	string  	$text 		Text of string.
	 * @param 	null|String $type		Type of alert. null|success|warning|alert|info|secondary. Default is null, which uses alert.
	 * @param 	bool 		$closeable	Optional. If true alert can be closed/ dismissed. Defaults to false.
	 * @param 	bool 		$rounded	Optional. If true corners will be rounded. Default is false.
	 *
	 * @return 	string
	 *
	 * @since	0.0.1
	 */
	function alert( $text, $type = null, $closeable = false, $rounded = false ) {
		$alert = '<div data-alert class="alert-box ';
		if ( is_null( $type ) ) {
			$alert .= 'alert';
		}
		else {
			$alert .= '';
		}

		if ( $rounded ) {
			$alert .= ' rounded';
		}

		$alert .= '" >';

		$alert .= $text;
		if ( $closeable ) {
			$alert .= '<a href="#" class="close">&times;</a>';
		}

		$alert .= '</div><!--.alert-box-->';

		return $alert;

	}

	function task_link( $id = null, $text = null, $title = null, $button = false ) {

		if ( is_null( $id ) ) {
			$id = get_queried_object_id();
		}
		$url = get_term_link( $id, HT_DMS_TASK_CT_NAME );

		if ( is_null( $text ) ) {
			$term = get_term( $id, HT_DMS_TASK_CT_NAME );
			if ( is_object( $term ) && ! is_a( $term, 'WP_Error' ) ) {
				$text = $term->name;
			}
			if ( is_a( $term, 'WP_Error' ) ) {
				$text = 'task';
			}

		}

		if ( is_null( $title ) ) {
			if ( is_null( $text ) || is_object( $text ) || ! is_string( $text ) ) {
				$text = 'Task';
			}

			$title = 'View '.$text;
		}

		$class = '';
		if ( $button ) {
			$class = 'class="button"';
		}


		if ( is_string( $url ) && is_string( $title ) && is_string( $text ) ) {
			$out = sprintf( '<a href="%1s" text="%2s" %3s>%4s</a>', $url, $title, $class, $text );
			return $out;
		}

	}

	/**
	 * Outputs content in tabs or accordion according to device detection.
	 *
	 * @param array        $content The content to output. Should be a multi-dimensional array with each index containing keys for 'content' and 'label'
	 * @param null|string   $prefix Optional The prefix to use for the t
	 * @param string $class Optional. Class for outermost container
	 *
	 * @return string The container
	 *
	 * @since 0.0.1
	 */
	function output_container( $content, $prefix = null, $class = '' ) {
		foreach( $content as $i => $c ) {
			if ( ! is_string( $c['content'] ) ) {
				unset( $content[ $i ] );
				if ( HT_DEV_MODE ) {
					echo sprintf( __('The tab %1s was not a string, so it was unset from output container. It is a %2s', 'holotree'), $c[ 'label'], gettype( $c['content'] ) );
				}
			}

		}

		if ( ( function_exists( 'is_phone' ) && is_phone() ) || ( defined( 'HT_DEVICE' ) && HT_DEVICE === 'phone' ) ) {
			return $this->accordion( $content, $prefix, $class );
		}
		else {
			return $this->tab_maker( $content, $prefix, $class );
		}
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
