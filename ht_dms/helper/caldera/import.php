<?php
function ht_dms_caldera_import() {

	$dir = HT_DMS_DIR .'/helper/caldera/forms';

	$files = scandir( $dir  );
	foreach( $files as $file ) {
		$ext = pathinfo( $file, PATHINFO_EXTENSION );
		if ( $ext === 'json' ) {
			$data = (array) json_decode(file_get_contents( "{$dir}/{$file}", true) );


			// get form registry
			$forms = get_option( '_caldera_forms' );
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

		}

	}

}
