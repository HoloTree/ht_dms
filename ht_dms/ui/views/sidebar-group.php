<?php
/**
 * @The sidebar for groups
 * @package   @holotree
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

global $post;
$id = $post->ID;
$ui = holotree_dms_ui();

$content = $ui->views()->group_sidebar_widgets( $id );
return $ui->elements()->sidebar_wrapper( $content );
