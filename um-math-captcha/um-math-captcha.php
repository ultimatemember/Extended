<?php
/**
 * Plugin Name: Ultimate Member - Math Captcha in Register form
 * Plugin URI:  https://github.com/ultimatemember/Extended/tree/main/um-math-captcha
 * Description: This plugin adds Math Captcha field to the registration form.
 *
 * Author:     Ultimate Member
 * Author URI: http://ultimatemember.com/
 * License:    GPL v2 or later
 *
 * Text Domain: um-math-captcha
 * Domain Path: /languages
 *
 * Version: 1.1.0
 * UM version: 2.7.0
 *
 * Requires at least: 5.5
 * Requires PHP: 5.6
 *
 * @package UM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once ABSPATH . 'wp-admin/includes/plugin.php';

$plugin_data = get_plugin_data( __FILE__ );

define( 'um_math_captcha_url', plugin_dir_url( __FILE__ ) );
define( 'um_math_captcha_path', plugin_dir_path( __FILE__ ) );
define( 'um_math_captcha_plugin', plugin_basename( __FILE__ ) );
define( 'um_math_captcha_extension', $plugin_data['Name'] );
define( 'um_math_captcha_version', $plugin_data['Version'] );
define( 'um_math_captcha_textdomain', 'um-math-captcha' );
define( 'um_math_captcha_requires', '2.7.0' );


// Check dependencies.
if ( ! function_exists( 'um_math_captcha_check_dependencies' ) ) {
	function um_math_captcha_check_dependencies() {
		if ( ! defined( 'um_path' ) || ! function_exists( 'UM' ) || ! UM()->dependencies()->ultimatemember_active_check() ) {
			// UM is not active.
			add_action(
				'admin_notices',
				function () {
					// translators: %s - plugin name.
					echo '<div class="error"><p>' . wp_kses_post( sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-math-captcha' ), um_math_captcha_extension ) ) . '</p></div>';
				}
			);
		} else {
			require_once 'includes/class-um-math-captcha.php';
			UM()->set_class( 'Math_Captcha', true );
		}
	}
}
add_action( 'plugins_loaded', 'um_math_captcha_check_dependencies', 2 );


// Loads a plugin's translated strings.
if ( ! function_exists( 'um_math_captcha_plugins_loaded' ) ) {
	function um_math_captcha_plugins_loaded() {
		$locale = ( get_locale() !== '' ) ? get_locale() : 'en_US';
		load_textdomain( um_math_captcha_textdomain, WP_LANG_DIR . '/plugins/' . um_math_captcha_textdomain . '-' . $locale . '.mo' );
		load_plugin_textdomain( um_math_captcha_textdomain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}
add_action( 'plugins_loaded', 'um_math_captcha_plugins_loaded', 6 );
