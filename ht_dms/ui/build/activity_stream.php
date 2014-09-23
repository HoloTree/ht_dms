<?php
/**
 * Recent activity for network/user/organization/group
 *
 * @package   @TODO
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\ui\build;


class activity_stream {

	function __( $type, $id ) {
		add_filter( 'ht_dms_before_magic_templates', array( $this, 'before' ), 10, 2 );
		add_filter( 'ht_dms_before_magic_templates', array( $this, 'after' ), 10, 2 );

	}

	function network(  ) {
		return $this->data( null );
	}

	function user( $id ) {
		return 'WORKING ON IT!';
	}

	function organization( $id ) {
		return 'WORKING ON IT!';
	}

	function group( $id ) {
		return $this->data( $id );

	}

	function placeholder() {
		return 'WORKING ON IT!';
	}

	function before( $content, $view ) {
		holotree_error();
		if ( $view === 'decision_activity_stream.php' ) {
			$content = 'before';
		}

		return $content;
	}

	function after( $content, $view ) {
		if ( $view === 'decision_activity_stream.php' ) {
			$content = 'after';
		}

		return $content;
	}

	private function data( $id ) {
		$return = array( 'view' => 'decision_activity_stream.php' );

		return ht_dms_ui()->views()->active_decisions( null, $id, get_current_user_id(), 5, $return );

	}
} 
