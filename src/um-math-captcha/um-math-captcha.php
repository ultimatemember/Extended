<?php
/**
 * Plugin Name: Ultimate Member - Math Captcha in Register form
 * Description:  This plugin adds math challenge in the Register forms
 * Version: 1.0.1
 * Author: Ultimate Member
 * Author URI: http://ultimatemember.com/
 * Text Domain: um-math-captcha * UM version: 2.1.0
 *
 * @package UM_Extended_Math_Captcha\Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( ! function_exists( 'um_extended_math_captcha_loading_allowed' ) ) {
	/**
	 * Don't allow to run the plugin when  Ultimate Member plugin is not active/installed
	 *
	 * @since 1.0.0
	 */
	function um_extended_math_captcha_loading_allowed() {

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}

		// Search for ultimate-member plugin name.
		if ( ! is_plugin_active( 'ultimate-member/ultimate-member.php' ) ) {

			add_action( 'admin_notices', 'um_extended_math_captcha_ultimatemember_requirement_notice' );

			return false;
		}

		return true;
	}

	if ( ! function_exists( 'um_extended_math_captcha_ultimatemember_requirement_notice' ) ) {
		/**
		 * Display the notice after activation
		 *
		 * @since 1.0.0
		 */
		function um_extended_math_captcha_ultimatemember_requirement_notice() {

			echo '<div class="notice notice-warning"><p>';
			printf(
				wp_kses( /* translators: %1$s - The Ultimate Member requires the latest version. */
					__( 'The Ultimate Member - Field Counter requires the latest version of <a href="%1$s" target="_blank" rel="noopener noreferrer">Ultimate Member</a> plugin to be installed &amp; activated.', 'um-extended' ),
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
	if ( um_extended_math_captcha_loading_allowed() === false ) {
		return;
	}

	/**
	* Autoloader with Composer
	*/
	if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
		require __DIR__ . '/vendor/autoload.php';
	}
}

/**
 * Global function-holder. Works similar to a singleton's instance().
 *
 * @since 1.0.0
 *
 * @return UM_Extended_Cover_Photo\Core
 */
function um_extended_math_captcha_plugin() {
	/**
	 * Load core class
	 *
	 * @var $core
	 */
	static $core;

	if ( ! isset( $core ) ) {
		$core = new \UM_Extended_Math_Captcha\Core();
	}

	return $core;
}
um_extended_math_captcha_plugin();
