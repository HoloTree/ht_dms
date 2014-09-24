<?php
/**
 * Interface adapted from http://carlalexander.ca/mediator-pattern-wordpress/
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link
 * @copyright 2014 Josh Pollock
 * /
 *
* /**
 * Action_Hook_SubscriberInterface is used by an object that needs to subscribe to
 * WordPress action hooks.
 */
interface Action_Hook_SubscriberInterface {
	/**
	 * Returns an array of actions that the object needs to be subscribed to.
	 *
	 * The array key is the name of the action hook. The value can be:
	 *
	 *  * The method name
	 *  * An array with the method name and priority
	 *  * An array with the method name, priority and number of accepted arguments
	 *
	 * For instance:
	 *
	 *  * array('action_name' => 'method_name')
	 *  * array('action_name' => array('method_name', $priority))
	 *  * array('action_name' => array('method_name', $priority, $accepted_args))
	 *
	 * @return array
	 */
	public static function get_actions();
}

/**
 * Filter_Hook_SubscriberInterface is used by an object that needs to subscribe to
 * WordPress filter hooks.
 */
interface Filter_Hook_SubscriberInterface {
	/**
	 * Returns an array of filters that the object needs to be subscribed to.
	 *
	 * The array key is the name of the filter hook. The value can be:
	 *
	 *  * The method name
	 *  * An array with the method name and priority
	 *  * An array with the method name, priority and number of accepted arguments
	 *
	 * For instance:
	 *
	 *  * array('filter_name' => 'method_name')
	 *  * array('filter_name' => array('method_name', $priority))
	 *  * array('filter_name' => array('method_name', $priority, $accepted_args))
	 *
	 * @return array
	 */
	public static function get_filters();
}
/**
 * Hook_SubscriberInterface is used by an object that needs to subscribe to
 * WordPress filter or action hooks.
 */
interface Hook_SubscriberInterface extends Filter_Hook_SubscriberInterface, Action_Hook_SubscriberInterface {

}

