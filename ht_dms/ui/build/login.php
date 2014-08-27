<?php
/**
 * @TODO What this does.
 *
 * @package   @TODO
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

namespace ht_dms\ui\build;


class login {

	function __construct() {
		add_filter( 'login_headerurl', array( $this, 'login_logo_url' ) );
		add_filter( 'login_headertitle', array( $this, 'login_logo_url_title' ) );
		add_action( 'wp_head', array( $this, 'login_style' ) );
		add_action( 'login_enqueue_scripts', array( $this, 'login_style' ) );
		add_filter( 'logout_url', array( $this, 'logout_url' ) );
	}

	function logout_url( $logout_url ) {
		$logout_url = ht_dms_home();

		return $logout_url;
	}


	function login_logo_url() {
		return ht_dms_home();

	}

	function title() {
		return apply_filters( 'ht_dms_name', 'HoloTree' );
	}

	function login_logo_url_title() {
		return $this->title();

	}

	function login_logo() {
		return apply_filters( 'ht_dms_login_logo', '' );
	}

	function login_style() {

		$logo = $this->login_logo();
		?>
		<style type="text/css">
			.login h1 a {
				margin: 0 auto;
				color: white;
				background-image: url(<?php echo $logo; ?>) !important;
				background-repeat: no-repeat;
				width: 300px !important;
				height: 150px !important;
				foo:bar;
				;

			}
			html, body{
				background-color: #EFC771;
			}

			.login form {
				background-color: #F4D99F;
			}

			input#wp-submit {
				background-color: #5A180A;
			}
		</style>
	<?php }

	static function output() {

		return holotree_dms_ui()->view_loaders()->content_wrap(
			wp_login_form(
				array( 'echo' => false, )
			)
		);
	}

	function force_login( $template ) {
		$template = trailingslashit( HT_DMS_VIEW_DIR ) .'login.php';
		$template = apply_filters( 'ht_dms_login_template', $template );

		return $template;
		
	}


} 
