<?php
/**
 * Make smaller JSON Arrays
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\helper;

class json {

	static public function group( $id, $obj = null ) {
		$obj    = ht_dms_group_class()->null_obj( $obj, $id );
		$fields = array (
			'organizationID'    => 'organization.ID',
			'organizationName'  => 'organization.post_title',
			'members'           => 'members',
			'description'       => 'group_description',
			'name'          => 'post_title'
		);

		$data = array ();
		foreach ( $fields as $index => $field ) {
			if ( $index == 'members' ) {
				$group_members = false;
				$members        = $obj->field( $field );

				if ( is_array( $members ) ) {
					foreach( $members as $member ) {
						$uID = pods_v( 'ID', $member );
						$member = array(
							'name' => pods_v( 'display_name', $member ),
							'avatar' => get_avatar( $uID, '96', ht_dms_fallback_avatar() ),
							'ID' => $uID,
						);


						$group_members[ $uID ] = $member;
					}

				}

				if ( is_array( $group_members ) ) {
					$data[ $index ] = $group_members;
				}

			} else {

				$data[ $index ] = $obj->display( $field );
			}
		}

		$data[ 'organizationLink' ] = get_permalink( $data[ 'organizationID' ] );
		$data[ 'link' ] = get_permalink( $id );
		$data[ 'ID' ] =  $id;

		if ( is_array( $data ) ) {
			return json_encode( $data );
		}

	}

	static public function organization( $id, $obj = null ) {

		$obj = ht_dms_organization_class()->null_obj( $obj, $id );

		$fields = array (
			'name'          => 'post_title',
			'members'       => 'members',
			'description'   => 'description',

		);

		$data = array ();
		foreach ( $fields as $index => $field ) {
			if ( $index == 'members' ) {
				$members = false;
				$members = $obj->field( $field );

				if ( is_array( $members ) ) {
					foreach( $members as $member ) {
						$uID = pods_v( 'ID', $member );
						$member = array(
							'name' => pods_v( 'display_name', $member ),
							'avatar' => get_avatar( $uID, '96', ht_dms_fallback_avatar() ),
							'ID' => $uID,
						);


						$org_members[ $uID ] = $member;
					}

				}

				if ( is_array( $org_members ) ) {
					$data[ $index ] = $org_members;
				}

			} else {

				$data[ $index ] = $obj->display( $field );

			}
		}


		$data[ 'link' ] = get_permalink( $id );
		$data[ 'ID' ] =  $id;

		if ( is_array( $data ) ) {
			return json_encode( $data );
		}

	}

	static public function encode_to_script( $data, $var_name ) {
		if ( ! is_array( $data ) ) {
			explode( ',', $data );
		}

		$data = json_encode( $data );

		return "<script type='text/javascript'>var {$var_name} = {$data};</script>";

	}

} 
