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


		if ( $field[ 'ID' ] == 'fld_738259' ) {
			if ( $d->is_proposed_change( $id, $obj ) ) {
				unset( $field[ 'option' ][ $this->decision_option_id( 'accept' ) ] );
			}
			else {
				unset( $field[ 'option'][ $this->decision_option_id( 'accept-proposed-change' ) ] );
			}

			if ( ! $d->is_new( $id, $obj ) && !$d->is_proposed_change( $id, $obj ) ) {
				foreach( $this->decision_option_id( '', true ) as $option ) {
					if ( $option !== $this->decision_option_id( 'respond' ) ) {
						unset( $field[ 'option'][ $option ] );

					}

				}
			}



		}

		return $field;

	}

	function decision_option_id( $what, $return_all = false ) {

		$options = array(
			'opt3452387' => 'respond',
			'opt1586' => 'accept',
			'opt1782504' => 'accept-proposed-change',
			'opt1782504' => 'object',
			'opt2779288' => 'propose-change',

		);

		if ( $return_all ) {
			return $options;
		}

		$options = array_flip( $options );

		return pods_v( $what, $options, false, true );

	}
} 
