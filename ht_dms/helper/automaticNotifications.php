<?php
/**
 * Creates notifications on specific events
 *
 * @package   ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\helper;


class automaticNotifications implements \Action_Hook_SubscriberInterface {

	/**
	 * Set actions
	 *
	 * @since 0.0.3
	 *
	 * @return array
	 */
	public static function get_actions() {

		return array(

			'ht_dms_decision_passed' => array( 'decision_passed', 10, 1 ),
			'ht_dms_decision_failed' => array( 'decision_failed', 10, 1 ),
            'ht_dms_new_decision'    => array( 'new_decision', 10, 4)
		);
	}

    /**
     * Notify all members of group that a decision passed
     *
     * @since 0.0.3
     *
     * @param int $id Decision ID
     *
     * @uses 'ht_dms_decision_passed' action
     */
    function decision_passed( $id ) {

        $this->decision_status_change_message( $id, 'passed' );

    }

    /**
     * Notify all members of group that a decision failed
     *
     * @since 0.0.3
     *
     * @param int $id Decision ID
     *
     * @uses 'ht_dms_decision_failed' action
     */
    function decision_failed( $id ) {

        $this->decision_status_change_message( $id, 'failed' );

    }

    /**
     * Notify all members of group that a new decision was created
     *
     *
     * @since 0.0.3
     * @uses 'ht_dms_new_decision'
     *
     * @param $dID
     * @param $data
     * @param $gID
     * @param $oID
     */
	function new_decision( $dID, $data, $gID, $oID ) {
		$g = ht_dms_group_class();
		$d = ht_dms_decision_class();
		$gObj = ht_dms_group( $gID );
		$group_name = $g->title( $gID, $gObj );
		$decision_name = $d->title( $dID );
		$decision_link = ht_dms_link( $dID );

		$members = $g->all_members( $gID, $gObj );

		$subject = __( sprintf( 'New decision %1s created in the %1s group.', $decision_name, $group_name ), 'ht_dms' );

		$message = __( sprintf( 'You can see the new decision here: %1s', $decision_link ), 'ht_dms' );

		$this->send_to_members( $members, $subject, $message );

	}

    /**
     * Send to all members
     *
     * @since 0.0.3
     *
     * @param array $members Array of IDs to send to
     * @param string $subject The message subject
     * @param string $message The message
     */
    private function send_to_members( $members, $subject, $message ) {
        if ( is_array( $members ) && ! empty( $members ) ) {
            foreach( $members as $to => $uneeded ) {
                $this->new_message( $to, $subject, $message );

            }

        }
    }

    /**
     * Create a decision status change message
     *
     * @todo set $change in a translation friendly fashion.
     *
     * @since 0.0.3
     *
     * @param int $id The decision's ID
     * @param string $change Change message.
     */
    private function decision_status_change_message( $id, $change ) {
        $d = ht_dms_decision_class();
        $obj = $d->item( $id );
        $members = $d->consensus_members( $id );

        $decision_name = $d->title( $id, $obj );
        $decision_link = ht_dms_link( $id );
        $group_link = ht_dms_link( $d->get_group( $id, $obj ) );


        $subject = __( sprintf( 'The decision %1s has %2s', $decision_name, $change ), 'ht_dms' );
        $message = __( sprintf( 'The decision %1s in the group %2s has %3s.', $decision_link, $group_link ), 'ht_dms' );

        $this->send_to_members( $members, $subject, $message );
    }


    /**
     * Send a message.
     *
     * @since 0.0.3
     *
     * @param $to
     * @param $subject
     * @param $message
     * @return int
     */
	function new_message( $to, $subject, $message  ) {

		return ht_dms_notification_class()->create( $to, $subject, $message );

	}

    /**
     * Create summary messages
     *
     * @since 0.0.3
     *
     * @param int $uID User ID
     */
	function create_summaries( $uID ) {
		if ( ! is_array( $uID ) && ht_dms_integer( $uID ) ) {
			$users =  array( $uID );
		}
		else{
			$users = $uID;
		}

		$summaries = $this->summaries();
		if ( is_array( $summaries )  && ! empty( $summaries ) && is_array( $users ) ) {
			foreach( $users as $uID ) {
				foreach( $summaries as $summary ) {
					$content = $this->create_summary( $uID, $summary[ 'type' ] );
					$this->new_message( $uID, $summary[ 'subject' ], $content );

				}

			}

		}

	}

    /**
     * Creates the summaries specified in $this->summaries
     *
     * @since 0.0.3
     *
     * @param int $uID
     * @param string $type The type of summary.
     *
     * @return array
     */
	private function create_summary( $uID, $type ) {
		$types = $this->summaries();
		$types = wp_list_pluck( $types, 'type' );
		if ( in_array( $type, $types ) ) {
			$method = "{$type}_summary";
			if ( method_exists( $this, $method ) ) {

				return call_user_func( array( $this, $method ), $uID );

			}
		}

	}

    /**
     * Decision summary create, this does.
     *
     * @since 0.0.3
     *
     * @param $uID
     *
     * @return string
     */
	private function decision_summary( $uID ) {
		$out = $decisions = false;
		$g = ht_dms_group_class();
		$groups = $g->users_groups_obj( $uID, null, -1,false, true );
		if ( is_array( $groups ) && ! empty( $groups ) ) {
			$statuses = array ( 'new', 'blocked' );
			foreach( $groups as $group ) {
				foreach ( $statuses as $status ) {
					$decisions[ $status ] = $g->decisions_by_status( $group, $status, 'names' );
				}
			}
		}

		if ( is_array( $decisions ) ) {
			foreach ( $decisions as $label => $type ) {
				$list = false;

				foreach ( $type as $id => $name ) {
					$list[ ] = sprintf( '<li><a href="%1s">%2s</a></li>', get_the_permalink( $id ), $name );
				}
				if ( is_array( $list ) ) {
					$out[ ] = sprintf( '<div class="decision-type">%1s</div>', ucwords( $label ) );
					$out[ ] = sprintf( '<ul class="decisions-list">%1s</ul>', implode( $list ) );
				}


			}
		}

		if ( is_array( $out ) ) {

			return implode( $out );

		}

	}

    /**
     * Creates membership summary
     *
     * @since 0.0.3
     *
     * @param $uID
     *
     * @return bool|string
     */
	private function membership_summary( $uID ) {
		$g = ht_dms_group_class();
		$params = array(
			'where' => 'facilitators.ID = "' . $uID . ' " ',
			'expires' => 599,
		);
		$obj = $g->object( false, $params );

		$pending = $out = false;

		if ( $obj->total() > 0 ) {
			$pending[ $obj->id() ] = array(
				'pending_members' => $g->get_pending( $obj->ID(), $obj ),
				'name' => $obj->display( 'post_title' ),
			);
		}

		if ( is_array( $pending ) ) {
			$build_elements = ht_dms_ui()->build_elements();
			foreach( $pending as $group ) {
				foreach( $group as $pending_members => $name) {
					$pending_list = false;
					if (is_array( $pending_members ) ) {

						foreach( $pending_members as $uID ) {
							$pending_list[] = $build_elements->member_details( $uID );
						}
					}

					if ( is_array( $pending_members ) ) {
						$out[] = sprintf(
							'<div class="pending-members-in">
								<h3 class="group-name">%1s</h3>
								<ul class="pending-members-list">
									%2s
								</ul>
							</div>
							',
							$name,
							implode( $pending_members )
						);
					}
				}
			}

			if ( is_array( $out ) ) {

				return sprintf( '<div id="pending-members-summary" class="summary-notification">%1s</div>', implode( $out ) );
			}

		}

		return $pending;

	}

    /**
     * List of summaries to create
     *
     * @since 0.0.3
     *
     * @return array
     */
	private function summaries() {
		return array(
			array(
				'type' => 'membership',
				'subject' => __( 'Daily Group Membership Updates', 'ht_dms' ),
			),
			array(
				'type' => 'decisions',
				'subject' => __( 'Active Decision Updates', 'ht_dms' ),
			)

		);

	}

	/**
	 * Holds the instance of this class.
	 *
	 * @since  0.0.3
	 * @access private
	 * @var    object
	 */
	private static $instance;


	/**
	 * Returns instance of class
	 *
	 * @return automaticNotifications|object
	 *
	 * @since  0.0.3
	 */
	public static function init() {
		if ( !self::$instance )
			self::$instance = new self();

		return self::$instance;

	}
} 
