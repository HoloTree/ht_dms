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

namespace ht_dms\api\internal\actions;


class create_organization {
	/**
	 * Check the code
	 *
	 * @since 0.1.0
	 *
	 * @param $params
	 *
	 * @return bool
	 */
	public static function act( $params ) {
		//disabled for now, using pods-json-api instead.
		return false;
		if ( false != ( $invite_code = pods_v( 'invite', $params ) ) ) {
			$verify = new \ht_dms\helper\registration\organization\verify( $invite_code, false );
			if( $verify->check() ) {
				unset( $params[ 'invite' ] );
				ht_dms_organization_class()->create( $params );
			}
			else {
				return 550;
			}

		}

	}

	/**
	 * Params for this route
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public static function args() {
		return array(
			'description',
			'facilitators',
			'invite',
			'location',
			'members',
			'open_access',
			'owner',
			'post_title',
			'time_frame',
			'visibility',

		);

	}

	public static function method() {
		return 'POST';
	}

} 
