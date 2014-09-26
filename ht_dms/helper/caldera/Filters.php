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

namespace ht_dms\helper\caldera;


class Filters extends Forms implements \Filter_Hook_SubscriberInterface {

	/**
	 * Set filters
	 *
	 * @since 0.0.3
	 *
	 * @return array
	 */
	public  static function get_filters() {

		return array(
			'caldera_forms_render_form_attributes' => array( 'ajax_callbacks', 10, 2 ),

		);

	}

	function ajax_callbacks(  $form_attributes, $form ) {
		$form_id = pods_v( 'ID', $form );
		$callback = false;
		if ( in_array( $form_id, $this->membership_forms() ) ) {
			//$callbacks = 'reloadMembership';
		}

		if ( $form_id== $this->form_id( 'decision_actions_form' ) ) {
			$callback = 'reloadConsensus';
		}

		if ( $callback ) {
			$callbacks       = array ( 'data-callback' => $callback );
			$form_attributes = array_merge( $form_attributes, $callbacks );
		}

		return $form_attributes;
	}
} 
