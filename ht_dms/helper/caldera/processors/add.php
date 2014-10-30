<?php
/**
 * Add Custom CF processers
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\helper\caldera\processors;


class add implements \Filter_Hook_SubscriberInterface{

	public  static function get_filters() {

		return array(
			'caldera_forms_get_form_processors' => array( 'run_action_register_processor', 10, 2 ),

		);

	}

	function run_action_register_processor($pr){
		$pr['run_action'] = array(
			"name"              =>  __('Run Action'),
			"description"       =>  __("Run Action on submission"),
			"author"            =>  'David Cramer',
			"author_url"        =>  'http://cramer.co.za',
			"processor"         =>  'htrun_action_process',
			"template"          =>  dirname( __FILE__ ) . "/run-action-config.php",
		);

		return $pr;

	}

	function run_action_process($config, $form){

		$data = array();
		foreach($form['fields'] as $field_id=>$field){
			$data[$field['slug']] = Caldera_Forms::get_field_data($field_id, $form);
		}

		do_action( $config['action'], $data, $form);

	}

} 
