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

class activity {

	function __construct( $type, $id ) {

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



	private function data( $id ) {
		$return = array( 'view' => 'decision_activity_stream.php' );

		return ht_dms_ui()->views()->active_decisions( null, $id, get_current_user_id(), 5, $return );

	}
} 
