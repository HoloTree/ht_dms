<?php
/**
 * Setup rewrite rules
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2015 Josh Pollock
 */

namespace ht_dms\helper\urls;


class add implements \Action_Hook_SubscriberInterface{

	public static function get_actions() {
		return array(
			'init' => 'add_tags',
			'query_vars' => 'query_vars'
		);

	}

	public static function add_tags() {
		add_rewrite_tag( '%ht_dms_group_name%', '([^&]+)' );
		add_rewrite_tag( '%ht_dms_organization_name%', '([^&]+)');

		add_rewrite_rule('^ht-dms/([^/]*)/([^/]*)/?','index.php?post_type=' . HT_DMS_ORGANIZATION_POD_NAME . '&ht_dms_organization_name=$matches[1]','top');

		//add_rewrite_rule('^ht-dms/([^/]*)/([^/]*)/?','index.php?post_type=' . HT_DMS_GROUP_POD_NAME . '&ht_dms_organization_name=$matches[1]&ht_dms_group_name=$matches[1]','top');

		//add_rewrite_rule('^ht-dms/([^/]*)/([^/]*)/?','index.php?post_type= ' . HT_DMS_DECISION_POD_NAME . '&ht_dms_organization_name=$matches[1]&ht_dms_group_name=$matches[1]&ht_dms_decision_name=$matches[1]','top');


	}

	public static function query_vars( $qvars ) {
		$new_vars = array(
			'ht_dms_group_name',
			'ht_dms_organization_name'
		);

		foreach( $new_vars as $var ) {
			if ( ! in_array( $var, $qvars ) ) {
				$qvars[ ] = $var;
			}

		}

		return $qvars;

	}

	/**
	 * Holds the instance of this class.
	 *
	 * @since  0.3.0
	 *
	 * @access private
	 * @var    object
	 */
	private static $instance;


	/**
	 * Returns the instance.
	 *
	 * @since  0.3.0
	 *
	 * @return add_rules|object
	 */
	public static function init() {

		if ( !self::$instance )
			self::$instance = new self;

		return self::$instance;

	}

}
