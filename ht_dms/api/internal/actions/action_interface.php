<?php
/**
 * Interface that all internal API actions MUST implement or they will fail validation
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\api\internal\actions;


interface action_interface {

	/**
	 * Will be called by API router. Must return the response.
	 *
	 * @param array $params An array of params, defined by self::args()
	 *
	 * @return mixed
	 */
	public static function act( $params );

	/**
	 * Params for this route
	 *
	 * Add an array of the names of GET or POST vars to pass into self::act()
	 *
	 * @return array
	 */
	public static function args();

	/**
	 * Define if this action should use GET or POST.
	 *
	 * This method should either be:
	 *
	 * return "GET"; or return "POST";
	 *
	 * @return string
	 */
	public static function method();

} 
