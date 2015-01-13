<?php
/**
 * Consensus system for HoloTree DMS
 *
 * @package   @holotree_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link
 * @copyright 2014 Josh Pollock
 */

/**
 * In consensus_ui arrays:
 *
 * 0 represents no opinion expressed
 * 1 represents acceptance
 * 2 represents objection
 */
namespace ht_dms\helper;

use ht_dms\ui\build\elements\consensus_ui;

class consensus  {

	/**
	 * Get a consensus_ui or create one if it does not exist.
	 *
	 * @param 	int 	$dID ID of decision to get/ create for.
	 *
	 * @return 	array		The consensus_ui array.
	 *
	 * @since 	0.0.1
	 */
	public function consensus( $dID ) {
		$consensus = $this->get( $dID );
		if ( $consensus === false || ! is_array( $consensus ) || empty( $consensus) ) {
			$this->create( $dID );
			$consensus = $this->consensus( $dID );

		}

		return $consensus;

	}

	/**
	 * Create a new consensus_ui
	 *
	 * @param 	int 		$dID 		ID of decision.
	 * @param 	null|obj	$obj		Optional. Decision single object.
	 * @param	bool		$dont_set	Optional. If true, consensus_ui array will be returned <em>unserialized</em> instead of saving it to DB. Default is false
	 * @param   bool    $return_consensus Optional. If true, the consensus_ui array is returned. If false, the default, the decision ID is returned.
	 *
	 * @return 	int|array						ID of decision or the consensus_ui array.
	 *
	 * @since 	0.0.1
	 */
	function create( $dID, $obj = null, $dont_set = false, $return_consensus = false ) {
		if ( ! $dID || ! ht_dms_is_decision( $dID ) ) {
			return;
		}

		$obj = ht_dms_decision( $dID, $obj );
		if ( !is_object( $obj ) ) {
			ht_dms_error( 'No object in', __METHOD__ );
		}

		$gID = (int) $obj->display( 'group.ID' );

		$users = ht_dms_group_class()->all_members( $gID  );


		if ( is_array( $users ) ) {
			$consensus = array ();
			foreach ( $users as $user ) {
				$consensus[ $user ] = array(
					'id'		=> $user,
					'value'		=> 0
				);
			}


			if ( is_array( $consensus ) ) {
				if ( $dont_set ) {

					return $consensus;

				}
				else {
					$id = $obj->save( 'consensus_ui', serialize( $consensus ) );
					$id = $obj->save( 'decision_status', 'new' );
					if ( $return_consensus ) {
						return $consensus;

					}
					else {
						return $id;

					}

				}
			}
		}
		else {
			ht_dms_error( __METHOD__, print_c3( array( 'obj->id()' => $obj->id(), 'users_array' => $users, 'group_id' => $gID ) ) );
		}

	}

	/**
	 * Get current consensus_ui array for a decision.
	 *
	 * @param int $dID Decision ID
	 *
	 * @return array $consensus_ui	Consensus Array
	 *
	 * @since 0.0.1
	 */
	function get( $dID, $obj = null, $unserialized = true ) {

		$obj       = ht_dms_decision( $dID, $obj );
		$consensus = $obj->field( 'consensus' );
		if ( $unserialized ) {
			$consensus = maybe_unserialize( $consensus );

		}

		return $consensus;

	}

	/**
	 * Modifies a consensus_ui array.
	 *
	 * Note: Does not save array or anyway modify DB.
	 *
	 * @param int      	$dID		ID of decision.
	 * @param int		$new_value	Value to set.
	 * @param null|int	$uID		Optional. User ID. Defaults to current user ID.
	 *
	 * @return array	The modified consensus_ui array.
	 *
	 * @since 0.0.1
	 */
	private function modify ( $dID, $new_value, $uID = null ) {
		if ( $new_value > -1 && $new_value < 4 ) {
			$uID =  $this->null_user( $uID );
			$uID = (int) $uID;
			$consensus = $this->get( $dID );
			if ( is_array( $consensus) ) {

				if ( isset( $consensus[ (int) $uID ] ) ) {
					$consensus[ (int) $uID ] = array (
						'id'    => $uID,
						'value' => $new_value,
					);
				}



				return $consensus;

			}

		}

	}



	/**
	 * Update the consensus_ui.
	 *
	 * @param int		$dID 		ID of decision.
	 * @param int|array	$value		Value to change in array or a whole consensus_ui array to write.
	 * @param null		$uID		Optional. User to change value for. Defaults to current user ID.
	 *
	 * @return int 	$id			ID id decision whose consensus_ui was updated.
	 *
	 * @TODO Should this really return $id?
	 *
	 * @since 0.0.1
	 */
	function update( $dID, $value, $uID = null ) {
		if ( !is_array( $value ) ) {
			$uID = $this->null_user( $uID );
			$value = $this->modify( $dID, $value, $uID );
		}

		return \ht_dms\helper\consensus\update::save( $value, $dID );

	}


	/**
	 * What the status of a decision should be, based on the consensus_ui array.
	 *
	 * Note: May not be the actual status saved in the decision_status field. This is a check of the consensus_ui array itself, not that field.
	 *
	 * @param array	$consensus A consensus_ui array
	 *
	 * @return string
	 *
	 * @since 0.0.1
	 */
	function status( $consensus ) {
		if ( is_array( $consensus ) ) {
			$status = 'new';
			$acceptable = 0;
			foreach ( $consensus as $value ) {
				if ( $value[ 'value' ] === 0 || $value[ 'value' ] === 2 ) {
					$acceptable = 'nope';
				}

				if ( $value[ 'value' ] === 2 ) {
					$status = 'blocked';
					break;
				}


			}
			if ( is_int( $acceptable  ) ) {
				$status = 'passed';
			}

			return $status;
		}

	}

	/**
	 * Calculates what would be the result of a user taking any action on a consensus_ui
	 *
	 * @param int $dID Decision ID to make change on.
	 * @param int $uID User ID of user to test based on.
	 *
	 * @return array
	 *
	 * @since 0.0.3
	 */
	function possible_changes( $dID, $uID ) {
		$actual_status = $this->status( $this->get( $dID ) );
		$possible = false;

		if ( in_array( $actual_status, array( 'new', 'blocked' ) ) ) {
			for ( $i = 0; $i <= 2; $i ++ ) {
				$ic = $this->modify( $dID, $i, $uID );

				$possible[ $i ] = $this->status( $ic );
			}

			//@todo make response translation friendly
			//@todo figure out how given that $this->modify can't be translation friendly as it returns values for the field.
		}
		else {
		}

		$possible_changes =  array(
			'current_status' => $actual_status,
			'possible_results' => $possible,
		);

		return $possible_changes;

	}

	/**
	 * Sort a consensus_ui by status
	 *
	 * @param int|array $dID Either a decision ID or a consensus_ui array.
	 *
	 * @return array sorted consensus_ui [status_code] => array( $uIDs)
	 *
	 * @since 0.0.3
	 */
	function sort_consensus( $dID, $js_output = false ) {
		if ( ! $dID || ! ht_dms_is_decision( $dID ) ) {
			return false;
		}

		if ( ! is_array( $dID ) ) {
			$consensus = $this->consensus( $dID );
		}
		else {
			$consensus = $dID;
		}

		if ( ! is_array( $consensus ) ) {
			$consensus = $this->create( $dID, false, true );
		}

		if ( is_wp_error( $consensus ) ) {
			return false;
		}

		reset( $consensus );
		$first_key = key( $consensus );

		if ( ! isset( $consensus[$first_key][ 'value' ] ) ) {
			return '';
		}

		$user_value = wp_list_pluck( $consensus, 'value' );

		$statuses = array();
		if ( is_array( $consensus ) ) {
			foreach ( $user_value as $uID => $value ) {
				$statuses[ $value ][ ] = $uID;
			}

			for ( $i = 0; $i <= 2; $i ++ ) {
				if ( isset( $statuses[ $i ] ) ) {
					$count[ $i ] = count( $statuses[ $i ] );
				}
				else {
					$count[ $i ] = 0;
					$statuses[ $i ] = array ();
				}
			}

			if ( ! $js_output ) {
				return $statuses;
			}
			else {
				$build_elements =ht_dms_ui()->build_elements();
				$details = array();
				$consensus_status = array();
				if ( is_array( $statuses ) ) {
					foreach ( $statuses as $status => $user_ids ) {
						$consensus_status[ $status ] = $user_ids;
						$count[ $status ] = count( $user_ids );

						foreach( $user_ids as $uID ) {
							$user = $build_elements->member_details( $uID );
							$details[ $status ][] = $user[0];

						}

					}

				}

				$consensus_status[ 'details' ] = json_encode( $details );

				for ( $i=0; $i<=2; $i++ ) {

					$consensus_status[ 'headers' ] = consensus_ui::consensus_headers( $count );

				}

				return $consensus_status;

			}

		}

	}

	function reset( $dID ) {
		$obj  = ht_dms_decision( $dID );
		$consensus = $obj->save( 'consensus_ui', '' );
		$this->consensus( $dID );
		$obj->save( array( 'decision_status', 'new' ) );
	}


	/**
	 * Convert null value for user ID to current user ID.
	 *
	 * @param 	int|null $uID	Optional. A user ID.
	 *
	 * @return 	int				Same as input or current user ID if input is null.
	 *
	 * @since 	0.0.1
	 */
	function null_user( $uID ) {

		return ht_dms_null_user( $uID );

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
