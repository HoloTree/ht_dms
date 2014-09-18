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
		$pods = array( HT_DMS_ORGANIZATION_NAME, HT_DMS_GROUP_CPT_NAME, HT_DMS_DECISION_CPT_NAME, HT_DMS_TASK_CT_NAME );
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

	$elements = ht_dms_ui()->build_elements();
	$items = array(
		ht_dms_home() => $elements->icon( 'home' ) . __( 'Home', 'holotree' ),
		ht_dms_preferences_url() => $elements->icon( 'preferences' ) . __( 'Preferences', 'ht_dms' ),
		ht_dms_notifications_url() => $elements->icon( 'notification' ) . __( 'Notifications', 'ht_dms' ),
		wp_logout_url() => $elements->icon( 'logout' ) .  __( 'Logout', 'holotree' ),
	);

	/**
	 * Override mini-menu items
	 *
	 * @param array $items Must be in form of  link => link text
	 *
	 * @since 0.0.2
	 */
	$items = apply_filters( 'ht_dms_mini_menu_items', $items );

	return $items;

}

function ht_dms_url( $id, $type = false ) {
	if ( $type === 'post-type' || in_array( $type, array(
			HT_DMS_DECISION_CPT_NAME,
			HT_DMS_GROUP_CPT_NAME,
			HT_DMS_ORGANIZATION_NAME,
		)
	) ) {
		return get_permalink( $id );
	}

	if ( false != ( $link = get_permalink( $id ) ) ) {

		return $link;

	}

	if ( $type === HT_DMS_TASK_CT_NAME || false == $link  ) {

		return get_term_link( $id, HT_DMS_TASK_CT_NAME );

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
 * @param $value
 *
 * @since 0.0.3
 */
function ht_dms_consensus_status_readable( $value ) {
	$accepted_values = array( 0,1,2 );
	if ( ! in_array( $value , $accepted_values ) ) {
		return false;
	}
	$values = array(
		'0' => __( 'No Response', 'holotree' ),
		'1' => __( 'Accepted', 'holotree' ),
		'2' => __( 'Blocked', 'holotree' ),
	);

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

	return $values[ $value ];

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
	$fallback = 'http://joshpress.net/jp-content/uploads/sites/10/2013/08/gus.jpg';
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
 * Pass null or user ID, returns same ID or current user ID if null
 *
 * @param null $uID
 *
 * @return int|null
 *
 * @since 0.0.3
 */
function ht_dms_null_user( $uID = null ) {

	if ( is_null( $uID ) ) {
		$uID = get_current_user_id();

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
		$url = holotree_action_append( ht_dms_home(), 'notifications', $id );
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
 * @param 	string    $content_type Content type to check for
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

	if (  in_array( $content_type, array(  HT_DMS_ORGANIZATION_NAME, HT_DMS_GROUP_CPT_NAME, HT_DMS_DECISION_CPT_NAME ) ) ) {

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

	if ( $content_type === HT_DMS_TASK_CT_NAME ) {
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

	if ( $content_type == HT_DMS_NOTIFICATION_NAME ) {
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

	return ht_dms_is( HT_DMS_ORGANIZATION_NAME, $id );

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

	return ht_dms_is( HT_DMS_GROUP_CPT_NAME, $id );

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

	return ht_dms_is( HT_DMS_DECISION_CPT_NAME, $id );

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

	return ht_dms_is( HT_DMS_TASK_CT_NAME, $id );

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

	return ht_dms_is( HT_DMS_NOTIFICATION_NAME, $id );

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
	return array( HT_DMS_DECISION_CPT_NAME, HT_DMS_TASK_CT_NAME, HT_DMS_GROUP_CPT_NAME, HT_DMS_ORGANIZATION_NAME );
}
