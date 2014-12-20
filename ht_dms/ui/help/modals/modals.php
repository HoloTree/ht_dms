<?php
/**
 * Base class for modals
 *
 * @package   @ht_dms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\ui\help\modals;


abstract class modals {

	/**
	 * @var string Id for trigger element. Will get '-trigger' appended.
	 *
	 * @since 0.3.0
	 */
	public $trigger_id;

	/**
	 * @var string Id for content element. Will get '-content' appended.
	 *
	 * @since 0.3.0
	 */
	public $content_id;

	/**
	 * var string The size of the modal.
	 *
	 *  @since 0.3.0
	 */
	public $size = 'medium';

	/**
	 * Constructor. used to invalidate if empty params or run $this->make_modal()
	 *
	 * @since 0.3.0
	 */
	function __construct() {
		if ( ! is_string( $this->trigger_id )  ) {
			return;
		}else {
			if ( ! is_string( $this->content_id ) ) {
				$this->content_id = $this->trigger_id;
			}

			$this->make_modal();

		}
	}

	/**
	 * Actually make the modal
	 *
	 * @access protected
	 *
	 * @since 0.3.0
	 *
	 */
	protected function make_modal() {
		$trigger_id = $this->trigger_id.'-trigger';
		$content_id = $this->content_id.'-content';
		$size = $this->size;
		$content = $this->content();

		new \holotree\modal\foundation( $trigger_id, $content_id, $content, $size );

	}

	/**
	 * Conditionally open the modal on load if $this->conditional() evaluates to true.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	public function js_css() {
		if ( $this->conditional() ) {
			$script[] = '<script type="text/javascript">';
			$script[] = 'jQuery(document).ready(function(){jQuery("'.$this->trigger_id.'").foundation("reveal", "open")});';
			$script[] = '</script>';

			return implode( '', $script );
		}

	}

	/**
	 * Conditional for auto-opening. If want always open: don't replace in final class.
	 *
	 * @since 0.3.0
	 *
	 * @return bool
	 */
	public function conditional() {
		return true;

	}


} 
