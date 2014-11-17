<?php
/**
 * Handles Changes To Consensuses
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\helper\consensus;


class update implements \Action_Hook_SubscriberInterface{

	/**
	 * Add actions
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	static function get_actions() {
		return array(
			'ht_dms_add_member_to_group' => array( 'add_to_consensus', 10, 2 )
		);
	}

	/**
	 * When a new member joins group, add to all active consensuses
	 *
	 * @since 0.1.0
	 *
	 * @uses 'ht_dms_add_member_to_group' action
	 *
	 * @param $uID
	 * @param $gID
	 *
	 */
	static function add_to_consensus( $uID, $gID ) {
		$decisions = ht_dms_group_class()->all_decisions( $gID, true );
		if ( is_array( $decisions ) ) {
			foreach ( $decisions as $decision ) {
				$consensus = self::add_member( $uID, ht_dms_consensus( $decision ) );
				self::save( $consensus, $decision  );
			}

		}

	}

	/**
	 * Save consensuses
	 *
	 * @since 0.1.0
	 *
	 * @param array $value Consensus array to save
	 * @param int $dID Decision to save into
	 *
	 * @return int Decision ID
	 */
	static function save( $value, $dID ) {
		if ( is_array( $value ) ) {
			$d = ht_dms_decision_class();
			$id = $d->update( $dID, 'consensus', $value );
			$status = ht_dms_consensus_class()->status( $value );
			$d->update( $dID, 'decision_status', $status );
			do_action( 'ht_dms_consensus_changed', $dID, $status );
			if ( $status === 'passed' ) {
				do_action( 'ht_dms_decision_passed', $dID );
			}

			return $id;
		}

	}

	/**
	 * Add a member to a consensus
	 *
	 * @since 0.1.0
	 *
	 * @param int $uID User ID to add
	 * @param array $consensus Consensus array to add to
	 *
	 * @return array|bool
	 */
	static function add_member( $uID, $consensus ) {
		if ( is_array( $consensus ) ) {
			$consensus[ $uID ] = array(
				'id'    => $uID,
				'value' => 0,
			);

			return $consensus;
		}
	}

	/**
	 * Holds the instance of this class.
	 *
	 * @since  0.1.0
	 * @access private
	 * @var    object
	 */
	private static $instance;

	/**
	 * Returns an instance of this class.
	 *
	 * @since  0.1.0
	 * @access public
	 *
	 * @return update|object
	 */
	public static function init() {

		if ( !self::$instance )
			self::$instance = new self;

		return self::$instance;
	}


} 
