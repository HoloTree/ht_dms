<?php
/**
 * Helper Functions
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

/**
 * Removes the prefix.
 *
 * Useful for turning content type names into short names. Prefix is defined in HT_DMS_PREFIX
 *
 * @param prefixed string  $string
 * @param bool $remove_underscore Optional. Whether to remove the trailing underscore or not. Default is true.
 *
 * @return string
 *
 * @since 0.0.2
 */
function ht_dms_prefix_remover( $string, $remove_underscore = true ) {
	$prefix = HT_DMS_PREFIX;
	if ( $remove_underscore ) {
		$prefix = $prefix.'_';
	}

	return str_replace( $prefix, '', $string );
}


/**
 * Setups the Pods for HoloTree DMS
 *
 *  @since 0.0.2
 *
 * @param bool $package_only	Optional. Whether to just import from import package and skip relationship field updates. Default is false.
 * @param bool $skip_package	Optional. Whether to skip importing the package, and advanced directly to updating relationship fields, not pass go and not collect $200 dollars. Default is false.
 * @param bool $delete_existing	Optional. Whether to delete existing Pods (for DMS including user) before importing. Default is true.
 */
function ht_dms_setup_pods( $package_only = false, $skip_package = true, $delete_existing = false) {

	include( trailingslashit( HT_DMS_DIR ).'setup.php' );

	new ht_dms\setup( $package_only, $skip_package, $delete_existing );

}

function ht_dms_pods_exist() {
	$key = 'ht_dms_pods_exists';
	if ( false == pods_transient_get( $key )  ) {
		$pods = array( HT_DMS_ORGANIZATION_POD_NAME, HT_DMS_GROUP_POD_NAME, HT_DMS_DECISION_POD_NAME, HT_DMS_TASK_POD_NAME );
		$api = pods_api();
		foreach( $pods as $pod ) {
			$params = array( 'name' => $pod );
			if ( ! $api->pod_exists( $params ) ) {
				return false;
			}
		}

		pods_transient_set( $key, true );
	}
	else {
		return pods_transient_get( $key );
	}

}

/**
 * Returns the main HT DMS Page url.
 *
 * By default returns home_url(), can be modified with the 'ht_dms_home_url' filter
 *
 * @since 0.0.2
 *
 * @return mixed|void
 */
function ht_dms_home() {
	$home = home_url();

	/**
	 * Customize the main HT DMS page
	 *
	 * @param string $home. URL
	 *
	 * @since 0.0.2
	 */
	return apply_filters( 'ht_dms_home_url', $home );

}

/**
 * Set mini-menu items outputted
 *
 * @since 0.0.2
 *
 * @return array
 */
function ht_dms_mini_menu_items() {
	
	$items = array(
		ht_dms_home() => ht_dms_add_icon( __( 'Home', 'ht_dms' ), 'home' ),
		ht_dms_preferences_url() => ht_dms_add_icon( __( 'Preferences', 'ht_dms' ), 'preferences' ),
		ht_dms_notifications_url() => ht_dms_add_icon(  __( 'Notifications', 'ht_dms' ), 'notifications' ),
		wp_logout_url() => ht_dms_add_icon(  __( 'Logout', 'ht_dms' ), 'logout' ),
	);

	/**
	 * Override mini-menu items
	 *
	 * @param array $items Must be in form of  link => link text
	 *
	 * @since 0.0.2
	 */
	$items = apply_filters( 'ht_dms_mini_menu_items', $items );

	if ( current_user_can( 'edit_users') ) {
		$items[ admin_url() ] = __( 'WP Admin', 'ht_dms' );
		$items[ get_edit_post_link() ] =  __( 'Edit Post', 'ht_dms' );
	}


	return $items;

}

function ht_dms_url( $id, $type = false ) {
	if ( $type === 'post-type' || in_array( $type, array(
			HT_DMS_DECISION_POD_NAME,
			HT_DMS_GROUP_POD_NAME,
			HT_DMS_ORGANIZATION_POD_NAME,
		)
	) ) {
		return get_permalink( $id );
	}

	if ( false != ( $link = get_permalink( $id ) ) ) {

		return $link;

	}

	if ( $type === HT_DMS_TASK_POD_NAME || false == $link  ) {

		return get_term_link( $id, HT_DMS_TASK_POD_NAME );

	}


}

/**
 * Outputs the loading spinner
 *
 * It will be shown by default you must provide your own hide/show jQuery
 *
 * @return string
 *
 * @since 0.0.2
 */
function ht_dms_spinner() {

	$spinner = ht_dms_ui()->build_elements()->icon( 'spinner', 'fa-2x fa-spin'  );
	$spinner = sprintf( '<div class="spinner" style="padding-top:12px;">%1s</div>', $spinner );
	return $spinner;

}

/**
 * Translates a user's value in the consensus value to a word in the current language.
 *
 * @param int  $value Status code 0|1|2 to translate.
 * @param bool $with icons. Whether to return with an icon or not.
 *
 * @since 0.0.3
 */
function ht_dms_consensus_status_readable( $value, $with_icons = false, $ing = false ) {
	$accepted_values = array( 0,1,2 );
	if ( ! in_array( $value , $accepted_values ) ) {

		return false;

	}

	$values = array(
		'0' => __( 'No Response', 'ht_dms' ),
		'1' => __( 'Accepted', 'ht_dms' ),
		'2' => __( 'Blocked', 'ht_dms' ),
	);

	if ( $ing ) {
		$values[1] = __( 'Accepting', 'ht_dms' );
		$values[2] = __( 'Blocking', 'ht_dms' );
	}

	/**
	 * Change what we call each consensus value
	 *
	 * Note filter will not be used unless its response is in a valid form.
	 *
	 * $params array Values
	 *
	 * @since 0.0.3
	 */
	$filtered = apply_filters( 'ht_dms_readable_consensus_values', $values );

	if ( count( $filtered ) === 3 ) {
		$use = true;

		for( $i = 0; $i <= 2; $i++) {
			if ( ! isset( $filtered[ $i ] ) ) {
				$use = false;
				break;
			}

		}

		if ( $use === true ) {
			$values = $filtered;
		}

	}

	if ( $with_icons ) {

	}

	return pods_v( $value, $values );

}


/**
 * Returns the fallback avatar for users without Gravtars set.
 *
 * @use 'ht_dms_fallback_avatar' filter to change the fallback image.
 *
 * @return string
 *
 * @since 0.0.3
 */
function ht_dms_fallback_avatar() {
	$fallback =  HT_DMS_ROOT_URL  .'ht_dms/ui/img/gus.jpg';
	/**
	 * Fallback avatar for users without one set.
	 *
	 * @param $fallback url of fallback image.
	 *
	 * @since 0.0.1
	 */
	$fallback = apply_filters( 'ht_dms_fallback_avatar', $fallback );

	return $fallback;

}

/**
 * Pass null or user ID, returns same ID or current user ID if null.
 *
 * @param null $uID
 *
 * @return int|null
 *
 * @since 0.0.3
 */
function ht_dms_null_user( $uID = null ) {

	if ( is_null( $uID ) ) {
		global $cuID;
		$uID = $cuID;

	}

	return $uID;


}

function ht_dms_notification_link( $id, $button = false, $title = null, $text = null ) {
	if ( is_null( $title ) || is_null( $text ) ) {
		$obj = ht_dms_notification( $id );

		if ( is_null( $text ) ) {
			$text = $obj->display( 'name' );
		}

		if ( empty( $text ) ) {
			$text = 'View Notification';
		}

		if ( is_null( $title ) ) {
			$title = $text;
		}

	}

	$class = '';
	if ( $button ) {
		$class = 'button';
	}

	if ( 'notifications' === pods_v( 'dms_action', 'get', false, true ) ) {
		$url = '#';
	}
	else {
		$url = ht_dms_action_append( ht_dms_home(), 'notifications', $id );
	}

	$url = "#";

	return sprintf( '<a href="%1s" class="notification-link %2s" notification="%3s" title="%4s" id="a">%5s</a>',
		$url, $class, $id, $title, $text
	);

}

/**
 * Load a Calder a form
 * @param        $caldera_id
 * @param string $before
 * @param string $after
 *
 * @return string
 */
function ht_dms_caldera_loader( $caldera_id, $before = '', $after = '' ) {
	if ( class_exists( 'Caldera_Forms' ) ) {
		$caldera = new \Caldera_Forms();
		$form    = $caldera::render_form( $caldera_id );

		return $before . $form . $after;
	}

}

/**
 * Check if is the content type set in $content type
 *
 * @param 	string      $content_type Content type to check for
 * @param 	bool 		$id	Optional. ID of item to check or if false, the default check current page.
 *
 * @return 	bool
 *
 * @since 	0.0.3
 */
function ht_dms_is( $content_type, $id = false ) {
	if ( $content_type === 'home' ){
		if ( is_home() || is_front_page() ) {

			return true;
		}
		else{
			return false;
		}
	}

	if (  in_array( $content_type, ht_dms_content_types() ) ) {

		if ( $id ) {
			$post = get_post( $id );
		}
		else {
			global $post;
		}
		if ( is_object( $post ) && isset( $post->post_type ) ) {
			if ( $content_type == $post->post_type ) {
				return true;

			}
			else {

				return false;

			}

		}
	}

	if ( $content_type === HT_DMS_TASK_POD_NAME ) {
		if ( !  $id ) {
			$id = get_queried_object_id();
		}

		$term = get_term( $id, $content_type );

		if ( is_object( $term ) ) {

			return true;
		}
		else {

			return false;

		}

	}

	if ( $content_type == HT_DMS_NOTIFICATION_POD_NAME ) {
		if ( $id && is_object( ht_dms_notification( $id ) ) ) {

			return true;

		}

	}


}

/**
 * Check if a particular item, or current page is an  organization.
 *
 * @param 	bool $id Optional. ID of item to check or if false, the default, checks current page.
 *
 * @return bool
 *
 * @since 0.0.3
 */
function ht_dms_is_organization( $id = false ) {

	return ht_dms_is( HT_DMS_ORGANIZATION_POD_NAME, $id );

}

/**
 * Check if a particular item, or current page is a group.
 *
 * @param 	bool $id Optional. ID of item to check or if false, the default, checks current page.
 *
 * @return bool
 *
 * @since 0.0.3
 */
function ht_dms_is_group( $id = false ) {

	return ht_dms_is( HT_DMS_GROUP_POD_NAME, $id );

}

/**
 * Check if a particular item, or current page is a decision.
 *
 * @param 	bool $id Optional. ID of item to check or if false, the default, checks current page.
 *
 * @return bool
 *
 * @since 0.0.3
 */
function ht_dms_is_decision( $id = false ) {

	return ht_dms_is( HT_DMS_DECISION_POD_NAME, $id );

}

/**
 * Check if a particular item, or current page is a task.
 *
 * @param 	bool $id Optional. ID of item to check or if false, the default, checks current page.
 *
 * @return bool
 *
 * @since 0.0.3
 */
function ht_dms_is_task( $id = false ) {

	return ht_dms_is( HT_DMS_TASK_POD_NAME, $id );

}

/**
 * Check if a particular item, or current page is a notification.
 *
 * @param 	bool $id Optional. ID of item to check or if false, the default, checks current page.
 *
 * @return bool
 *
 * @since 0.0.3
 */
function ht_dms_is_notification( $id = false ) {

	return ht_dms_is( HT_DMS_NOTIFICATION_POD_NAME, $id );

}

/**
 * URL for preferences view
 *
 * @return string
 *
 * @since 0.0.3
 */
function ht_dms_preferences_url() {

	return ht_dms_ui()->output_elements()->action_append( ht_dms_home(), 'preferences' );

}

/**
 * URL for user profile view/edit
 *
 * @param int $uID Optional. User ID to view profile for. If 0, current user profile edit returned.
 *
 * @return string
 *
 * @since 0.0.3
 */
function ht_dms_profile_url( $uID = 0 ) {

	return ht_dms_ui()->output_elements()->action_append( ht_dms_home(), 'user-profile', $uID );

}

/**
 * Get URL for notifications view
 *
 * @return string
 *
 * @since 0.0.3
 */
function ht_dms_notifications_url() {

	return ht_dms_ui()->output_elements()->action_append( ht_dms_home(), 'notifications' );

}

/**
 * Add an icon to a string
 *
 * @param string $string String to add icon to
 * @param string $icon Icon to add. Must be defined in ht_dms_ui()->build_elements()->icon()
 * @param string $extra_class Optional. Additional class to add to icon.
 *
 * @return string
 *
 * @since 0.0.3
 */
function ht_dms_add_icon( $string, $icon, $extra_class = false ) {
	if ( is_array( $icon ) ) {
		foreach ( $icon as $i  ) {
			$icons[] = ht_dms_ui()->build_elements()->icon( $i );
		}

		if ( is_array( $icons ) ) {
			$string = implode( $icons ) . $string;
		}

	}
	else{
		$icon = ht_dms_ui()->build_elements()->icon( $icon, $extra_class );

		$string = $icon . $string;
	}

	return $string;

}

function ht_dms_content_types() {
	return array( HT_DMS_DECISION_POD_NAME, HT_DMS_TASK_POD_NAME, HT_DMS_GROUP_POD_NAME, HT_DMS_ORGANIZATION_POD_NAME );
}



function ht_dms_print_r2( $val) {
	echo '<pre style="background-color:#cccccc">';
	print_r($val);
	echo  '</pre>';
}

if ( ! function_exists( 'print_r2' ) ) {
	function print_r2( $val ){
		ht_dms_print_r2( $val );
	}
}

function ht_dms_print_x2( $val) {
	echo '<pre style="background-color:#cccccc">';
	var_export( $val);
	echo  '</pre>';
}

if ( ! function_exists('print_x2') ) {
	function print_x2($val){
		ht_dms_print_x2( $val );
	}
}

if ( ! function_exists( 'print_c3' ) ) {
	function print_c3( $val, $r = true ) {
		ht_dms_print_c3( $val, $r );
	}
}

function ht_dms_print_c3( $val, $r = true  ) {
	if ( ! is_null( $val ) && $val !== false ) {
		if ( $r ) {
			echo ht_dms_print_r2( $val );

		}
		else {
			ht_dms_print_x2( $val );
		}

	}
	else {
		var_dump( $val );
	}
}

/**
 * Log Errors to debug log
 */
if ( ! function_exists( 'log' ) ) {
	function log ( $log )  {
		ht_dms_log( $log );
	}

}

function ht_dms_log() {
	if ( is_array( $log ) || is_object( $log ) ) {
		error_log( print_r( $log, true ) );
	} else {
		error_log( $log );
	}

}

/**
 * Error function
 *
 * @uses wp_die()
 *
 * @param string| null $error 	Optional. Error message to show.
 *
 * @param string| null $method	Optional. Method generating the error.
 *
 * @since 0.0.1
 */
function ht_dms_error( $error = null, $method = null ) {
	$app_name = 'HolotTree DMS';
	if ( is_null( $error )  && is_null( $method ) ) {
		$trace = debug_backtrace();
		$caller = array_shift( $trace );

		$in =  $caller[ 'function' ];
		if ( isset( $caller[ 'method' ] ) ) {
			$in =  $caller[ 'method' ];
		}
		if ( isset($caller['class'] ) ) {
			$in .= $caller['class'];
		}
		$message = __(
			sprintf( '%0s encountered an error in %1s line %2s', $app_name, $caller[ 'file' ], $caller[ 'line' ] ),
			'ht_dms'
		);
	}
	elseif ( is_null( $error )  && ! is_null( $method ) ) {
		$message= __(
			sprintf( '%1s encountered an in %2s', $app_name, $method ),
			'holotree' );
	}
	elseif ( ! is_null( $error )  && ! is_null( $method ) ) {
		$message = __(
			sprintf( '%1s %2s', $error, $method ),
			'holotree' )
		;
	}
	else {
		$message = $error;
	}

	if ( function_exists( 'pods_error' ) ) {
		pods_error( $message );
	}
	else {
		wp_die( $message );
	}
}



/**
 * For creating links with optional button, class and ID.
 *
 * Wrapper for ui/elements/link()
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
function ht_dms_link( $id, $type = 'permalink', $text= 'view', $title= null, $button = false, $classes = false, $link_id = false, $append = false  ) {
	return ht_dms_ui()->elements()->link( $id, $type, $text, $title, $button, $classes, $link_id, $append );

}




/**
 * For safely appending variables to urls. By default in the dms_action={action}&dms_id={id} pattern.
 *
 * @param 	string			$url	Base URL
 * @param 	string|array	$action	Variable to append. If string should be value for 'dms_action'. To set action and value pass array.
 *   Array arguments {
 * 		@type string var 	The name of the variable to append.
 * 		@type string value	The value of the variable.
 *   }
 * @param int               $id     ID of post.
 * @param bool  $add_nonce Whether to add a nonce to the URL or not.
 *
 * @return 	string					URL
 *
 * @since 	0.0.1
 */
function ht_dms_action_append( $url, $action, $id = false, $add_nonce = true ) {

	return ht_dms_ui()->elements()->action_append( $url, $action, $id, $add_nonce );
}


/**
 * Get the content type.
 *
 * @param bool $specific_type Optional. If true, the default name of content type is returned.  If false the type of content (ie post type, taxonomy) is returned.
 *
 * @return bool|string
 */
function ht_dms_get_content_type( $specific_type = true ) {
	$queried_object = get_queried_object();
	if ( $queried_object ) {

		// Post Type Singular
		if ( isset( $queried_object->post_type ) ) {
			$type = 'post_type';
			$specific_type = $queried_object->post_type;
		}
		// Term Archive
		elseif ( isset( $queried_object->taxonomy ) ) {
			$type = 'taxonomy';
			$specific_type = $queried_object->taxonomy;
		}
		// Author Archive
		elseif ( isset( $queried_object->user_login ) ) {
			$type = $specific_type = 'user';
		}
		// Post Type Archive
		elseif ( isset( $queried_object->public ) && isset( $queried_object->name ) ) {
			$type = $queried_object->name;
			$specific_type = $queried_object->name;

		}

		if ( $specific_type && isset( $type ) &&$type  ) {
			return $specific_type;
		}

		return $type;

	}

}

/**
 * Check if a value is an integer or a string representing an integer. If so typecast as int and return
 *
 * @param 	mixed $int Value to check
 *
 * @return 	int|bool
 *
 * @since 0.0.3
 */
function ht_dms_integer( $int ) {
	if ( is_int( $int ) || intval( $int  ) > 0 ){

		return (int) $int;

	}
}

function ht_dms_reset_consensus( $id ) {

	return ht_dms_consensus_class()->reset( $id );

}



/**
 * Login link
 *
 * @since 0.0.3
 *
 * @param bool $button
 *
 * @return null|string
 */
function ht_dms_login_link( $button = true, $text = false ) {
    if ( ! $text ) {
        $text = __('Login To HoloTree', 'holotree');
    }

	return ht_dms_link( wp_login_url(), '', $text, $text, $button );

}

/**
 * Login link
 *
 * @since 0.0.3
 *
 * @param bool $button
 *
 * @return null|string
 */
function ht_dms_registeration_link( $button = true ) {
	$text = __( 'Register For HoloTree', 'holotree' );

	return ht_dms_link( wp_registration_url(), '', $text, $text, $button );

}

function ht_dms_lost_password_link( $button = true ) {
	$text = __( 'Reset Password', 'holotree' );

	return ht_dms_link( wp_lostpassword_url(), '', $text, $text, $button );

}


/**
 * Generate or check an invite code
 *
 *
 * @since 0.0.3
 *
 * @param bool $generate
 * @param      $email
 * @param bool $oID
 * @param bool $code
 *
 * @return string The code if generating or the organization ID contained in the code if checking, and checks pass.
 */
function ht_dms_invite_code( $generate = true, $email, $oID = false, $code = false  ) {
	if ( $generate && ! is_array( $generate )  ) {

		return ht_dms\helper\user\registration\codes::create_invite_code( $oID, $email );

	}
	else{

		return ht_dms\helper\user\registration\codes::verify_code( $email, $code );

	}

}

if ( ! function_exists( 'get_avatar' ) ) :
	/**
	 * Replacement for built-in get_avatar to use our fallback avatar & Pods Avatar properly.
	 *
	 * @since 0.1.0
	 *
	 * @param int|string|object $id_or_email A user ID,  email address, or comment object
	 * @param int $size Size of the avatar image
	 * @param string $default URL to a default image to use if no avatar is available
	 * @param string $alt Alternative text to use in image tag. Defaults to blank
	 *
	 * @return string <img> tag for the user's avatar
	 */
	function get_avatar( $id_or_email, $size = '96', $default = '', $alt = false ) {
		if ( ! $default ) {
			$default = ht_dms_fallback_avatar();
		}

		if ( ! $alt ) {
			$alt = __( 'User Avatar', 'ht_dms' );
		}

		if ( is_object( $id_or_email ) ) {
			if ( false == ( $id = pods_v( 'user_id', $id_or_email, false, true ) ) ) {
				$id = 0;
			}

		} elseif ( ht_dms_integer( $id_or_email ) ) {
			$id = $id_or_email;
		} else {
			if ( is_email( $id_or_email ) ) {
				$id = get_user_by( 'email', $id_or_email );
			} else {
				ht_dms_error();
			}
		}


		$avatar = get_user_meta( $id, 'avatar', true );
		if ( $avatar ) {
			return pods_image( $avatar, array( $size, $size ) );
		} else {
			return sprintf( '<img src="%1s" width="%2s" height="%3s" alt="%4s" />',
				esc_url( $default ), esc_attr( $size ), esc_attr( $size ), esc_attr( $alt )
			);
		}
	}
endif;

/**
 * Get the members details from a sorted consensus
 *
 *
 * @since 0.1.0
 * 
 * @param $sorted_consensus
 *
 * @return array
 */
function ht_dms_sorted_consensus_details( $sorted_consensus ) {

	return pods_v( 'details', $sorted_consensus, array() );

}

/**
 * Verify the dms-action nonce
 *
 * @since 0.1.0
 *
 * @param string $method Optional. Transport method being used. Default is 'get'. Only other valid option is 'post'.
 *
 * @return bool
 */
function ht_dms_verify_action_nonce( $method = 'get' ) {
	$nonce = pods_v_sanitized( ht_dms_ui()->output_elements()->action_nonce_name, $method  );
	$verify = wp_verify_nonce( $nonce, ht_dms_ui()->output_elements()->action_nonce_action );

	return $verify;
}

/**
 * Run Caldera Forms Input
 *
 * @since 0.1.0
 */
function ht_dms_caldera_import() {

	echo \ht_dms\helper\caldera\import::import_forms();

}

/**
 * Determine if we are using tasks or not.
 *
 * @return bool True if HT_DMS_TASK_MODE is defined & true
 *
 * @since 0.1.0
 */
function ht_dms_task_mode() {
	if ( defined( 'HT_DMS_TASK_MODE' ) && HT_DMS_TASK_MODE ) {
		return true;

	}
}

/**
 * Process invite code validation.
 *
 * @since 0.1.0
 *
 * @uses 'wp_ajax_nopriv_ht_dms_validate_invite_code'
 */
add_action( 'wp_ajax_ht_dms_validate_invite_code', 'ht_dms_validate_invite_code' );
add_action( 'wp_ajax_nopriv_ht_dms_validate_invite_code', 'ht_dms_validate_invite_code' );
function ht_dms_validate_invite_code() {
	if ( check_ajax_referer( 'ht-dms-login', 'nonce' ) ) {
		$code = pods_v_sanitized( 'code', 'post' );
		$email = pods_v_sanitized( 'email', 'post'  );
		if ( ! is_null( $code ) && ht_dms_invite_code( false, $email, false, $code ) ) {
			wp_die( 1 );
		}

	}
	wp_die( 0 );
}

