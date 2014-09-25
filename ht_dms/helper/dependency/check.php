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

namespace ht_dms\helper\dependency;


class check {

	/**
	 * Dependent Plugins
	 *
	 * @since 0.0.3
	 *
	 * @return array|mixed|void
	 */
	function plugins() {
		$plugins = array(
			'pods-alternative-cache' 		=> 'http://pods.io/2014/04/16/introducing-pods-alternative-cache/',
			'pods-frontier-auto-template' 	=> 'http://pods.io/?p=182830',
			'pods-seo' 						=> 'http://wordpress.org/plugins/pods-seo/',
			'pods-ajax-views'				=> 'https://wordpress.org/plugins/pods-ajax-views/',
			'csv-importer-for-pods' 		=> 'https://wordpress.org/plugins/csv-importer-for-pods/',
		);

		/**
		 * Set plugins available in the plugin installer
		 *
		 * @param array $plugins Should be in the form of 'plugin-slug' => 'plugin-uri'
		 *
		 * @returns array
		 *
		 * @since 0.0.3
		 */
		$plugins = apply_filters( 'ht_dms_pods_plugins', $plugins );

		return $plugins;
	}

	/**
	 * Fetch plugin info via WordPress.org plugins API
	 *
	 * @param string $slug The plugin's slug (needs to match http://wordpress.org/plugins/{$slug})
	 *
	 * @return array
	 *
	 * @since 0.0.3
	 */
	function plugin_info_via_api( $slug ) {
		$key = 'ht_plugin_info';
		$url = "http://api.wordpress.org/plugins/info/1.0/{$slug}.json";
		$plugin_info = get_transient( $key );
		if ( !  is_array( $plugin_info ) ||  ! isset( $plugin_info[ $slug ] ) || empty( $plugin_info[ $slug ] ) ) {
			if ( curl_init( $url ) ) {
				$json = file_get_contents( $url );
				$obj  = json_decode( $json );
				$info = array (
					'Name'        => $obj->name,
					'Version'     => $obj->version,
					'Author'      => $obj->author,
					'AuthorURI'   => $obj->author_profile,
					'Description' => $obj->short_description,
				);

				$plugin_info[ $slug ] = $info;


			}

		}
		else{
			$info = $plugin_info[ $slug ];
		}

		return $info;

	}

	/**
	 * Get the base file for any installed plugin.
	 *
	 * @param string $uri The plugins WordPress.org page. IE 'http://wordpress.org/plugins/pods/'
	 *
	 * @return string|bool Either the base file for the plugin, IE 'pods/init.php' or false if plugin isn't installed.
	 *
	 * @since 0.0.3
	 */
	function plugin_file( $uri ) {
		$all_plugins = get_plugins();
		$plugin_uris = wp_list_pluck( $all_plugins, 'PluginURI' );
		$plugin_uris = array_flip( $plugin_uris );
		if ( array_key_exists( $uri, $plugin_uris ) ) {
			return  $plugin_uris[ $uri ];
		}

	}

	function install_or_activate( $slug ) {
		$plugins = $this->plugins();
		$uri = $plugins[ $slug ];

		$plugin_file = $this->plugin_file( $uri );
		$all_plugins = get_plugins();
		if ( isset( $all_plugins[ $plugin_file ][ 'Name' ] ) ) {
			$name = $all_plugins[ $plugin_file ][ 'Name' ];
		}
		else {
			$name = $slug;
		}

	//@todo use activate_plugin()
		//@todo in backaground
			//plugin is installed, but not active. So Activate
			if ( ! is_plugin_active( $plugin_file ) && $plugin_file ) {
				echo sprintf( '<div id="message" class="error"><p>%s</p></div>',
					sprintf(
						__( 'Activating %1$s', 'pods' ),
						$name )
				);
				$this->redirect( wp_nonce_url( self_admin_url( 'plugins.php?action=activate&plugin=' . $plugin_file ), 'activate-plugin_' . $plugin_file ) );

			}
			//plugin is not active or installed. So install.
			elseif ( ! is_plugin_active( $plugin_file ) && ! $plugin_file ) {
				echo sprintf( '<div id="message" class="error"><p>%s</p></div>',
					sprintf(
						__( 'Installing %1$s', 'pods' ),
						$name )
				);
				$this->redirect( wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . $component ), 'install-plugin_' . $component ) );
			}


	}

	/**
	 * Redirects to another page.
	 *
	 * Copypasta of pods_redirect()
	 *
	 * @param string $location The path to redirect to
	 * @param int $status Status code to use
	 *
	 * @return void
	 *
	 * @since 0.0.3
	 */
	private function redirect ( $location, $status = 302 ) {
		if ( !headers_sent() ) {
			wp_redirect( $location, $status );
			die();
		}
		else {
			die( '<script type="text/javascript">'
			     . 'document.location = "' . str_replace( '&amp;', '&', esc_js( $location ) ) . '";'
			     . '</script>' );
		}

	}

	function versions() {

	}

} 
