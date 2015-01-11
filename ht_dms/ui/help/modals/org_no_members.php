<?php
/**
 * Modal Notification if org has no members
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\ui\help\modals;


use ht_dms\ui\build\baldrick\modals;

class org_no_members extends help implements modals {

	public static $action = 'org_no_members_modal';

	/**
	 * The modal content.
	 *
	 * @since 0.3.0
	 *
	 * @return bool
	 */
	public static function content() {
		$content[] = __( 'Looks like your organization has no members.', 'ht_dms' );
		$content[] = __( 'Use the "invite members" to invite new members to your organization. If they are not HoloTree users already they will receive an invitation to join.' , 'ht_dms' );

		return	"<p>".implode('</span>,<span>', $content )."</p>";

	}

	/**
	 * Set condition by which this will be added.
	 *
	 * @since 0.3.0
	 *
	 * @return bool
	 */
	public static function conditional() {
		global $post;
		if ( ht_dms_is_organization() && is_object( $post ) && false == ht_dms_membership_class()->total_members( $post->ID ) ) {
			return true;

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
