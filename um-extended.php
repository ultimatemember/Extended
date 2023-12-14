<?php
/**
 * Plugin Name: Ultimate Member - Extended Features & Functionalities
 * Plugin URI: https://www.ultimatemember.com/
 * Description: Extended features & functionalities of Ultimate Member
 * Version: 2.0.4
 * Author: Ultimate Member Group Ltd.
 * Author URI: https://www.ultimatemember.com
 * Text Domain: um-extended
 *
 * @package UM_Extended
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
define( 'UM_IS_EXTENDED', true );
define( 'UM_EXTENDED_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'UM_EXTENDED_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

if ( ! function_exists( 'um_extended_loading_allowed' ) ) {
	/**
	 * Don't allow to run the plugin when Ultimate Member plugin is not active/installed
	 *
	 * @since 1.0.0
	 */
	function um_extended_loading_allowed() {

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}

		// Search for ultimate-member plugin name.
		if ( ! is_plugin_active( 'ultimate-member/ultimate-member.php' ) ) {

			add_action( 'admin_notices', 'um_extended_ultimatemember_requirement_notice' );

			return false;
		}

		return true;
	}

	if ( ! function_exists( 'um_extended_ultimatemember_requirement_notice' ) ) {
		/**
		 * Display the notice after activation
		 *
		 * @since 1.0.0
		 */
		function um_extended_ultimatemember_requirement_notice() {

			echo '<div class="notice notice-warning"><p>';
			printf(
				wp_kses( /* translators: %1$s - The Ultimate Member - Extended Features & Functionalities plugin requires the latest versio. */
					__( 'The Ultimate Member - Extended Features & Functionalities plugin requires the latest version of <a href="%1$s" target="_blank" rel="noopener noreferrer">Ultimate Member</a> plugin to be installed &amp; activated.', 'um-extended' ),
					array(
						'a'      => array(
							'href'   => array(),
							'target' => array(),
							'rel'    => array(),
						),
						'strong' => array(),
					)
				),
				'https://wordpress.org/plugins/ultimate-member/'
			);
			echo '</p></div>';

			if ( isset( $_GET['activate'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				unset( $_GET['activate'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			}
		}
	}

	// Stop the plugin loading.
	if ( um_extended_loading_allowed() === false ) {
		return;
	}

	/**
	 * Autoloader with Composer
	 */
	if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
		require __DIR__ . '/vendor/autoload.php';
	}

	/**
	 * Extended API
	 */
	if ( is_readable( __DIR__ . '/includes/class-um-extended-api.php' ) ) {
		require_once __DIR__ . '/includes/class-um-extended-api.php';
		new UM_Extended_API();
	}
}

/**
 * Extended
 */
#[AllowDynamicProperties]
final class UM_Extended {

	/**
	 * Instance
	 *
	 * @var UM_Extended the single instance of the class
	 */
	protected static $instance;

	/**
	 * Main UM_Extended Instance
	 *
	 * Ensures only one instance of UM is loaded or can be loaded.
	 *
	 * @since 1.0
	 * @static
	 * @see UM()
	 * @return UM - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->um_extended_construct();
		}

		return self::$instance;
	}

	/**
	 * Core object
	 *
	 * @var array $classes
	 */
	public $classes = array();


	/**
	 * Functions
	 *
	 * @var array $funcs
	 */
	public $funcs = array();

	/**
	 * Dynamically load extensions.
	 * Directory name and class should match patterns:
	 * e.g. directory 'um-user-shortcode' should have a class `UM_Extended_User_Shortcode`
	 */
	public function um_extended_construct() {

		if ( empty( get_option( 'um_extended_enable_active' ) ) ) {
			return;
		}

		$um_extended_api = new UM_Extended_API();

		$extensions = array_keys( $um_extended_api->get_active_extensions() );

		foreach ( $extensions as $i => $ext ) {

			$name      = str_replace( 'um-', '', basename( $ext ) );
			$slug      = $name;
			$func_name = str_replace( '-', '_', $name );
			$name      = str_replace( '-', ' ', $name );
			$name      = ucwords( $name );
			$name      = str_replace( ' ', '_', $name );
			$name      = str_replace( '_Wp', '_WP', $name );
			$name      = str_replace( 'Wp_', 'WP_', $name );

			if ( 'wpcli' === $slug ) {
				$class_name = 'UM_WPCLI\Core';
			} else {
				$class_name = 'UM_Extended_' . $name . '\Core';
			}

			if ( class_exists( $class_name ) ) {
				$this->funcs[ $func_name ] = function() use ( $class_name, $slug ) {

					if ( ! isset( $this->classes[ $slug ] ) ) {
						$this->classes[ $slug ] = new $class_name( __FILE__ );
					}
					return $this->classes[ $slug ];
				};

				call_user_func( array( $this, $func_name ) );
			} else {
				if ( ! defined( 'WP_CLI' ) ) {
					wp_die( esc_attr( 'Invalid Class Name: ' . $class_name ) );
				}
			}
		}

	}

	/**
	 * Call function
	 *
	 * @param string $name Function name.
	 * @param array  $arguments Function args.
	 */
	public function __call( $name, $arguments ) {
		if ( isset( $this->funcs[ $name ] ) ) {
			return call_user_func( $this->funcs[ $name ], $arguments );
		}

		die( 'Nothin\'?' );
	}
}

/**
 * Extended function
 */
function um_extended_plugin() {

	return UM_Extended::instance();
}
um_extended_plugin();
