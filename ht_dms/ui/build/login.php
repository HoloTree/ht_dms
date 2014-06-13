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
		add_filter( 'logout_url', array( $this, 'logout_url' ) );
	}

	function logout_url( $logout_url ) {
		$logout_url = site_url();

		return $logout_url;
	}


	function login_logo_url() {
		return site_url();

	}

	function title() {
		return apply_filters( 'ht_dms_name', 'HoloTree' );
	}

	function login_logo_url_title() {
		return $this->title();

	}

	function login_style() {
		$logo = trailingslashit( HT_DMS_UI_DIR ).'img/login-logo.jpg';
		/**
		 * Set login logo
		 *
		 * @param string $logo Login logo source url
		 *
		 * @return Loging logo source.
		 *
		 * @since 0.0.1
		 */
		$logo = apply_filters( 'ht_dms_login_logo', $logo );
		?>
		<style type="text/css">
			.entry-title a {
				margin: 0 auto;
				color: white;
				background-image: url(<?php echo $logo; ?>);
				background-repeat: no-repeat;
				width: 300px !important;
				height: 150px !important;
				;
				
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
