<?php
/**
 * Does stuff on Caldera input & sets up Caldera fields
 *
 * @package   ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\helper\caldera;

/**
 * Class caldera_actions
 *
 * @package ht_dms\helper
 *
 * @since 0.0.3
 */
class Fields extends Forms implements \Hook_SubscriberInterface{


	private $decision_actions_field = 'fld_738259';
	private $force_debug = false;


	/**
	 * Set actions
	 *
	 * @since 0.0.3
	 *
	 * @return array
	 */
	public static function get_actions() {

		return array(
			'ht_dms_decision_action_process' => 'decision_actions',
			'ht_dms_group_process' => 'process_group',
			'ht_dms_pending_membership_process' => 'pending_process',
			'ht_dms_invite_new_user' => 'process_invite_new_user',
		);

	}

	/**
	 * Set filters
	 *
	 * @since 0.0.3
	 *
	 * @return array
	 */
	public static function get_filters() {

		return array(
			'caldera_forms_render_get_field_type-dropdown' => array( 'decision_action_options', 5, 2 ),
			'caldera_forms_render_get_field_type-checkbox' => array( 'pending_membership_fields', 1, 2 ),
		);

	}


	/**
	 * Runs when the decision action form is run and acts on its input
	 *
	 * @uses 'ht_dms_decision_action_process' action, which is generated by a Caldera Form
	 *
	 * @param $data
	 *
	 * @since 0.0.3
	 */
	function decision_actions( $data ) {
		$action = pods_v( 'action', $data, false, true );
		$dID = pods_v( 'did', $data, false, true );


		$d = ht_dms_decision_class();
		$uID = get_current_user_id();
		if ( $action && $dID ) {
			$action = strtolower( $action );

			if ( 'accept' === $action ) {
				$id = $d->accept( $dID, $uID );
			}

			if ( $action === 'remove-objection' ) {
				$id = $d->unblock( $dID, $uID );
			}

			if ( $action === 'object' ) {
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

	/**
	 * Sets options for decision action forms
	 *
	 *
	 * @uses 'caldera_forms_render_get_field_type-dropdown' filter
	 *
	 * @param $field
	 * @param $form
	 *
	 * @return array
	 *
	 * @since 0.0.3
	 */
	function decision_action_options( $field, $form ) {
		if ( $form[ 'ID' ] !== $this->decision_actions_form_id ) {
			return $field;
		}

		global $post;
		$id = $post->ID;
		$obj = ht_dms_decision( $id  );
		$d = ht_dms_decision_class();


		if ( $field[ 'ID' ] == $this->decision_actions_field ) {
			if ( $d->is_proposed_change( $id, $obj ) ) {
				unset( $field['config']['option'][ $this->decision_option_id( 'accept' ) ] );
			}
			else {
				unset( $field['config']['option'][ $this->decision_option_id( 'accept-change' ) ] );
			}

			if ( ! $d->is_new( $id, $obj ) && ! $d->is_proposed_change( $id, $obj ) && ! $d->is_blocked( $id ) ) {
				$options = $this->decision_option_id( '', true );
				$options = array_keys( $options );
				foreach( $options  as $option  ) {
					if ( $option !== $this->decision_option_id( 'respond' ) && isset( $field['config']['option'][ $option ] ) ) {
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

				if ( $d->is_blocking( $id ) ) {
					unset( $field[ 'config' ][ 'option' ][ $this->decision_option_id( 'accept' ) ] );
				}

			}
			else {
				unset( $field[ 'config'][ 'option' ][ $this->decision_option_id( 'remove-objection' ) ] );

			}



		}


		return $field;

	}

	/**
	 * Returns a Decision field action option's ID, given value, or the whole array of info.
	 *
	 *
	 * @param string   	$value 		Which value to return ID for.
	 * @param bool 		$return_all	Optional. If true returns the whole array
	 *
	 * @return array|string
	 *
	 * @access private
	 *
	 * @since 0.0.3
	 */
	private function decision_option_id( $value, $return_all = false ) {

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

	/**
	 * Process the add/leave group form input.
	 *
	 * @uses 'ht_dms_group_process' action, which is generated by Caldera
	 *
	 * @param $data
	 *
	 * @since 0.0.3
	 */
	function process_group( $data ) {
		$id = pods_v( 'gid', $data, false, true );
		if ( $id ) {
			if ( array_key_exists( 'join_group', $data ) ) {
				ht_dms_group_class()->join( $id, get_current_user_id() );
			}
			elseif ( array_key_exists( 'leave_group', $data )  ) {
				ht_dms_group_class()->remove_member( $id, get_current_user_id() );
			}

		}

		if ( $this->force_debug || HT_DEV_MODE ) {
			if ( isset( $id ) ) {
				$data[ 'id' ] = $id;
			}

			update_option( __FUNCTION__, $data );

		}

	}

	/**
	 * Processes the pending members field
	 *
	 * @uses 'ht_dms_pending_membership_process' action, which is generated by Caldera Forms
	 *
	 * @param $data
	 *
	 * @since 0.0.3
	 */
	function pending_process( $data ) {
		$gID = pods_v( 'gid', $data );
		if ( $gID  ) {
			$class          = ht_dms_group_class();

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

	/**
	 * Add pending members to the approve/reject form's fields.
	 *
	 * @uses 'caldera_forms_render_get_field_type-checkbox' filter
	 *
	 * @param $field
	 * @param $form
	 *
	 * @return array
	 *
	 * @since 0.0.3
	 */
	function pending_membership_fields( $field, $form ) {
		if ( $this->group_pending_form_id !== $form[ 'ID' ] ) {

			return $field;
		}
		else {



			global $post;
			if ( is_object( $post  ) && in_array( $field[ 'ID' ], array( $this->pending_member_fields( 'members_to_reject' ), $this->pending_member_fields( 'members_to_accept' ) ) ) ) {
				$gID = $post->ID;


				$pending = ht_dms_group( $gID )->field( 'pending_members.ID' );
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

	/**
	 * Get ID of field in pending member form.
	 *
	 * @param string $field Field to get
	 *
	 * @return string
	 *
	 * @since 0.0.3
	 */
	private function pending_member_fields( $field ) {
		$fields =  array(
			'members_to_reject' => 'fld_3704384',
			'members_to_accept' => 'fld_8545072',
		);

		return pods_v( $field, $fields );
		
	}

	/**
	 * Process new member invitation.
	 *
	 * @since 0.0.3
	 *
	 * @uses 'ht_dms_invite_new_user' action, generated by Caldera Forms processer
	 *
	 * @param $data
	 *
	 * @return bool|int
	 */
	function process_invite_new_user( $data ) {
		if ( $this->force_debug || HT_DEV_MODE ) {
			if ( isset( $id ) ) {
				$data[ 'id' ] = $id;
			}
			update_option( __FUNCTION__, $data );

		}

		$email = $data[ 'email_address' ];
		$oID = $data[ 'oid' ];
		$organization_name = get_the_title( $oID );
        $uID = email_exists( $email );
		if ( $uID ) {
            $new_user = false;
			$name = get_userdata( $uID );
			if ( is_object( $name ) ) {
				$name = $name->data->display_name;
				ht_dms_membership_class()->invite_existing( $oID, $oID, null, false );
			}

		}
        else {
            $new_user = true;
            $name = $data[ 'first_name' ] . ' ' .$data[ 'last_name' ];
        }

		$message = ht_dms_membership_class()->invite_message( $name, $oID, $organization_name, $email, $new_user );

		if ( $uID ) {
			$subject = sprintf( __( 'You have been invited to join the organization %1s', $organization_name ), 'holotree' );

			return ht_dms_notification_class()->new_notification( $uID, $subject, $message );

		}

		$subject = sprintf( __( 'You have been invited to join %1s on HoloTree.', $organization_name ), 'holotree' );

		return wp_mail( $email, $subject, $message );

	}


}
