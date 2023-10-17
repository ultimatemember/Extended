<?php
/**
 * Core class
 *
 * @package UM_Extended_API
 */

/**
 * Class Core to handle all plugin initialization.
 *
 * @since 1.0.0
 */
class UM_Extended_API {

	/**
	 * Domain
	 *
	 * @var $domain
	 */
	private $domain = '127.0.0.1';

	/**
	 * Port
	 *
	 * @var $port
	 */
	private $port = '5173';
	/**
	 * Port
	 *
	 * @var $assets
	 */
	private $assets = array(
		'settings',
	);

	/**
	 * Init
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'extended_menu' ) );
		add_action( 'admin_head', array( $this, 'dev_refresh_runtime' ) );

		add_action( 'wp_ajax_um_extended_extensions', array( $this, 'extensions' )  );
		add_action( 'wp_ajax_um_extended_activate', array( $this, 'extension_activate' )  );
		add_action( 'wp_ajax_um_extended_deactivate', array( $this, 'extension_deactivate' )  );
		add_action( 'wp_ajax_um_extended_disable_all_active', array( $this, 'disable_all_active' ) );

		add_filter( 'script_loader_tag', array( $this, 'script_loader_tag' ), 10, 3 );

		$handle = $this->js_handle( 'settings' );
		$url    = defined( 'UM_EXTENDED_IS_DEV' ) && UM_EXTENDED_IS_DEV
		? $this->get_dev_url() . ltrim( $handle, 'Extended/' ) . '/main.js'
		: $this->live_url( $handle );

		$this->js_preload_imports( $url );

		add_action(
			'admin_init',
			function() {
				if ( isset( $_REQUEST['page'] ) && 'um-extended' === $_REQUEST['page'] ) { //phpcs:ignore
					remove_all_actions( 'admin_notices' );
				}
			}
		);
	}

	/**
	 * Register Menu
	 */
	public function extended_menu() {
		add_menu_page(
			__( 'UM Extended', 'um-extended' ),
			'UM Extended',
			'manage_options',
			'um-extended',
			array( $this, 'settings_page' ),
			'dashicons-admin-users',
			'42.78578',
		);
	}

	/**
	 * Settings page
	 */
	public function settings_page() {

		$this->register_js( 'settings', array( 'jquery' ), array( 'plugin_url' => UM_EXTENDED_PLUGIN_URL, 'ajax_url' => admin_url('admin-ajax.php') ) );

		require_once UM_EXTENDED_PLUGIN_DIR . 'templates/settings.php';
	}

	/**
	 * Adds the RefreshRuntime.
	 *
	 * @since1.0.0
	 *
	 * @return void
	 */
	public function dev_refresh_runtime() {

		echo sprintf(
			'<script type="module">
		import RefreshRuntime from "%1$s@react-refresh"
		RefreshRuntime.injectIntoGlobalHook(window)
		window.$RefreshReg$ = () => {}
		window.$RefreshSig$ = () => (type) => type
		window.__vite_plugin_react_preamble_installed__ = true
		</script>',
			$this->get_dev_url(), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}

	/**
	 * Return the dev URL.
	 *
	 * @since 4.1.9
	 *
	 * @return string The dev URL.
	 */
	private function get_dev_url() {
		$protocol = is_ssl() ? 'https://' : 'http://';

		return $protocol . $this->domain . ':' . $this->port . '/';
	}

	/**
	 * Register the JS to enqueue.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $asset        The script to load.
	 * @param  array  $dependencies An array of dependencies.
	 * @param  mixed  $data         Any data to be localized.
	 * @param  string $object_name   The object name to use when localizing.
	 * @return void
	 */
	public function register_js( $asset, $dependencies = array(), $data = null, $object_name = 'um_extended' ) {
		$handle = $this->js_handle( $asset );

		if ( wp_script_is( $handle, 'registered' ) ) {
			return;
		}
		$url = defined( 'UM_EXTENDED_IS_DEV' ) && UM_EXTENDED_IS_DEV
			? $this->get_dev_url() . ltrim( $handle, 'Extended/' ) . '/main.js'
			: $this->live_url( $asset );

		if ( ! defined( 'UM_EXTENDED_IS_DEV' ) ) {
			$url = $this->load_manifest( $asset );
		}

		wp_register_script( $handle, $url, $dependencies, '1.0.0', true );
		wp_enqueue_script( $handle );
		$css_files = $this->load_manifest( $asset, true );
		
		if ( ! empty( $css_files ) && ! defined( 'UM_EXTENDED_IS_DEV' ) ) {
			foreach( $css_files  as $i => $css_file ) {
				$this->register_styles( $css_file, $handle );
			}
		}
		if ( empty( $data ) ) {
			return;
		}

		wp_localize_script(
			$handle,
			$object_name,
			$data
		);
	}

	/**
	 * Register the CSS to enqueue.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $asset    The script to load.
	 * @param  string $handle   Handle name
	 * @return void
	 */
	public function register_styles( $asset, $handle ) {
		
		if ( ! wp_script_is( $handle, 'registered' ) ) {
			return;
		}
	
		$url = UM_EXTENDED_PLUGIN_URL . 'dist/assets/' . $asset;  

		
		$asset =  pathinfo( $handle . '-' . $asset, PATHINFO_FILENAME );
		wp_register_style( $asset, $url, $url, '1.0.0', 'all' );
		wp_enqueue_style( $asset );

	}

	/**
	 * Get the JS asset handle.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $asset The asset to find the handle for.
	 * @return string        The asset handle.
	 */
	public function js_handle( $asset = '' ) {
		return basename( UM_EXTENDED_PLUGIN_URL ) . DIRECTORY_SEPARATOR . 'app/vue/' . $asset;
	}

	/**
	 * Live Plugin URL
	 *
	 * @param string $asset Asset name.
	 *
	 * @since 1.0.0
	 */
	public function live_url( $asset ) {

		$url = $this->load_manifest( $asset );

		return $url;
	}

	/**
	 * Filter the script loader tag if this is our script.
	 *
	 * @since 4.1.9
	 *
	 * @param  string $tag    The tag that is going to be output.
	 * @param  string $handle The handle for the script.
	 * @param string $src    The source.
	 * @return string        The modified tag.
	 */
	public function script_loader_tag( $tag, $handle = '', $src = '' ) {

		if ( strpos( $handle, 'Extended/app/' ) === false ) {
			return $tag;
		}
		// Remove the type and re-add it as module.
		$tag = preg_replace( '/type=[\'"].*?[\'"]/', '', $tag );
		$tag = preg_replace( '/<script/', '<script type="module"', $tag );

		return $tag;
	}

	/**
	 * Preload JS imports.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $url The asset to load imports for.
	 * @return void
	 */
	private function js_preload_imports( $url ) {
		$res  = '';
		$res .= '<link rel="modulepreload" crossorigin href="' . $url . '"/>';

		if ( ! empty( $res ) ) {
			add_action(
				'admin_head',
				function () use ( &$res ) {
					echo $res; // phpcs:ignore
				}
			);
			add_action(
				'wp_head',
				function () use ( &$res ) {
					echo $res; // phpcs:ignore
				}
			);
		}
	}

	/**
	 * Load Asset from Manifest
	 *
	 * @param string $asset The asset name.
	 */
	public function load_manifest( $asset, $css_dependencies = false ) {

		$manifest_items = $this->get_manifest();
		if ( isset( $manifest_items[ 'app/vue/' . $asset . '/main.js' ] ) ) {
			if ( $css_dependencies ) {
				return $manifest_items[ 'app/vue/' . $asset . '/main.js' ]['css'];
			}
			return UM_EXTENDED_PLUGIN_URL . 'dist/assets/' . $manifest_items[ 'app/vue/' . $asset . '/main.js' ]['file'];
		}
	}

	/**
	 * Get the manifest to load assets from.
	 *
	 * @since 4.1.9
	 *
	 * @return array An array of files.
	 */
	private function get_manifest() {
		static $file = null;
		if ( $file ) {
			return $file;
		}

		$manifestJson = ''; //phpcs:ignore
		require UM_EXTENDED_PLUGIN_DIR . 'dist/manifest.php';

		$file = json_decode( $manifestJson, true ); //phpcs:ignore

		return $file;
	}

	/**
	 * Get Extensions
	 */
	public function extensions() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return wp_send_json_error( 'restricted_page' );
		}

		$search_extension = $_REQUEST['extension']; //phpcs:ignore
		$extensions = '';
		require UM_EXTENDED_PLUGIN_DIR . 'dist/extensions.php';

		$extensions = json_decode( stripslashes( $extensions ), true ); //phpcs:ignore
		
		$active_extensions = get_option( 'um_extended_active_extensions' )? : array();


		$extensions['active_extensions'] = array();
		$extensions['all_active_enabled'] = get_option( 'um_extended_enable_active' ) ? true:false;
		$extensions['all_extensions'] = $extensions['extensions'];
		foreach( $active_extensions as $i => $ae ) {
			$extensions['all_extensions'][ $i ]['is_active'] = false;
			$extensions['active_extensions'][ $i ]['is_active'] = false;
			
			if( isset( $extensions['extensions'][ $i ] ) ) {
				$extensions['active_extensions'][ $i ] = $extensions['extensions'][ $i ];
				$extensions['active_extensions'][ $i ]['is_active'] = true;
				unset( $extensions['extensions'][ $i ] );
				$extensions['all_extensions'][ $i ]['is_active'] = true;
			} 
			
		}

		if( ! empty( $search_extension ) ) {
			foreach( $extensions['all_extensions'] as $idx => $ext ) {
				if( $idx === $search_extension ) {
					if ( isset( $extensions['active_extensions'][ $idx ] ) ) {
						$extensions['all_extensions'][ $i ]['is_active'] = true;
						$extensions['active_extensions'][ $idx ]['is_active'] = true;
						return wp_send_json_success( $extensions['active_extensions'][ $idx ] );
					} else if ( isset( $extensions['extensions'][ $idx ] ) ) {
						$extensions['all_extensions'][ $i ]['is_active'] = false;
						$extensions['extensions'][ $idx ]['is_active'] = false;
						return wp_send_json_success( $extensions['extensions'][ $idx ] );
					} else{
						return wp_send_json_error( __('Something went wrong', 'um-extended' ) );
					}
				}
			}
		}

		return wp_send_json_success( $extensions );
	}

	public function extension_activate() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return wp_send_json_error( 'restricted_page' );
		}

		$search_extension = $_REQUEST['extension']; //phpcs:ignore
		$extensions = '';
		require UM_EXTENDED_PLUGIN_DIR . 'dist/extensions.php';

		$extensions = json_decode( stripslashes( $extensions ), true ); //phpcs:ignore
		
		$active_extensions = get_option( 'um_extended_active_extensions' )? : array();

		if ( isset( $extensions['extensions'][ $search_extension ] ) ) {
			if( isset( $active_extensions[ $search_extension ] ) ) {
				return wp_send_json_error( __( 'Extension is already active.', 'um-extended' ) );
			} else {
				$active_extensions[ $search_extension ] = $extensions['extensions'][ $search_extension ];
				update_option( 'um_extended_active_extensions', $active_extensions ); 
				return wp_send_json_success( __( 'Extension has been activated', 'um-extended' ) );
			}
		}
	}


	public function extension_deactivate() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return wp_send_json_error( 'restricted_page' );
		}

		$search_extension = $_REQUEST['extension']; //phpcs:ignore
		$extensions = '';
		require UM_EXTENDED_PLUGIN_DIR . 'dist/extensions.php';

		$extensions = json_decode( stripslashes( $extensions ), true ); //phpcs:ignore
		
		$active_extensions = get_option( 'um_extended_active_extensions' )? : array();

		if ( isset( $extensions['extensions'][ $search_extension ] ) ) {
			if( isset( $active_extensions[ $search_extension ] ) ) {
				unset( $active_extensions[ $search_extension ] );
				update_option( 'um_extended_active_extensions', $active_extensions ); 
				return wp_send_json_success( __( 'Extension has been deactivated', 'um-extended' ) );
			}
		}
	}

	public function disable_all_active(){

		if ( ! current_user_can( 'manage_options' ) ) {
			return wp_send_json_error( 'restricted_page' );
		}

		$state = $_REQUEST['state']; //phpcs:ignore
		if( 'false' === $state ) {
			update_option( 'um_extended_enable_active', true ); 
			return wp_send_json_success( __( 'All Active extensions have been enabled.', 'um-extended' ) );
		} else {
			delete_option( 'um_extended_enable_active' ); 
			return wp_send_json_success( __( 'All Active extensions have been disabled.', 'um-extended' ) );
		}
	}

	/**
	 * Get Active Extensions
	 */
	public function get_active_extensions() {
		$active_extensions = get_option( 'um_extended_active_extensions' )? : array();
		return $active_extensions;
	}

}
