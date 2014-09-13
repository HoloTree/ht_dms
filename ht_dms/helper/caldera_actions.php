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
	public static $decision_actions_form_id = 'CF5411fb087123d';
	private $decision_actions_field = 'fld_738259';

	function __construct() {
		add_action( 'ht_dms_decision_action_process', array( $this, 'decision_actions' ) );
		add_filter( 'caldera_forms_render_get_field_type-dropdown', array( $this, 'decision_action_options' ), 5, 2 );
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

		if ( true || HT_DEV_MODE ) {
			if ( isset( $id ) ) {
				$data[ 'id' ] = $id;
			}
			update_option( __FUNCTION__, $data );

		}

	}

	function decision_action_options( $field, $form ) {
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



} 
