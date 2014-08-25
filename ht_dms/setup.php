<?php
/**
 * Setups Pods
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms;

class setup {

	/**
	 * The name of the Pods package to add
	 *
	 *  @since 0.0.2
	 *
	 * @var string
	 */
	static $package_path = 'pods.json';

	/**
	 * Constructor for the class.
	 *
	 *  @since 0.0.2
	 *
	 * @param bool $package_only	Optional. Whether to just import from import package and skip relationship field updates. Default is false.
	 * @param bool $skip_package	Optional. Whether to skip importing the package, and advanced directly to updating relationship fields, not pass go and not collect $200 dollars. Default is false.
	 * @param bool $delete_existing	Optional. Whether to delete existing Pods (for DMS including user) before importing. Default is true.
	 */
	function __construct( $package_only = false, $skip_package = true, $delete_existing = false ) {
		echo 'Beginning import...<br />';

		if ( $delete_existing ) {
			echo 'Deleting existing Pods';
			$this->delete_existing();
		}

		if ( ! $skip_package  ) {
			$import = $this->import_package();
			$this->reset();
			if ( $import ) {
				echo 'Pods imported';
				pods_transient_set( 'ht_dms_pods_exists', true );
			}
			else {
				echo 'Pods not imported. Aborting.';
				return;
			}
		}

		if ( false === $package_only  ) {
			echo 'Updating relationship fields<br />';
			$relationships = $this->relationships();
			foreach ( $relationships as $relationship ) {
				$id = $this->update( $relationship[ 'from' ], $relationship[ 'to' ] );
				echo $id.'<br />';
			}

			$this->reset();

		}

		echo 'Import completed';


	}

	/**
	 * Updates the relationship fields.
	 *
	 * @since 0.0.2
	 *
*@param array $from	The name of Pod & field relationship is from.
	 * @param array $to		The name of Pod & field relationship is to.
	 *
	 * @return array|int
	 */
	private function update( $from, $to ) {
		$api = $this->api();

		if ( false == $api->pod_exists( $from[ 'pod' ]  ) ) {
			echo sprintf( 'The Pod %1s does not exist.', $from[ 'pod' ] );
			return;

		}

		$params[ 'pod' ] = $from[ 'pod' ];
		$params[ 'name' ] = $from[ 'field' ];
		$sister_id = $api->load_field( $params );
		$sister_id  = $sister_id [ 'id' ];

		unset( $params );

		if ( false == $api->pod_exists( $to[ 'pod' ] ) ) {
			echo sprintf( 'The Pod %1s does not exist.', $to[ 'pod' ] );
			return;

		}

		$params[ 'pod' ] = $to[ 'pod' ];
		$params[ 'sister_id' ] = $sister_id;
		$params[ 'name' ] = $to[ 'field' ];

		return $api->save_field( $params );

	}

	/**
	 * Delete existing Pods
	 *
	 * @since 0.0.2
	 */
	private function delete_existing() {
		$api = $this->api();
		$pods = array( HT_DMS_ORGANIZATION_NAME, HT_DMS_GROUP_CPT_NAME, HT_DMS_GROUP_CPT_NAME, HT_DMS_TASK_CT_NAME, 'user' );

		foreach( $pods as $pod ) {
			$api->delete_pod( $params[ 'name' ] = $pod );
		}


	}

	/**
	 * Defines the relationships to update
	 *
	 * This method is defined as public so it can be overloaded.
	 *
	 * @since 0.0.2
	 *
	 * @return array
	 */
	public function relationships() {
		$d = HT_DMS_DECISION_CPT_NAME;
		$g = HT_DMS_GROUP_CPT_NAME;
		$t = HT_DMS_TASK_CT_NAME;
		$o = HT_DMS_ORGANIZATION_NAME;
		return array(
			'decisions_managing' => array(
				'from' 		=> array(
					'pod' 	=> $d,
					'field' => 'manager',
				),
				'to' 		=> array(
					'pod' 	=> 'user',
					'field' => 'decisions_managing',
				),
			),
			'proposed_by' 	=> array(
				'from'	 	=> array(
					'pod' 	=> $d,
					'field' => 'proposed_by',
				),
				'to' 		=> array(
					'pod' 	=> 'user',
					'field' 	=> 'decisions_proposed',
				),
			),
			'group'			=> array(
				'from' 		=> array(
					'pod'	=> $d,
					'field' => 'group',
				),
				'to'		=> array(
					'pod'	=> $g,
					'field'	=> 'decisions'
				),
			),
			'tasks'			=> array(
				'from'		=> array(
					'pod'	=> $d,
					'field'	=> 'tasks',
				),
				'to'		=> array(
					'pod'	=> $t,
					'field'	=> 'decision',
				),
			),
			'organization'	=> array(
				'from'		=> array(
					'pod'	=> $d,
					'field' => 'organization',
				),
				'to'		=> array(
					'pod' 	=> $o,
					'field'	=> 'decisions',
				),
			),
			'group_members'	=> array(
				'from'		=> array(
					'pod'	=> $g,
					'field'	=> 'members',
				),
				'to'		=> array(
					'pod'	=> 'user',
					'field'	=> 'groups',

				),
			),
			'group_decisions'	=> array(
				'from'		=> array(
					'pod'	=> $g,
					'field'		=> 'decisions',
				),
				'to'		=> array(
					'pod'	=> $d,
					'field'	=> 'group',
				),
			),
			'group_pending_members'	=> array(
				'from'		=> array(
					'pod'	=> $g,
					'field'	=> 'pending_members',
				),
				'to'		=> array(
					'pod'	=> 'user',
					'field'	=> 'pending_memberships',
				),
			),
			'group_facilitators'	=> array(
				'from'		=> array(
					'pod'	=> $g,
					'field'	=> 'facilitators',
				),
				'to'		=> array(
					'pod'	=> 'user',
					'field'	=> 'groups_facilitating',
				),
			),
			'group_organization'=> array(
				'from'		=> array(
					'pod'	=> $g,
					'field' => 'organization',
				),
				'to'		=> array(
					'pod' 	=> $o,
					'field'	=> 'groups',
				),
			),
			'organization_members' => array(
				'from'		=> array(
					'pod'	=> $o,
					'field'	=> 'members',
				),
				'to'		=> array(
					'pod'	=> 'user',
					'field'	=> 'organization_memberships',
				),
			),
			'organization_facilitators'	=> array(
				'from'		=> array(
					'pod'	=> $o,
					'field'	=> 'facilitators',
				),
				'to'		=> array(
					'pod'	=> 'user',
					'field'	=> 'organizations_facilitating',
				),
			),
			'assigned_user' => array(
				'from'		=> array(
					'pod'	=> $t,
					'field'	=> 'assigned_user',
				),
				'to'		=> array(
					'pod'	=> 'user',
					'field'	=> 'tasks_assigned',
				),
			),
			'blockers'	 	=> array(
				'from'		=> array(
					'pod'	=> $t,
					'field'	=> 'blockers',
				),
				'to'		=> array(
					'pod'	=> $t,
					'field'	=> 'blocking',
				),
			),
			'task_group' 	=> array(
				'from'		=> array(
					'pod'	=> $t,
					'field'	=> 'decision_group',
				),
				'to'		=> array(
					'pod'	=> $g,
					'field'	=> 'tasks',
				),
			),
			'task_organization'	=> array(
				'from'		=> array(
					'pod'	=> $t,
					'field'	=> 'organization',
				),
				'to'		=> array(
					'pod'	=> $o,
					'field'	=> 'tasks',
				),
			),
		);

	}

	/**
	 * Imports the Pods import package
	 *
	 * @since 0.0.2
	 *
	 * @return bool
	 */
	private function import_package() {
		$file = self::$package_path;
		if ( $file ) {
			$file = trailingslashit(  HT_DMS_ROOT_DIR ).'inc/'.$file;
		}

		if ( file_exists( $file ) && class_exists( 'Pods_Migrate_Packages' ) ) {
			$data = file_get_contents( $file  );
			return \Pods_Migrate_Packages::import( $data );
		}
		else {
			echo sprintf( 'The file %1s could not be found and used to import.', $file  );
			return false;
		}

	}

	/**
	 * Get an instance of the Pods_API class
	 *
	 *
	 * @since 0.0.2
	 *
	 * @todo cache?
	 *
	 * @return \PodsAPI
	 */
	private function api() {
		$api = \pods_api();

		return $api;

	}

	/**
	 * Clear Pods Cache
	 */
	private function reset() {
		pods_transient_clear();
		pods_cache_clear();
	}

}


