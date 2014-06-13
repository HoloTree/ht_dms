<?php
/**
 * Creates the "my_stuff" widget
 *
 * @TODO Limit number of groups/ tasks or paginate.
 *
 * @package   holotree
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\ui\build;

class my_stuff {

	/**
	 * Gets all decisions for each group a user is a member of.
	 *
	 * @param 	int|null 	$uID	Optional. User ID. Defaults to null, which uses current logged in user.
	 *
	 * @return 	array				Array of decisions for use in $this->view()
	 *
	 * @since 	0.0.1
	 */
	function my_group_decisions( $uID = null ) {
		$decisions = false;
		$uID = holotree_dms_class()->null_user( $uID );
		$groups = holotree_group_class()->users_groups( $uID, true );

		if ( is_array( $groups ) ) {
			foreach ( $groups as $group ) {
				$gID = $group[ 'ID' ];
				$obj = holotree_pods_object( 'decision' );
				$params = array ( 'where' => 'group.id = "' . $gID . '"' );
				$obj = $obj->find( $params );
				if ( $obj->total() > 0 ) {
					while ( $obj->fetch() ) {
						$status = $obj->field( 'decision_status' );
						$decisions[ $gID ][ $status ][ $obj->id() ] = array (
							'ID'     => $obj->id(),
							'name'   => $obj->field( 'post_title' ),
							'status' => $status
						);

					}
					$decisions[ $gID ][ 'group' ] = array (
						'ID'   => $gID,
						'name' => $group[ 'name' ],
					);

				}

			}
		}

		return $decisions;

	}

	/**
	 * Gets all tasks assigned to a user
	 *
	 * @param 	int|null 	$uID	Optional. User ID. Defaults to null, which uses current logged in user.
	 *
	 * @return 	array				Array of tasks for use in $this->view()
	 *
	 * @since 	0.0.1
	 */
	function my_tasks( $uID = null ) {
		$uID = holotree_dms_class()->null_user( $uID );
		$obj = holotree_pods_object( 'task' );
		$obj = $obj->find( array(  'd.assigned_user = "'.$uID.'" ') );
		if ( $obj->total() > 0 ) {
			while ( $obj->fetch() ) {
				$id = $obj->id();
				$tasks[ $id ] = array(
					'ID'		=> $id,
					'name'		=> $obj->field( 'name' ),
					'status'	=> $obj->field( 'task_status' ),
				);

			}

		}

		return $tasks;

	}

	/**
	 * Creates the widget's output
	 *
	 * @param   int|null 	$uID 	Optional. User ID. Defaults to null, which uses current logged in user.
	 *
	 * @return  string             	The widget.
	 *
	 * @since	0.0.1
	 */
	function view( $uID = null  ) {
		$mgd = $this->my_group_decisions( $uID );
		$group_count = count( $mgd );


		$out = '<h3>'.__( 'My Stuff', 'holotree' ).'</h3>';

		if ( is_array( $mgd ) ) {
			$panels[0][ 'label' ] = __( 'My Groups', 'holotree' );
			$panel_content = '';
			$i = 0;

			foreach ( $mgd as $decisions ) {

				if ( $group_count === 1 ) {
					$panel_content .= '<h5>' . $decisions[ 'group' ][ 'name' ] . '</h5>';
				}
				else {
					$tabs[ $i ][ 'label' ] = $decisions[ 'group' ][ 'name' ];
					$tab_content = '';
				}


				if ( isset( $decisions[ 'new'] ) ) {
					$new = $decisions[ 'new' ];

					if ( is_array( $new ) && $new !== FALSE ) {
						$content = '<h6>' . __( 'New Decisions', 'holotree' ) . '</h6>';
						$content .= $this->decisions( $new );

						if ( $group_count === 1 ) {
							$out .= $content;
						}
						else {
							$tab_content .= $content;
						}

					}

					unset( $new );

				}

				if ( isset( $decisions[ 'blocked' ] ) ) {
					$blocked = $decisions[ 'blocked' ];
					if ( is_array( $blocked ) ) {
						$content = '<h6>' . __( 'Blocked Decisions', 'holotree' ) . '</h6>';
						$content .= $this->decisions( $blocked );

						if ( $group_count === 1 ) {
							$panel_content .= $content;
						}
						else {
							$tab_content .= $content;

						}

					}

					unset( $blocked );

				}

				$tabs[ $i ][ 'content' ] = $tab_content;
				$i++;

			}

			unset( $decisions );

			if ( $group_count !== 1 ) {

				$panel_content .= $this->ui()->elements()->tab_maker( $tabs );
			}

			$panels[0][ 'content' ] = $panel_content;
		}

		$tasks = $this->my_tasks( $uID );
		if ( is_array( $tasks ) ) {
			$panels[1][ 'label' ] =  __( 'My Tasks', 'holotree' );

			$panel_content = '<ul>';

			foreach ( $tasks as $task ) {
				$panel_content .= '<li>' . holotree_link( $task[ 'ID' ], 'tax', $task[ 'name' ], $task[ 'name' ] );
				if ( isset( $task[ 'status' ] ) && $task[ 'status'] !== '' ) {
					$panel_content .= '<br /><span class="my-stuff-task-status">Status- ' . $task[ 'status' ] . '</span></li>';
				}
			}

			$panel_content .= '</ul>';
			$panels[1][ 'content' ] = $panel_content;
		}

		$out .= $this->ui()->elements()->accordion( $panels );

		return $out;

	}

	/**
	 * Outputs link to a decision as needed by $this->view()
	 *
	 * @param 	array	$decision	Individual decision array
	 *
	 * @return 	string				The link.
	 *
	 * @since 	0.0.1
	 */
	function decision( $decision ) {
		$out = '<li>' . holotree_link( $decision[ 'ID' ], 'permalink', $decision[ 'name' ], $decision[ 'name' ] ) . '</li>';

		return $out;

	}

	/**
	 * Outputs decision list as needed by $this->view()
	 *
	 * @param 	array	$decisions	Decisions array
	 *
	 * @return 	string				The list.
	 *
	 * @since 	0.0.1
	 */
	function decisions( $decisions ) {
		$out = '<ul>';
		foreach ( $decisions as $decision ) {

			$out .= $this->decision( $decision );

		}

		$out .= '</ul>';

		return $out;
	}

	/**
	 * Get instance of UI class
	 *
	 * @return 	\holotree\ui
	 *
	 * @since 	0.0.1
	 */
	function ui(){
		$ui = holotree_dms_ui();

		return $ui;

	}

} 
