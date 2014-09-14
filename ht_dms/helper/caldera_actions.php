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

namespace ht_dms\helper;


class caldera_actions {
	public static $join_group_form_id = 'CF54138690b504b';
	public static $leave_group_form_id = 'CF5413964215412';
	public static $group_pending_form_id = 'CF5413972657523';

	public static $decision_actions_form_id = 'CF5411fb087123d';

	private $decision_actions_field = 'fld_738259';
	private $force_debug = true;

	function __construct() {
		add_action( 'ht_dms_decision_action_process', array( $this, 'decision_actions' ) );
		add_action( 'ht_dms_group_process', array( $this, 'group' ) );

		add_filter( 'caldera_forms_render_get_field_type-dropdown', array( $this, 'decision_action_options' ), 5, 2 );

		add_action( 'ht_dms_pending_membership_process', array( $this, 'pending_process' ) );
		add_filter(  'caldera_forms_render_get_field_type-checkbox', array( $this, 'pending_membership_fields' ), 1, 2 );


	}


	function decision_actions( $data ) {
		$action = pods_v( 'action', $data, false, true );
		$dID = pods_v( 'did', $data, false, true );


		$d = holotree_decision_class();
		$uID = holotree_dms_ui();
		if ( $action && $dID ) {
			$action = strtolower( $action );

			if ( 'accept' === $action ) {
				$id = $d->accept( $dID, $uID );
			}

			if( $action === 'object' ) {
				$id = $d->block( $dID, $uID );
			}

		}

		if ( $this->force_debug || HT_DEV_MODE ) {
			if ( isset( $id ) ) {
				$data[ 'id' ] = $id;
			}
			update_option( __FUNCTION__, $data );

		}

	}

	function decision_action_options( $field, $form ) {
		if ( $form[ 'ID' ] !== self::$decision_actions_form_id ) {
			return $field;
		}

		global $post;
		$id = $post->ID;
		$obj = holotree_decision( $id  );
		$d = holotree_decision_class();


		if ( $field[ 'ID' ] == $this->decision_actions_field ) {
			if ( $d->is_proposed_change( $id, $obj ) ) {
				unset( $field['config']['option'][ $this->decision_option_id( 'accept' ) ] );
			}
			else {
				unset( $field['config']['option'][ $this->decision_option_id( 'accept-change' ) ] );
			}

			if ( ! $d->is_new( $id, $obj ) && ! $d->is_proposed_change( $id, $obj ) ) {
				foreach( $this->decision_option_id( '', true ) as $option ) {
					if ( $option !== $this->decision_option_id( 'respond' ) ) {
						unset( $field['config']['option'][ $option ] );
					}

				}
			}

			if ( ! $d->is_proposed_change( $id ) ) {
				unset( $field[ 'config' ][ 'option' ][ $this->decision_option_id( 'accept-change' ) ] );

			}

			if ( $d->is_blocked( $id ) ) {
				if ( $d->is_blocking( $id ) ){
					unset( $field[ 'config' ][ 'option' ][ $this->decision_option_id( 'object' ) ] );

				}

			}
			else {
				unset( $field[ 'config'][ 'option' ][ $this->decision_option_id( 'remove-objection' ) ] );

			}



		}


		return $field;

	}



	function decision_option_id( $value, $return_all = false ) {

		$options = array (
			'opt1791757' =>
				array (
					'value' => 'respond',
					'label' => 'Respond',
				),
			'opt1957683' =>
				array (
					'value' => 'accept',
					'label' => 'Accept',
				),
			'opt1353927' =>
				array (
					'value' => 'object',
					'label' => 'Object',
				),
			'opt1289816' =>
				array (
					'value' => 'remove-objection',
					'label' => 'Remove Objection',
				),
			'opt2001124' =>
				array (
					'value' => 'propose-modify',
					'label' => 'Propose Modification',
				),
			'opt1639315' =>
				array (
					'value' => 'accept-change',
					'label' => 'Accept Proposed Modification',
				),
		);



		if ( $return_all ) {
			return $options;
		}

		$values = wp_list_pluck( $options, 'value' );
		$values = array_flip( $values );

		return pods_v( $value, $values, false, true );

	}

	function group( $data ) {
		$id = pods_v( 'gid', $data, false, true );
		if ( $id ) {
			if ( array_key_exists( 'join_group', $data ) ) {
				holotree_group_class()->join( $id, get_current_user_id() );
			}
			elseif ( array_key_exists( 'leave_group', $data )  ) {
				holotree_group_class()->remove_member( $id, get_current_user_id() );
			}

		}

		if ( $this->force_debug || HT_DEV_MODE ) {
			if ( isset( $id ) ) {
				$data[ 'id' ] = $id;
			}

			update_option( __FUNCTION__, $data );

		}

	}

	function pending_process( $data ) {
		$gID = pods_v( 'gid', $data );
		if ( $gID  ) {
			$class          = holotree_group_class();

			$actions = array(
				'members_to_accept',
				'members_to_reject',
			);

			foreach( $actions as $action  ) {
				$members = pods_v( $action, $data );

				$approve = true;


				if ( is_array( $members ) ) {

					foreach ( $members as $member ) {

						if ( $action == 'members_to_reject' ) {
							$approve = false;
						}
						$class->pending( (int) $gID, $member, $approve );
					}
				}

			}

		}

		if ( $this->force_debug || HT_DEV_MODE ) {
			foreach( $actions as $action ) {
				$data[ $action ] = pods_v( $action, $data );
			}
			update_option( __FUNCTION__, $data );

		}
	}

	function pending_membership_fields( $field, $form ) {
		if ( self::$group_pending_form_id !== $form[ 'ID' ] ) {

			return $field;
		}
		else {



			global $post;
			if ( is_object( $post  ) && in_array( $field[ 'ID' ], array( $this->pending_member_fields( 'members_to_reject' ), $this->pending_member_fields( 'members_to_accept' ) ) ) ) {
				$gID = $post->ID;


				$pending = holotree_group( $gID )->field( 'pending_members.ID' );
				if ( is_array( $pending ) ) {
					foreach( $pending as $user ) {
						$label = $user;
						$user_data = get_userdata( $user );
						if ( is_object( $user_data ) ) {
							$label = $user_data->display_name;
						}
						$option[] = array(
							'value' => $user,
							'label' => $label,
						);
					}


				}

				if ( is_array( $option ) ) {
					$field[ 'config' ][ 'option' ] = $option;
				}



			}

			return $field;

		}

	}

	private function pending_member_fields( $field ) {
		$fields =  array(
			'members_to_reject' => 'fld_3704384',
			'members_to_accept' => 'fld_8545072',
		);

		return pods_v( $field, $fields );
	}



} 
