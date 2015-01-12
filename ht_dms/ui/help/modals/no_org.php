<?php
/**
 * Run a modal on home page if user has no organizations.
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\ui\help\modals;


use ht_dms\ui\build\baldrick\modals;

class no_org extends help implements modals, help_modal {

	/**
	 * The modal content.
	 *
	 * @since 0.3.0
	 *
	 * @return bool
	 */
	public static function content() {
		$content[] = __( 'Looks like you are not a member of any organizations.', 'ht_dms' );
		$text = __( 'your preferences', 'ht_dms' );
		$link = sprintf( '<a href="%1s">%2s</a>', ht_dms_pref_link(), $text );
		$content[] = __( sprintf( 'Everything in HoloTree happens in organizations. You can create one in %1s,', $link ), 'ht_dms' );

		$content ="<p>".implode('</p>,<p>', $content )."</p>";

		return $content;

	}

	/**
	 * Set condition by which this will be added.
	 *
	 * @since 0.3.0
	 *
	 * @return bool
	 */
	public static function conditional() {
		if ( ht_dms_is( 'home' )   ) {
			$users_orgs_object = ht_dms_ui()->views()->users_organizations( null, null, 5, 'Pods'  );
			if ( is_object( $users_orgs_object ) && 1 > $users_orgs_object ) {
				return true;

			}

		}

	}

	/**
	 * Holds the instance of this class.
	 *
	 * @since  0.3.0
	 * @access private
	 * @var    object
	 */
	private static $instance;

	/**
	 * Returns an instance of this class.
	 *
	 * @since  0.3.0
	 * @access public
	 *
	 */
	public static function init() {

		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;

	}

} 
