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
		$link = ht_dms_url( $id, 'post-type' );
		$link = add_query_arg( 'add-comment', true, $link );
		$link = add_query_arg( 'dms_id', $id, $link );


		$form = sprintf(  '<form action="%1s" method="POST" id="dms-comment-form">', $link );
		$form .= sprintf( '<input type="hidden" name="dms_id" value="%1s">', $id );
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
	 *
	 * @param	array	 $tabs {
	 *     For each tab, label and content. First tab will be active by default.
	 *
	 *     @type string $label 		Label for tab.
	 *     @type string $content 	Content of tab.
	 * }
	 * @param bool|string    Optional. ID for outermost container.
	 *
	 * @return 	string
	 *
	 * @since 	0.0.1
	 */
	function tab_maker( $tabs, $tab_prefix = 'tab_', $class = '', $id = false ) {
		if ( ! $tab_prefix ) {
			$tab_prefix = 'tab_';
		}

		return $this->tabs( $tabs, $tab_prefix, $class, false, $id );


	}


	function tabs( $tabs, $tab_prefix = 'tab_', $class = '', $vertical = false, $id = false ) {
		if ( ! $tab_prefix ) {
			$tab_prefix = 'tab_';
		}
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
		$vertical = apply_filters( 'ht_dms_foundation_vertical_tabs', $vertical, $tab_prefix );

		if ( $vertical ) {
			$vertical = 'vertical';
		}


		if ( $vertical ) {
			$class = $class. ' '.$vertical;

		}
		else {
			$vertical = '';
		}



		$out = sprintf( '<ul class="%1s" data-tab >', $class );
		$out .=  '<li class="tab-title active"><a href="#'.$tab_prefix.'1">'.$tabs[ 0 ][ 'label' ].'</a></li>';
		$i = 2;
		foreach ( $tabs as $key => $value ) {
			if ( $key != 0 ) {
				$out .= '<li class="tab-title"><a href="#'.$tab_prefix.''.$i.'">'.$value[ 'label' ].'</a></li>';
				$i++;
			}

		}
		$out .= apply_filters( 'ht_dms_after_foundation_tab_choice', '' );
		$out .= '</ul>';



		$i = 1;

		if ( false == $id ) {
			$id = 'tabs';
		}

		$out .= sprintf( '<div id="%0s" class="tabs-content %1s" >', $id, $vertical );


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



	function accordion( $panels, $prefix= 'panel', $class = '', $id ) {

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
			$pageURL .= $_SERVER["SERVER_ADDR"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_ADDR"].$_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	}


	/**
	 * Get instance of UI class
	 *
	 * @return \ht_dms\ui\ui
	 *
	 * @since 	0.0.1
	 */
	function ui(){
		$ui = ht_dms_ui();

		return $ui;

	}

	function breadcrumbs() {
		$name = apply_filters( 'ht_dms_name', 'ht_dms' );

		$logo = apply_filters( 'ht_dms_logo_instead_of_name_in_title', false );

		if ( $logo  ) {
			$name = sprintf( '<img src="%1s" alt="Home" height="50" width="50" />', $logo );
		}

		$bread_names = array(
			'organization' => '',
			'group'=> '',
			'decision' => '',
		);

		$script = \ht_dms\helper\json::encode_to_script( $bread_names, 'breadNamesJSON' );

		$name = $this->link( null, 'front', $name );

		if (  ! ht_dms_is( 'home' ) || ht_dms_is_notification() ) {
			$titles = $oID = $gID = $dID = $tID = false;

			$id = get_queried_object_id();

			if ( ht_dms_is_organization( $id ) ) {
				$oID = $id;
			} elseif ( ht_dms_is_group( $id ) ) {
				$gID = $id;
				$oID = ht_dms_group_class()->get_organization( $gID );

			} elseif ( ht_dms_is_decision( $id ) ) {

				$dID = $id;
				$obj = ht_dms_decision( $id );
				$oID = ht_dms_decision_class()->get_organization( $id, $obj );
				$gID = ht_dms_decision_class()->get_group( $id, $obj );
			}

		}
		else{
			$out = $script;
			$out .= sprintf( '<div id="breadcrumbs" class="bread"><img heigth="30" width="30" src="%1s" /></div>', $logo );
			return $out;
		}



		foreach ( array(
			HT_DMS_ORGANIZATION_POD_NAME => $oID,
			HT_DMS_GROUP_POD_NAME => $gID,
			HT_DMS_DECISION_POD_NAME => $dID,
		) as $type => $id ) {
			if ( ht_dms_integer( $id ) ) {
				$name = get_the_title( $id );
				$link = get_the_permalink( $id );

				$bread_names[ ht_dms_prefix_remover( $type ) ] = $name;

				if ( $type == HT_DMS_ORGANIZATION_POD_NAME ) {
					$span_class= 'org fa fa-university';
					$font_id = 'breadNames';
					$font_class = 'orgName';
				}
				elseif ( $type == HT_DMS_GROUP_POD_NAME ) {
					$span_class= 'gru fa fa-group';
					$font_id = 'breadGroup';
					$font_class = 'orgName';
				}
				elseif( $type === HT_DMS_DECISION_POD_NAME ) {
					$span_class= 'dec fa fa-check';
					$font_id = 'breadDecid';
					$font_class = 'orgName';
				}
				$titles[] = sprintf( '<div class="in"><a href="%1s"><span class="%2s"><font id="%3s" class="%4s">%5s</font></a></span></div>', $link, $span_class, $font_id, $font_class, $name );

			}



		}

		end( $titles );
		$key = key( $titles );
		$titles[ $key ] = str_replace( '<div class="in">', '<div class="inLast">', $titles[ $key ] );

		$out = $script;
		$out .= sprintf( '<div id="breadcrumbs" class="bread">%1s</div>', implode( $titles ) );

		return $out;

	}



	/**
	 * Main title section/breadcrumbs
	 *
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
		return $this->breadcrumbs();
		$name = apply_filters( 'ht_dms_name', 'ht_dms' );

		$logo = apply_filters( 'ht_dms_logo_instead_of_name_in_title', false );

		if ( $logo  ) {
			$name = sprintf( '<img src="%1s" alt="Home" height="50" width="50" />', $logo );
		}
		$name = $this->link( null, 'front', $name );

		if (  ! ht_dms_is( 'home' ) || ht_dms_is_notification() ) {
			$titles = $oID = $gID = $dID = $tID = false;

			$id = get_queried_object_id();

			if ( ht_dms_is_organization( $id ) ) {
				$oID = $id;
			}
			elseif( ht_dms_is_group( $id  ) ) {
				$gID = $id;
				$oID = ht_dms_group_class()->get_organization( $gID );

			}
			elseif( ht_dms_is_decision( $id ) ) {

				$dID = $id;
				$obj = ht_dms_decision( $id );
				$oID = ht_dms_decision_class()->get_organization( $id, $obj );
				$gID = ht_dms_decision_class()->get_group( $id, $obj );
			}
			elseif( ht_dms_is_task( $id ) ) {
				//@todo if ! https://github.com/HoloTree/ht_dms/issues/55
			}



			foreach ( array(
				HT_DMS_ORGANIZATION_POD_NAME => $oID,
				HT_DMS_GROUP_POD_NAME => $gID,
				HT_DMS_DECISION_POD_NAME => $dID,
			) as $type => $id ) {
				if ( ht_dms_integer( $id ) ) {
					$titles[] = sprintf(
						'<span class="breadcrumbs-component" ><span class="breadcrumbs-label breadcrumbs-component">%1s:</span> %2s </span>',
						$build_elements->visualize_hierarchy_icon( $type ),
						$this->link( $id, 'permalink', get_the_title( $id ) )
					);
				}
			}

			if ( is_array( $titles ) ) {
				$name .= sprintf( '<span id="breadcrumbs-titles">%1s</span>', implode( $titles ) );
			}

		}

		$name = apply_filters( 'ht_dms_title_override', $name, $id );
		return $name;

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
				ht_dms_error();
			}
		}
		else {
			$action_name = apply_filters( 'ht_dms_action_name', 'dms_action' );
		}

		$url = add_query_arg( $action_name, $action, $url );

		if ( $id !== false ) {
			$id_var = apply_filters( 'ht_dms_action_id_var', 'dms_id' );

			$url = add_query_arg( $id_var, $id, $url );
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
			$url = ht_dms_home();
		}
		elseif ( intval( $id ) !== 0 ) {
			if ( $type === 'permalink' || $type === 'post' ) {
				$url = get_permalink( $id );
			}
			elseif ( $type === 'post_type_archive' || $type === 'cpt-archive' ) {
				$url = get_post_type_archive_link( $id );
			}
			elseif ( $type === 'taxonomy' || $type === 'tax' ) {
				if ( term_exists( $id, HT_DMS_TASK_POD_NAME ) ) {
					$url = get_term_link( $id, HT_DMS_TASK_POD_NAME );
				}
				else {
					$url = '#';
				}


			}
			elseif ( $type === 'user' ) {
				$url = get_author_posts_url( $id );
			}
			else {
				ht_dms_error( $type . ' Is an unacceptable $type for', __FUNCTION__ );
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

		$url = get_term_link( $id, HT_DMS_TASK_POD_NAME );

		if ( is_null( $text ) ) {
			$term = get_term( $id, HT_DMS_TASK_POD_NAME );
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
	 * @param bool|string    Optional. ID for outermost container.
	 *
	 * @return string The container
	 *
	 * @since 0.0.1
	 */
	function output_container( $content, $prefix = null, $class = '', $id = false ) {
		foreach( $content as $i => $c ) {
			if ( ! isset( $c[ 'content' ] ) || ! is_string( $c['content'] ) ) {
				unset( $content[ $i ] );
				if ( HT_DEV_MODE ) {
					echo sprintf( __('The tab %1s was not a string, so it was unset from output container.', 'ht_dms' ), $i );
				}
			}

		}

		if ( ( function_exists( 'is_phone' ) && is_phone() ) || ( defined( 'HT_DEVICE' ) && HT_DEVICE === 'phone' ) ) {
			return $this->accordion( $content, $prefix, $class, $id );
		}
		else {
			return $this->tabs( $content, $prefix, $class, $id );
		}
	}

	/**
	 * Creates a hamburger menu
	 *
	 * @param array $menu_items Should be in form of link => link text
	 *
	 * @return array|bool|string
	 */
	function hamburger( $menu_items ) {

		if ( is_array( $menu_items ) ) {
			foreach( $menu_items as $link => $text ) {
				$menu[] = sprintf( '<a href="%1s">%2s</a>', $link, $text );
			}
			if ( is_array( $menu ) ) {
				$out[] = sprintf( '<div>%1s</div>', implode( $menu ) );
			}
			else {
				$out = false;
			}

			if ( is_array( $out ) ) {
				$out = sprintf( '<nav id="ht-sub-menu"><span class="button" id="ht-sub-menu-button"></span>%1s</nav>', implode( $out ) );
			}

		}

		if ( is_string( $out ) ) {
			return $out;
		}


	}

	/**
	 * Creates view of a group of members
	 *
	 *
	 * @param  array    $users Array of user IDs.
	 * @param int  $desktop_wide Number of items wide in desktop view
	 * @param bool $mobile_wide Optional. Number of items wide in mobile view. If false, the default, will be half of $desktop_wide.
	 *
	 * @since 0.0.3
	 *
	 * @return string
	 */
	function members_details_view( $users, $desktop_wide = 8, $mobile_wide = false, $mini_mode = false ) {
		$members = false;
		if ( is_array( $users ) ) {
			foreach( $users as $key => $user ) {
				if ( ht_dms_integer( $user ) ) {
					$user = ht_dms_ui()->build_elements()->member_details( $user );
				}
				if ( ! pods_v( 'name', $user ) && isset( $user[0] )) {
					$user = $user[0];

				}


				$name = pods_v( 'name', $user );

				if ( ! is_null( $name ) ) {
					$avatar = pods_v( 'avatar', $user, ht_dms_fallback_avatar() );
					if ( ! $mini_mode ) {
						$members[ ] = sprintf( '<li class="member-view"><div class="avatar">%1s</span><div class="name">%2s</span></li>', $avatar, $name );
					}
					else {
						$members[] = sprintf( '<li class="member-view"><div class="mini-avatar" name="%1s">%2s</div></li>', $name, $avatar );
					}
				}
			}
		}

		if ( is_array( $members ) ) {
			if ( ! $mobile_wide ) {
				$mobile_wide = $desktop_wide / 2;
			}

			$class = 'members-view';
			if ( $mini_mode ) {
				$class .= ' mini-mode';
			}

 			return sprintf( '<div class="%0s"><ul class="small-block-grid-%d large-block-grid-%d">%3s</ul></div>', $class, $mobile_wide, $desktop_wide, implode( $members ) );


		}
		else {
			return '';
		}


	}

	/**
	 * Visual display of the current status of a consensus
	 *
	 * @param int|array $id Decision ID. Or can be an array. If array, must be in format ht_dms_decision_class()->consensus_members() returns.
	 * @param int  $desktop_wide Number of items wide in desktop view
	 * @param bool $mobile_wide Optional. Number of items wide in mobile view. If false, the default, will be half of $desktop_wide.
	 *
	 * @return string
	 *
	 * @since 0.0.3
	 */
	function view_consensus( $id, $desktop_wide = 8, $mobile_wide = false ) {

		$tabs = false;

		if ( ! is_array( $id ) ) {
			$sorted_consensus = ht_dms_consensus_class()->sort_consensus( $id );
		}
		else {
			$sorted_consensus = $id;
		}

		if ( is_array( $sorted_consensus ) ) {
			foreach ( $sorted_consensus as $status => $user_ids ) {
				$users = '';


				$container_id = "consensus-status-{$status}";
				if ( is_array( $user_ids ) ) {
					$users = implode( $user_ids, ',' );
				}
				$js =  'loadUsers( [' . $users . '], "'.$container_id.'", "#user-mini" )';

				$content = $this->ui()->view_loaders()->handlebars( 'user-mini', $container_id, $js );


				$tabs[ ] = array (
					'label'   => ht_dms_ui()->build_elements()->consensus_tab_header( $status, count( $users ) ),
					'content' => $content,
				);
			}
		}


		if ( is_array( $tabs ) ) {
			$tabs = ht_dms_ui()->output_elements()->tabs( $tabs, 'consensus_view_tab_', 'consensus-tabs', true, 'consensus-tabs' );

			if ( is_string( $tabs ) ) {

				return sprintf( '
					<div id="consensus-view" class="consensus-view">
						<h5>%0s</h5>
						%1s
					</div>
				', __( 'Consensus Status', 'ht_dms' ), $tabs );

			}
		}

	}

	/**
	 * Returns the third element with its wrapping markup.
	 *
	 * @param $type network|user|organization|group|consenus
	 *
	 * @return string
	 */
	function third_element( $type, $id ) {
		if ( $type == 'consensus' ) {
			ht_dms_consensus($id);
			$content = $this->view_consensus( $id );
		}
		elseif ( in_array( $type, array( 'network', 'user', 'organization', 'group' ) ) ) {
			$content = call_user_func( array( ht_dms_ui()->activity( $type, $id ),  $type ), $id );
		}
		else{
			ht_dms_error();
		}

		return sprintf( '<div id="ht-dms-third-element" class="ht-dms-third-element-%1s">%2s</div>', $type, $content );

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

