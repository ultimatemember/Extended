<?php
/*
Plugin Name: Ultimate Member - Yoast SEO
Plugin URI: https://ultimatemember.com/extensions/yoast/
Description: Enables Yoast OGs & tags in UM Profiles and adds user profiles in author's sitemap
Version: 1.0
Author: Ultimate Member
Author URI: http://ultimatemember.com/
Text Domain: um-yoast-seo
Domain Path: /languages
UM version: 2.1.0
*/

if ( ! defined( 'ABSPATH' ) ) exit;

require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

$plugin_data = get_plugin_data( __FILE__ );

define( 'um_yoast_seo_url', plugin_dir_url( __FILE__  ) );
define( 'um_yoast_seo_path', plugin_dir_path( __FILE__ ) );
define( 'um_yoast_seo_plugin', plugin_basename( __FILE__ ) );
define( 'um_yoast_seo_extension', $plugin_data['Name'] );
define( 'um_yoast_seo_version', $plugin_data['Version'] );
define( 'um_yoast_seo_textdomain', 'um-yoast-seo' );
define( 'um_yoast_seo_requires', '2.1.0' );


if ( ! function_exists( 'um_yoast_seo_plugins_loaded' ) ) {
	/**
	 * Text-domain loading
	 */
	function um_yoast_seo_plugins_loaded() {
		$locale = ( get_locale() != '' ) ? get_locale() : 'en_US';
		load_textdomain( um_yoast_seo_textdomain, WP_LANG_DIR . '/plugins/' . um_yoast_seo_textdomain . '-' . $locale . '.mo' );
		load_plugin_textdomain( um_yoast_seo_textdomain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
	add_action( 'plugins_loaded', 'um_yoast_seo_plugins_loaded', 0 );
}


if ( ! function_exists( 'um_yoast_seo_check_dependencies' ) ) {
	/**
	 * Check dependencies in core
	 */
	function um_yoast_seo_check_dependencies() {
		if ( ! defined( 'um_path' ) || ! file_exists( um_path  . 'includes/class-dependencies.php' ) ) {
			//UM is not installed
			function um_yoast_seo_dependencies() {
				echo '<div class="error"><p>' . sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-yoast-seo' ), um_yoast_seo_extension ) . '</p></div>';
			}

			add_action( 'admin_notices', 'um_yoast_seo_dependencies' );
		} else {

			if ( ! function_exists( 'UM' ) ) {
				require_once um_path . 'includes/class-dependencies.php';
				$is_um_active = um\is_um_active();
			} else {
				$is_um_active = UM()->dependencies()->ultimatemember_active_check();
			}

			if ( ! $is_um_active ) {
				//UM is not active
				function um_yoast_seo_dependencies() {
					echo '<div class="error"><p>' . sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-yoast-seo' ), um_yoast_seo_extension ) . '</p></div>';
				}

				add_action( 'admin_notices', 'um_yoast_seo_dependencies' );

			} /*elseif ( true !== UM()->dependencies()->compare_versions( um_yoast_seo_requires, um_yoast_seo_version, 'online', um_yoast_seo_extension ) ) {
				//UM old version is active
				function um_yoast_seo_dependencies() {
					echo '<div class="error"><p>' . UM()->dependencies()->compare_versions( um_yoast_seo_requires, um_yoast_seo_version, 'online', um_yoast_seo_extension ) . '</p></div>';
				}

				add_action( 'admin_notices', 'um_yoast_seo_dependencies' );

			}*/ else {
				require_once um_yoast_seo_path . 'includes/core/um-yoast-seo-init.php';
			}
		}
	}
	add_action( 'plugins_loaded', 'um_yoast_seo_check_dependencies', -20 );
}


if ( ! function_exists( 'um_yoast_seo_activation_hook' ) ) {
	/**
	 * Plugin Activation
	 */
	function um_yoast_seo_activation_hook() {
		//first install
		$version = get_option( 'um_yoast_seo_version' );
		if ( ! $version ) {
			update_option( 'um_yoast_seo_last_version_upgrade', um_yoast_seo_version );
		}

		if ( $version != um_yoast_seo_version ) {
			update_option( 'um_yoast_seo_version', um_yoast_seo_version );
		}

		//run setup
		if ( ! class_exists( 'um_ext\um_yoast_seo\core\Yoast_SEO_Setup' ) ) {
			require_once um_yoast_seo_path . 'includes/core/class-yoast-seo-setup.php';
		}

		$Yoast_SEO_setup = new um_ext\um_yoast_seo\core\Yoast_SEO_Setup();
		$Yoast_SEO_setup->run_setup();
	}
	register_activation_hook( um_yoast_seo_plugin, 'um_yoast_seo_activation_hook' );
}