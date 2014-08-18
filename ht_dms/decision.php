<?php
/**
 * HoloTree DMS Decision Management
 *
 * @package   @holotree_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

////namespace ht_dms;


class decision extends dms {

	/**
	 * Set name of CPT this class is for.
	 *
	 * @var string
	 *
	 * @since 0.0.1
	 */
	public static $type = HT_DMS_DECISION_CPT_NAME;

	function __construct() {
		$type = $this->get_type();

		add_filter( "pods_api_post_save_pod_item_{$type}", array( $this, 'user_fix'), 11, 3 );
		add_filter( "ht_dms_{$type}_select_fields", array( $this, 'set_fields_to_loop' ) );
		add_filter( "ht_dms_{$type}_edit_form_fields", array( $this, 'form_fields' ), 10, 6 );

		add_filter( "ht_dms_{$type}_form_fix_jQuery", array( $this, 'form_fix_jQuery' ), 10, 2 );


		//add_filter( "pods_api_pre_save_pod_item_{$type}", array( $this, 'save_proposed_modification' ), 15, 3 );
	}

	/**
	 * Set the name of the CPT
	 *
	 * @param 	string 	$type
	 *
	 * @since 0.0.1
	 */
	function set_type( ) {
		return self::$type;
	}

	/**
	 * Holds the instance of this class.
	 *
	 *
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
			self::$instance = new decision();

		return self::$instance;

	}

	/**
	 * Loop that returns an array of a decisions field or an array of field arrays.
	 *
	 * @param int|null 	$id
	 * @param obj     	$obj
	 *
	 * @return mixed
	 */
	function field_loop( $id, $obj, $all = false ) {
		if ( is_null( $id ) ) {
			if ( $obj->total() > 0 ) {
				while ( $obj->fetch() ) {
					$fields = $obj->fields();
					foreach ( $fields as $key => $value ) {
						$decision[ $key ] = $obj->field( $key );
						$decisions[ $obj->id() ] = $decision;
					}
				}

				return $decisions;

			}
		}
		else {
			$fields = $obj->fields();
			$fields[ 'ID' ] = null;
			$fields[ 'id' ] = null;
			$fields[ 'post_title' ] = null;
			$fields[ 'post_author' ] = null;
			foreach ( $fields as $key => $value ) {
				$decision[ $key ] = $obj->field( $key );
			}

			if ( $obj->total() === 1 ) {
				$decisions = array();
				$decisions[ $obj->id() ] = $decision;
				return $decisions;
			}
			else {

				return $decision;

			}

		}

	}

	/**
	 * Set fields to loop when getting a fields and in $this->edit() and $all = false
	 *
	 * @return 	bool
	 *
	 * @since 	0.0.1
	 */
	function set_fields_to_loop( $fields ) {

		$fields = array(
			'id',
			'post_title',
			'decision_status',
			'decision_type',
			'decision_description',
			'manager',
			'proposed_by',
			'group',
			'consensus',
			'projects',
			'change_to'
		);

		return $fields;

	}

	/**
	 * Get decisions from a specific group
	 *
	 * @param int			$group_id	ID of group.
	 * @param bool 			$just_ids	Optional. If true will only return decision's ID. Default is False.
	 * @param int  			$limit		Optional. Number of decisions to return. Default is 5.
	 * @param null|string 	$status		Optional. Only return decisions of a given status. Default of null returns all statuses.
	 *
	 * @return array|bool	$decisions	An array of decision fields or FALSE if not where returned.
	 *
	 * @uses $this->pods_object()
	 * @uses $this->fields()
	 *
	 * @since 0.0.1
	 */
	function decisions_by_group( $group_id, $just_ids= false, $limit = 5, $status = null, $obj = null) {
		if ( is_null( $status ) ) {
			$params = array(
				'where' => ' group.ID = " ' . $group_id . ' " ',
				'limit' => $limit,
			);
		}
		else {
			$params = array(
				'where' => ' group.ID = " ' . $group_id . ' "  AND d.decision_status = "'.$status.'" ',
				'limit' => $limit,
			);
		}
		$decisions = false;
		$obj = $this->null_object( $obj, $params );

		if ( $obj->total() > 0 ) {
			$decisions = array();
			if ( $just_ids  ) {
				while ( $obj->fetch() ) {
					$decisions[] = $obj->ID();
				}

			}
			else {
				while ( $obj->fetch() ) {
					$fields = $this->fields_to_loop();
					foreach ( $fields as $field ) {
						$decision[ $field ] = $obj->field( $field );

					}
					$decisions[] = $decision;
				}
			}
		}

		return $decisions;

	}

	/**
	 * Modifies the form fields for decisions forms.
	 *
	 * @uses "ht_dms_ht_dms_decision_form_fields" filter
	 *
	 * @param $form_fields
	 * @param $new
	 * @param $id
	 * @param $obj
	 * @param $oID
	 * @param $uID
	 *
	 * @return array
	 *
	 * @since 0.0.2
	 */
	function form_fields( $form_fields, $new, $id, $obj, $oID, $uID  ) {
		if ( $new !== 'modify' ) {
			$defaults = $this->default_values( $id, $obj, $oID, $uID );


			$form_fields = array (
				'post_title'      => array (
					'label' => 'Decision Name',
				),
				'decision_description',
				'tasks',
				'decision_type'   => array (
					'default' => $defaults[ 'decision_type' ],
				),
				'decision_status' => array (
					'default' => $defaults[ 'status' ],
				),
				'manager'         => array (
					'default' => $defaults[ 'user_id' ],
				),
				'proposed_by'     => array (
					'default' => $defaults[ 'user_id' ],
				),
				'group'           => array (
					'default' => $defaults[ 'group_id' ],
				),
				'organization'    => array (
					'default' => $defaults[ 'organization_id' ],
				),
			);
			if ( ! $new ) {

				$form_fields[ 'change_to' ] = array (
					'default' => (string) $id,
				);
				$form_fields[ 'reason_for_change' ] = array ();
				$form_fields[ 'decision_type' ] = array (
					'default' => 'change',
				);

			}
		}

		return $form_fields;

	}

	/**
	 * Default values for a decision, to be localized into the js that sets them.
	 *
	 * return array
	 *
	 * @since 0.0.1
	 */
	function default_values( $id, $obj, $oID, $uID  ) {
		$obj = $this->null_object( $obj, $id );

		if ( get_post_type() === HT_DMS_GROUP_CPT_NAME ) {
			global $post;
			$gID = $post->ID;
		}
		else {
			$gID = (int) $obj->display( 'group.ID' );
		}

		if ( is_null( $oID ) ) {
			$oID = (int) $obj->display( 'organization.ID' );
			if ( empty( $oID ) ) {
				$gObj = holotree_group( $gID );
				$oID = (int) $gObj->display( 'organization.ID' );
				unset( $gOBj );
			}
		}

		$uID = $this->null_user( $uID );
		$values = array (
			'group_id' 			=> $gID,
			'user_id' 			=> $uID,
			'status'   			=> 'new',
			'decision_type' 	=> 'original',
			'organization_id'	=> $oID,
		);

		/**
		 * Override default values set for new decisions
		 *
		 * By default only values for group_id, user_id and status are set. If adding values for other fields, you must use 'ht_dms_new_decision_fields' filter to set them as defaults.
		 *
		 * @param array $fields	Parameters for pods::form
		 * @see http://pods.io/docs/code/pods/form/
		 *
		 * @since 0.0.1
		 */
		$values = apply_filters( 'ht_dms_default_decision_values', $values );

		return $values;


	}


	function form_fix_jQuery( $jquery, $new ) {

		if ( $new  ) {
			//fix for new decision form
			$jQuery = "//fix for new decision form
			$( 'li.pods-form-ui-row-name-decision-status, li.pods-form-ui-row-name-proposed-by, li.pods-form-ui-row-name-decision-type, li.pods-form-ui-row-name-group, li.pods-form-ui-row-name-change-to, li.pods-form-ui-row-name-organization' ).hide();
		";
		}
		else {
			//fix for propose-modify form
			$jQuery =  "//fix for propose-modify form
		$( 'li.pods-form-ui-row-name-decision-status, li.pods-form-ui-row-name-decision-type, li.pods-form-ui-row-name-manager, li.pods-form-ui-row-name-group, li.pods-form-ui-row-name-change-to' ).hide();
		";
		}

		return $jQuery;


	}


	/**
	 * Change a decision's consensus
	 *
	 * Fires the 'ht_dms_consensus_changed' and 'ht_dms_consensus_changed_{$id}' actions.
	 *
	 * @param 	int			$id		ID of decision to change the consensus of.
	 * @param 	string		$value	New value
	 * @param 	null|int	$uID	Optional. User to change value for, defaults to current user ID.
	 * @params	obj|null	$obj	Optional. Single decision Pods object.
	 *
	 * @return 	int					ID of decision.
	 *
	 * @since 	0.0.1
	 */
	function change_consensus( $id, $value, $uID = null, $obj = null ) {
		$uID = $this->null_user( $uID );

		$c = holotree_consensus_class();
		$c->update( $id, $value, $uID );

		$consensus = $c->get( $id );
		$proper_status = $c->status( $consensus );
		$status = $this->status( $id );

		if ( $proper_status !== $status ) {
			$obj = $this->null_object( $obj, $id );
			$id = $this->update( $id, 'decision_status', $proper_status, $obj );
		}

		do_action( 'ht_dms_consensus_changed' );
		do_action( 'ht_dms_consensus_changed'.$id );
		return $id;
	}

	/**
	 * Block a proposed decision.
	 *
	 * Decision must be open (ie decision_status is 'new' or 'blocked').
	 * Fires the 'ht_dms_new_block' and 'ht_dms_new_block_{$id}' actions.
	 *
	 * @param 	int 		$id 	ID of decision to block.
	 * @param 	int|null 	$uID	Optional. User to change value for. Defaults to current user ID.
	 * @params	obj|null	$obj	Optional. Single decision Pods object.
	 *
	 * @uses	$this->get_consensus
	 *
	 * @return 	int			ID of decision.
	 *
	 * @since 	0.0.1
	 */
	function block( $id, $uID = null, $obj = null ) {
		$status = $this->status( $id );
		if ( $status === 'new' || $status === 'blocked' ) {
			$obj = $this->null_object( $obj, $id );
			if ( !is_object( $obj ) ) {
				holotree_error( __METHOD__ );
			}
			$id = $this->change_consensus( $id, 2, $uID );

			do_action( 'ht_dms_new_block' );
			do_action( 'ht_dms_new_block_'.$id );

			return $id;
		}

	}

	/**
	 * Unblock a proposed decision.
	 *
	 * Decision must be open (ie decision_status is 'new' or 'blocked').
	 * Fires the 'ht_dms_new_unblock' and 'ht_dms_new_unblock{$id}' actions.
	 *
	 * @param 	int 		$id 	ID of decision to block.
	 * @param 	int|null 	$uID	Optional. User to change value for. Defaults to current user ID.
	 * @params	obj|null	$obj	Optional. Single decision Pods object.
	 *
	 * @uses 	$this->get_consensus
	 *
	 * @return 	int					ID of decision.
	 *
	 * @since 	0.0.1
	 */
	function unblock( $id, $uID = null, $obj = null ) {
		$status = $this->status( $id );
		if ( $status === 'new' || $status === 'blocked' ) {
			$obj = $this->null_object( $obj, $id );
			$id = $this->change_consensus( $id, 0, $uID );

			do_action( 'ht_dms_new_unblock' );
			do_action( 'ht_dms_new_unblock_'.$id );

			return $id;
		}

	}

	/**
	 * Accept a decision
	 *
	 * Decision must be open (ie decision_status is 'new' or 'blocked').
	 * Fires the 'ht_dms_new_acceptance' and 'ht_dms_new_acceptance_{$id}' actions.
	 *
	 * @param 	int 		$id 	ID of decision to accept.
	 * @param 	int|null	$uID	Optional. User to change value for. Defaults to current user ID.
	 * @params	obj|null	$obj	Optional. Single decision Pods object.
	 *
	 * @uses 	$this->get_consensus
	 *
	 * @return 	int 				ID of decision.
	 *
	 * @since 	0.0.1
	 */
	function accept( $id, $uID = null, $obj = null ) {
		$status = $this->status( $id );
		if ( $status === 'new' || $status === 'blocked' ) {
			$obj = $this->null_object( $obj, $id );
			$id = $this->change_consensus( $id, 1, $uID );

			do_action( 'ht_dms_new_acceptance' );
			do_action( 'ht_dms_new_acceptance_'.$id );

			return $id;

		}

	}

	/**
	 * Respond to a decision
	 *
	 * Adds a comment to the decision.
	 * Fires the 'ht_dms_new_response' and 'ht_dms_new_response_{$id}' actions.
	 *
	 * @param	int		$id			ID of decision to respond to.
	 * @param	string	$content	Comment content.
	 */
	function respond( $id, $content ) {
		$time = current_time('mysql');

		$data = array(
			'comment_content'	=> $content,
			'comment_post_ID' 	=> $id,
			'user_id' 			=> get_current_user_id(),
			'comment_approved' 	=> 1,
		);

		wp_insert_comment( $data );

		do_action( 'ht_dms_new_response' );
		do_action( 'ht_dms_new_response_'.$id );
	}


	/**
	 * Get the dms_action query var
	 *
	 * @return bool|string
	 *
	 * @since 0.0.1
	 */
	function get_action_var() {
		$var = pods_v( 'dms_action', 'get', false, true );
		if ( $var !== false ) {
			return $var;
		}
		else {
			return false;
		}

	}

	/**
	 * Get the dms_id query var
	 *
	 * @return bool|int
	 *
	 * @since 0.0.1
	 */
	function get_id_var() {
		$var = intval( pods_v( 'dms_id', 'get', FALSE, TRUE ) );
		if ( $var !== false && is_int( $var )) {
			return $var;
		}
		else {
			return false;
		}

	}

	/**
	 * Action for accepting decision
	 *
	 * @return int $id ID of decision user has just accepted
	 *
	 * @since 0.0.1
	 */
	function action_accept( ) {
		if ( $this->get_action_var() !== false ) {
			if ( $this->get_action_var() === 'accept' ) {
				if ( $this->get_id_var() !== false ) {
					$id = $this->accept( $this->get_id_var() );
					return $id;
				}
			}
		}

	}

	/**
	 * Action for blocking decision
	 *
	 * @return int $id ID of decision user has just blocked
	 *
	 * @since 0.0.1
	 */
	function action_block( ) {
		if ( $this->get_action_var() !== false ) {
			if ( $this->get_action_var() === 'block' ) {
				if ( $this->get_id_var() !== false ) {
					$id = $this->block( $this->get_id_var() );
					return $id;
				}
			}
		}
	}

	/**
	 * Action for unblocking decision
	 *
	 * @return int $id ID of decision user has just unblocked
	 *
	 * @since 0.0.1
	 */
	function action_unblock( ) {
		if ( $this->get_action_var() !== false ) {
			if ( $this->get_action_var() === 'unblock' ) {
				if ( $this->get_id_var() !== false ) {
					$id = $this->unblock( $this->get_id_var() );
					return $id;
				}
			}
		}
	}

	/**
	 * Action for proposing a modification to a decision
	 *
	 * @TODO Where to output
	 *
	 * @return string  $form
	 *
	 * @since 0.0.1
	 */
	function action_propose_modify( ) {
		if ( $this->get_action_var() !== false ) {
			if ( $this->get_action_var() === 'propose_change' ) {
				if ( $this->get_id_var() !== false ) {
					$form = $this->propose_modify( $this->get_id_var() );
					return $form;
				}
			}
		}
	}

	/**
	 * Action for responding to a decision
	 *
	 * @TODO How to get content
	 *
	 * @return int $id ID of decision user has just unblocked
	 *
	 * @since 0.0.1
	 */
	function action_respond( ) {
		if ( $this->get_action_var() !== false ) {
			if ( $this->get_action_var() === 'respond' ) {
				if ( $this->get_id_var() !== false ) {
					$id = $this->respond( $this->get_id_var() );
					return id;
				}
			}
		}
	}


	/**
	 * Form for proposing a modification to a decision
	 *
	 * @TODO REMOVE THIS!
	 *
	 * @param 	int			$id			ID of decision to propose modification to.
	 * @param	obj|null	$single_obj	Optional. Decision object of single item that is being modified. If isn't a Pods Object for whole class, bad things will happen.
	 *
	 * @return 	string				Pods form
	 *
	 * @since 	0.0.1
	 */
	function _propose_modify ( $id, $obj = null ) {

		return $this->edit( $id, null, $obj );

		$form_fields = array(
			'post_title',
			'decision_description',
			'tasks',
			'decision_status',
			'manager',
			'proposed_by',
			'group',
			'organization',
		);

		foreach( $form_fields as $field ) {
			if ( $field === 'post_title' || 'decision_description' || 'decision_status' ) {
				$field[ 'default' ] = $obj->field( $field );
			}
			elseif ( $field === 'group' ) {
				$field[ 'default' ] = $this->get_group( $id, $obj );
			}
			elseif( $field === 'manager' ) {
				$field[ 'default' ] = (int) $obj->field( 'manager.ID' );
			}
			elseif( $field === 'proposed_by' ) {

			}
		}
		$form_fields ['change_to' ] =  array(
			'default' => (string) $id,
		);
		$form_fields [ 'reason_for_change' ] = array (
				'required' => true,
		);


	}


	/**
	 * Accept a proposed modification
	 *
	 * @param 	int   		$id		The ID of the of the <strong>proposed change</strong>.
	 * @param 	null|obj 	$obj	Optional. Decision object for <strong>the proposed change</strong>. If you pass the object for the original, bad things will happen.
	 *
	 * @return 	mixed
	 *
	 * @since 	0.0.1
	 */
	function accept_modify( $id, $obj = null ) {

		$obj =  $this->null_object( $obj, $id );

		$original_id = $obj->field( 'change_to.ID' );
		//get ID of original item and create seprate object for that.
		$original_obj = $this->item( $original_id  );

		//proceed directly to acceptance if accepting user is the original
		if ( (int) $original_obj->field( 'post_author' ) === ( $uID = (int) get_current_user_id()  ) ) {
			$make_mod = true;
		}
		else {
			$id = $this->accept( $id, $uID, $obj );
			if ( $this->has_consent( $id, $obj ) ) {
				$make_mod = true;
			}

		}

		if ( $make_mod ) {
			$this->make_modification( $id, $original_id, $obj, $uID, $original_obj );
			return true;

		}

		return $id;

	}

	/**
	 * Make modifications once accepted.
	 *
	 * @param $id
	 * @param $original_id
	 * @param $obj
	 * @param $uID
	 * @param $original_obj
	 *
	 * @return mixed
	 */
	function make_modification( $id, $original_id, $obj, $uID, $original_obj ) {

		$data = false;

		$unsets = array (
			'id',
			'ID',
			'consensus',
			'post_date',
			'post_date_gmt',
			'guid',
			'decision_status',
			'decision_type',
			'post_modified',
			'post_modified_gmt'
		);

		$data = $obj->row();


		foreach ( $unsets as $unset ) {
			unset( $data[ $unset ] );
		}


		//get a new consensus array, set $uID as accepting, and prepare it to save.
		$data[ 'consensus'] = holotree_consensus_class()->create( $id , $obj, true );
		$data[ 'consensus'][ $uID ][ 'value' ] = 1;
		$data[ 'consensus'] = serialize( $data[ 'consensus'] );

		$data[ 'decision_status' ] = 'new';
		$data[ 'decision_type' ] = 'modified';

		if ( is_array( $data ) ) {
			$updated_id = $original_obj->save( $data );

			if ( $original_id === $updated_id ) {
				$finished = $this->finish_proposed_change( $id, $obj );
			}
			else {
				holotree_error();
			}

			return $updated_id;

		}

	}

	/**
	 * Marks an accepted modification as being completed.
	 *
	 * @todo Change actual post status?
	 *
	 * @param      $id
	 * @param null $obj
	 *
	 * @return int
	 */
	function finish_proposed_change( $id, $obj = null ) {
		$obj = $this->null_object( $obj, $id );
		$id = $obj->save(
			array(
				'decision_type' 	=> 'accepted_change',
				'decision_status' 	=> 'completed',
			)
		);

		return $id;

	}

	/**
	 * Check if a group has consented to a decision.
	 *
	 * @param      $id
	 * @param null $obj
	 *
	 * @return bool
	 */
	function has_consent( $id, $obj = null ) {
		$obj = $this->null_object( $obj, $id );

		if ( $this->status( $id, $obj ) === 'completed' ) {
			return true;
		}
		else {
			$consensus = $this->get_consensus( $id );
			$values = wp_list_pluck( $consensus, 'value' );
			if ( ! in_array( 0, $values ) || ! in_array( 2, $values ) ) {
				return true;

			}

		}

	}

	function get_consensus( $id ) {
		$consensus = holotree_consensus( $id );

		if ( is_array( $consensus ) ) {
			return $consensus;

		}

	}

	/**
	 * Find if a decision has any active proposed changes.
	 *
	 * @TODO Skip closed param.
	 *
	 * @param 	int     $id				ID of decision to check for proposed modifications of.
	 * @param 	bool 	$skip_closed	Optional. If true, which is the default, only active proposed changes will be returned.

	 *
	 * @return 	bool|mixed				bool or array of ids that are modifications to the decision.
	 *
	 * @since	0.0.1
	 */
	function has_proposed_modification( $id, $obj = null, $skip_closed = true ) {

		$obj = $this->null_object( $obj, $id );

		$changes = $this->proposed_modifications( $id, $obj );

		if ( $changes ) {
			return true;
		}


	}

	/**
	 * Get all proposed changes to a decision.
	 *
	 * Returns either all fields, or just IDs of all proposed changes to a decision.
	 *
	 * @param 	int     		$id		ID of decision to see proposed changes of.
	 * @param 	Pods|obj|null 	$obj	Optional. Single Pods Object
	 * @param 	bool 			$ids	Optional. Whether to return the whole field array for each decision (the default, false) or to just return IDs (true).
	 *
	 * @return array|bool|mixed|null
	 */
	function proposed_modifications( $id, $obj = null, $ids = false ) {
		$obj = $this->null_object( $obj, $id );

		$changes = $obj->field( 'proposed_changes' );

		if ( ! empty( $changes) ) {
			if ( ! $ids ) {
				return $changes;
			}
			else {
				$ids = array();
				foreach( $changes as $change ) {
					$ids[] = $change[ 'ID' ];
				}

				if ( isset( $ids ) && is_array( $ids ) ) {
					return $ids;

				}

			}

		}

	}

	/**
	 * Check if a decision is a proposed modification.
	 *
	 * @param 	int     		$id		ID of decision to see proposed changes of.
	 * @param 	Pods|obj|null 	$obj	Optional. Single Pods Object
	 *
	 * @return 	bool					True if decision is a proposed modification to another decision. False if not.
	 *
	 * @since	0.0.2
	 */
	function is_proposed_modification( $id, $obj = null ) {
		$obj = $this->null_object( $obj, $id );

		if ( $obj->field( 'change_to' ) ) {
			return true;

		}

	}

	function save_proposed_modification( $pieces ) {
		$change_to = pods_v( 'dms_id', 'get', false, false );

		if ( isset ( $pieces[ 'fields' ][ 'change_to' ][ 'value' ] ) ) {
			$change_to = $pieces[ 'fields' ][ 'change_to' ][ 'value' ];
			$id = $change_to[ 'ID' ];
			$this->update( $id, 'proposed_changes', $change_to, true );

		}

	}

	/**
	 * Check if a decision is blocked.
	 *
	 * @param 	int	$id	ID of decision to test.
	 *
	 * @return 	bool 	True if is blocked.
	 *
	 * @since 0.0.1
	 */
	function is_blocked( $id ) {
		$status= $this->status( $id );
		if ( $status === 'blocked' ) {
			return true;
		}
	}

	/**
	 * Returns an array of who is blocking a decision.
	 *
	 * @param 	int		$id		ID of decision.
	 *
	 * @return  array   $who    IDs of those blocking.
	 *
	 * @since 0.0.1
	 */
	function who_is_blocking( $id ) {
		if ( $this->is_blocked( $id ) ) {
			$consensus = holotree_consensus_class()->get( $id );
			if ( is_array( $consensus ) ) {
				$who = array();
				foreach ( $consensus as $value ) {
					if ( $value[ 'value' ] === 2 ) {
						$who[] = $value[ 'id' ];

					}
				}
				if ( isset( $who ) ) {
					return $who;
				}
			}
		}

	}

	/**
	 * Check if a decision is blocked
	 *
	 * @param	int		$id		ID of decision.
	 * @param 	null 	$uID	Optional. User ID to check if they are blocking.
	 *
	 * @return 	bool 	True if blocked.
	 *
	 * @since 0.0.1
	 */
	function is_blocking( $id, $uID = null ) {
		$uID = intval( $this->null_user( $uID ) );
		$who =  $this->who_is_blocking( $id, $uID );
		if ( in_array( $uID, $who ) ) {
			return true;
		}

	}

	/**
	 * Check the status of a decision
	 *
	 * @param 	int			$id		ID of decision to check.
	 * @param	obj|null	$obj	Optional. Single decision status.
	 * @param	bool		$check	Optional. Whether to check if status is correct, by evaluating consensus array, if true, or to just check this field value, if false, the default.
	 *
	 * @return 	string		$status	The status.
	 *
	 * @since 	0.0.1
	 */
	function status( $id, $obj = null, $check = false ) {
		$obj = $this->null_object( $obj, $id );
		$status = $obj->field( 'decision_status' );
		if ( $check && ( $status !== 'passed' || $status !== 'completed' ) ) {
			if ( $this->has_consent( $id, $obj ) ){
				$this->update( $id, 'decision_status', 'passed', $obj );
				$status = 'passed';
			}

		}

		return $status;

	}

	function _time_frame( $obj = null, $id ) {
		$obj = $this->null_object( $obj, $id );
		if ( ! empty( $length = $obj->field( 'time_frame' ) )  ) {

			return $length;

		}
		elseif( ! empty ( $length = $obj->field( 'group.time_frame ' ) ) ) {

			return $length;

		}
		elseif( ! empty ( $length = $obj->field( 'organization.time_frame' ) ) ) {

			return $length;

		}
		else {

			return get_option( 'ht_dms_default_time_frame', WEEK_IN_SECONDS );

		}
	}

	function time_frame( $obj = null, $id ) {
		$obj = $this->null_object( $obj, $id );

		$length = $obj->field( 'time_frame' );
		if ( ! empty( $length )  ) {

			return $length;

		}

		$length = $obj->field( 'group.time_frame' );
		if ( ! empty ( $length ) ) {

			return $length;

		}

		$length = $obj->field( 'organization.time_frame' );
		if( ! empty ( $length ) ) {

			return $length;

		}

		return get_option( 'ht_dms_default_time_frame', WEEK_IN_SECONDS );

	}

	/**
	 *
	 * @TODO incremental?
	 */
	function checks () {
		$params = array(
			'where' => 'd.decision_status = "new"' OR 'd.decision_status = "blocked"'
		);
		$obj = $this->object( false, $params );
		$changes = $this->time_checks( $obj );
		$notifications_sent = $this->post_check_notifications( $changes, $obj );
		$checks = array(
			'changes'			 => $changes,
			'notifications_sent' => $notifications_sent,
		);

		return $checks;
	}


	function time_checks ( $obj ) {
		$changes = false;

		if ( $obj->total() > 0 ) {
			while ( $obj->fetch() ) {
				$created = strtotime( $obj->field( 'post_date' ) );
				$id = $obj->id();
				$length = $this->time_frame( $obj, $id  );

				$elapsed = time() - $created;
				if ( $length > $elapsed ) {
					$change = false;
					$status = $this->status( $id, $obj );

					if ( $status === 'new' ) {
						$change = 'passed';
					}
					elseif ( $status === 'blocked' ) {
						$change = 'failed';
					}

					if ( $change ) {
						$this->update( $id, 'decision_status', $change, $obj );
					}

					//@TODO More efficent/ less redundant way fo doing this?
					$gID = $this->get_group( $id, $obj);
					$group_name = get_the_title( $gID );
					$changes[] = array(
						'id' 			=> $id,
						'gID'			=> $gID,
						'what_changed'	=> $change,
						'name'			=> $obj->field( 'post_title' ),
						'group_name'	=> $group_name,
					);
				}
				else {
					$id = $obj->id;
					$changes[] = array(
						'id' 		=> $id,
						'change'	=> 'none'
					);
				}

			}
		}

		return $changes;

	}

	function post_check_notifications( $changes ) {
		return __METHOD__.' not ready:(';
		$dms = $GLOBALS[ 'ht_dms' ];
		foreach ( $changes as $change ) {
			extract( $change );
			$members = $GLOBALS[ 'dms_group' ]->all_members( $gID );
			foreach ( $members as $uID  ) {
				if ( $what_changed !== 'none' ) {

					$message = 'The pending decision ' . $name . ' in the group ' . $group_name . ' has ' . $what_changed . '.';
					$subject = '[HT Decision Making System] ' . $name . ' update';
					$dms->notification( $uID, $message, $subject );

					$notifications_sent[ ] = array (
						'group_id' 	=> $gID,
						'user_id'  	=> $uID,
						'decision'	=> $name,
						'change'	=> $what_changed,
					);
				}

			}

		}

		return $notifications_sent;

	}




	/**
	 * Update user fields related to this post type.
	 *
	 * Workaround for Pods issue #1945
	 * @see https://github.com/pods-framework/pods/issues/1945
	 *
	 * @param $pieces
	 * @param $is_new_item
	 * @param $id
	 *
	 * @uses 'pods_api_post_save_pod_item_' hook
	 *
	 * @return mixed
	 *
	 * @since 0.0.1
	 */
	function user_fix( $pieces, $is_new_item, $id ) {
		$fields = array(
			'proposed_by' => 'decisions_proposed',
			'manager' => 'decisions_managing'
		);


		if ( isset (  $pieces[ 'fields' ][ 'proposed_by' ][ 'value' ] ) ) {
			$user_id = (int)$pieces[ 'fields' ][ 'proposed_by' ][ 'value' ];
			if ( is_int( $user_id) ) {
				update_user_meta( $user_id, 'decisions_proposed', $id );
			}
		}

		if ( isset( $pieces[ 'fields' ][ 'manager' ][ 'value' ] ) ) {
			$user_id = (int)$pieces[ 'fields' ][ 'manager' ][ 'value' ];
			if ( is_int( $user_id ) ) {
				update_user_meta( $user_id, 'decisions_managing', $id );
			}
		}


		return $pieces;

	}



} 
