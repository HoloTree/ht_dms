<?php
namespace ht_dms\helper\caldera;

class import {
	/**
	 * Do the import
	 *
	 * @since 0.1.0
	 */
	static public function import_forms() {
		$files = self::files();
		if ( is_array( $files ) ) {
			foreach( $files as $file ) {
				self::import_form( $file );
			}

		}

	}

	/**
	 * Import a form
	 *
	 * @since 0.1.0
	 *
	 * @param string $file Path to file to import
	 */
	static private function import_form( $file ) {
		if ( file_exists( $file ) ) {
			$data = json_decode(file_get_contents( $file ), true);
			if(isset($data['ID']) && isset($data['name']) && isset($data['fields'])){

				// get form registry
				$forms = get_option( '_caldera_forms' );

				// return if already exists
				if( isset( $forms[$data['ID']] ) ){
					return;
				}

				// if a new install and no registery
				if(empty($forms)){
					$forms = array();
				}

				// add form to registry
				$forms[$data['ID']] = $data;

				// remove undeeded settings for registry
				if(isset($forms[$data['ID']]['layout_grid'])){
					unset($forms[$data['ID']]['layout_grid']);
				}
				if(isset($forms[$data['ID']]['fields'])){
					unset($forms[$data['ID']]['fields']);
				}
				if(isset($forms[$data['ID']]['processors'])){
					unset($forms[$data['ID']]['processors']);
				}
				if(isset($forms[$data['ID']]['settings'])){
					unset($forms[$data['ID']]['settings']);
				}

				// add from to list
				update_option($data['ID'], $data);
				do_action('caldera_forms_import_form', $data);

				update_option( '_caldera_forms', $forms );
				do_action('caldera_forms_save_form_register', $data);

				return __( 'Import Successful!', 'cf-mark-viewed' );

			}
			else {
				new WP_Error( 'ht-dms-caldera-bad-import-file', __( 'Import file is invalid.', 'ht-dms' ) );
			}
		}
		else{
			new WP_Error( 'ht-dms-caldera-no-import-file', __( 'No import file found:(', 'ht_dms' ) );
		}

	}

	/**
	 * Get all form files to import
	 *
	 * @since 0.1.0
	 *
	 * @return array|bool
	 */
	static private function files() {
		$dir = self::form_directory();
		if ( ! $dir ) {
			return false;
		}
		$files = scandir( $dir  );
		foreach ( $files as $file  ) {
			if ( 'json' !== pathinfo( $file, PATHINFO_EXTENSION ) ) {
				unset( $files[ $file ] );
			}
		}

		if ( is_array( $files ) ) {
			return $files;
		}
		else {
			ht_dms_error( var_dump( $dir  ) );
		}
	}

	/**
	 * Get the directory to import from.
	 *
	 * @since 0.1.0
	 * 
	 * @return string|bool
	 */
	static private function form_directory() {
		$dir = trailingslashit( basename( __FILE__ ) ) . 'forms';
		if ( file_exists( $dir ) ) {
			return $dir;
		}

	}

}




