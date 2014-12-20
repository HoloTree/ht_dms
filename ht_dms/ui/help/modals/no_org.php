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


class no_org extends modals implements modal {

	public $trigger_id = 'no-org-message';
	/**
	 * The modal content.
	 *
	 * @since 0.3.0
	 *
	 * @return bool
	 */
	public function content() {
		$content[] = __( 'Looks like you are not a member of any organizations.', 'ht_dms' );
		$text = __( 'your preferences', 'ht_dms' );
		$link = sprintf( '<a href="%1s">%2s</a>', ht_dms_pref_link(), $text );
		$content[] = __( sprintf( 'Everything in HoloTree happens in organizations. You can create one in %1s,', $link ), 'ht_dms' );

		return	"<p>".implode('</span>,<span>', $content )."</p>";

	}

	/**
	 * Set condition by which this will be added.
	 *
	 * @since 0.3.0
	 *
	 * @return bool
	 */
	public function conditional() {
		if ( ht_dms_is( 'home' )   ) {
			$users_orgs_object = ht_dms_ui()->views()->users_organizations( null, null, 5, 'Pods'  );
			if ( is_object( $users_orgs_object ) && 1 > $users_orgs_object ) {
				return true;

			}

		}
	}

} 
