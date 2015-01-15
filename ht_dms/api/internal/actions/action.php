<?php
/**
 * Abstract class that all internal API actions MUST extend
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2015 Josh Pollock
 */

namespace ht_dms\api\internal\actions;


abstract class action {

	/**
	 * Sets if POST data should be expected as JSON or not.
	 *
	 * @var bool
	 */
	public static $post_as_json = true;

	/**
	 * Define if this action should use GET or POST.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	public static function method() {
		return 'get';

	}

}
