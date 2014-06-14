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

namespace ht_dms\helper;

class consensus {

	function __construct() {


	}


	/**
	 * Get a consensus or create one if it does not exist.
	 *
	 * @param 	int 	$dID ID of decision to get/ create for.
	 *
	 * @return 	array		The consensus array.
	 *
	 * @since 	0.0.1
	 */
	function consensus( $dID ) {
		if ( $this->get( $dID ) === false || !is_array( $this->get( $dID ) ) ) {
			$this->create( $dID );
		}

		return $this->get( $dID );

	}

	/**
	 * Create a new consensus
	 *
	 * @param 	int 		$dID 		ID of decision.
	 * @param 	null|obj	$obj		Optional. Decision single object.
	 * @param	bool		$dont_set	Optional. If true, consensus array will be returned <em>unserialized</em> instead of saving it to DB. Default is false
	 *
	 * @return 	int						ID of decision
	 *
	 * @since 	0.0.1
	 */
	function create( $dID, $obj = null, $dont_set = false ) {
		$obj = holotree_decision( $dID, false, false, $obj );
		if ( !is_object( $obj ) ) {
			holotree_error( 'No object in', __METHOD__ );
		}

		$group = $obj->field( 'group' );

		$g = holotree_group_class();
		if ( !is_object( $g ) ) {
			holotree_error( '$c->get $g!obj', __METHOD__ );
		}

		$users = $g->all_members( (int) $group[0][ 'ID'] );


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
					$id = $obj->save( 'consensus', serialize( $consensus ) );
					holotree_decision_class()->reset_cache( $id );

					return $id;

				}
			}
		}
		else {
			holotree_error( __LINE__, print_c3( array( $obj->id(), holotree_decision( $obj->id(), false, true ), $users, $group, $dont_set, (int) $group[0][ 'ID'])));
		}

	}

	/**
	 * Get current consensus array for a decision.
	 *
	 * @param int $dID Decision ID
	 *
	 * @return array $consensus	Consensus Array
	 *
	 * @since 0.0.1
	 */
	function get( $dID, $obj = null, $unserialized = true ) {
		$obj = holotree_decision_class()->null_obj( $dID, $obj );
		$consensus =  $obj->field( 'consensus' );
		if ( $unserialized ) {
			return unserialize( $consensus );

		}

		return $consensus;

	}

	/**
	 * Modifies a consensus array.
	 *
	 * Note: Does not save array or anyway modify DB.
	 *
	 * @param int      	$dID		ID of decision.
	 * @param int		$new_value	Value to set.
	 * @param null|int	$uID		Optional. User ID. Defaults to current user ID.
	 *
	 * @return array	The modified consensus array.
	 *
	 * @since 0.0.1
	 */
	private function modify ( $dID, $new_value, $uID = null ) {
		if ( $new_value > -1 && $new_value < 4 ) {
			$uID = $this->null_user( $uID );
			$consensus = $this->get( $dID );

			if ( is_array( $consensus) ) {
				foreach ( $consensus as $key => $value ) {
					if ( $value[ 'id' ] === $uID ) {
						unset( $consensus[ $key ] );
						break;
					}
				}
				$consensus[ $uID ] = array (
					'id'    => $uID,
					'value' => $new_value,
				);


				return $consensus;
			}
		}

	}



	/**
	 * Update the consensus.
	 *
	 * @param int		$dID 		ID of decision.
	 * @param int|array	$value		Value to change in array or a whole consensus array to write.
	 * @param null		$uID		Optional. User to change value for. Defaults to current user ID.
	 *
	 * @return int 	$id			ID id decision whose consensus was updated.
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


		if ( is_array( $value ) ) {
			$d = holotree_decision_class();
			$id = $d->update( $dID, 'consensus', $value );
			$status = $this->status( $value );
			$d->update( $dID, 'decision_status', $status );
			return $id;
		}

	}






	/**
	 * What the status of a decision should be, based on the consensus array.
	 *
	 * Note: May not be the actual status saved in the decision_status field. This is a check of the consensus array itself, not that field.
	 *
	 * @param array	$consensus A consensus array
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
	 * Convert null value for $uID to current user ID
	 *
	 * @param 	int	$uID	A user ID.
	 *
	 * @return 	int	$uID 	Same as input or current user ID.
	 *
	 * @since 0.0.1
	 */
	function null_user( $uID ) {
		$dms = holotree_dms_class();
		$uID = $dms->null_user( $uID );

		return $uID;
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