<?php
/**
 * Plugin Name: Ultimate Member - Block Disposable Email Domains
 * Plugin URI: https://www.ultimatemember.com/
 * Description: This provides a list of known Spam/Disposable Email Domains.
 * Version: 1.0.0
 * Author: Ultimate Member
 * Author URI: https://www.ultimatemember.com
 * Text Domain: um-block-emails
 *
 * @package UM_Extended_Block_Emails\Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}


if ( ! function_exists( 'um_extended_blockemails_loading_allowed' ) ) {
	/**
	 * Don't allow to run the plugin when Ultimate Member plugin is not active/installed
	 *
	 * @since 1.0.0
	 */
	function um_extended_blockemails_loading_allowed() {

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}

		// Search for ultimate-member plugin name.
		if ( ! is_plugin_active( 'ultimate-member/ultimate-member.php' ) ) {

			add_action( 'admin_notices', 'um_extended_blockemails_ultimatemember_requirement_notice' );

			return false;
		}

		return true;
	}

	if ( ! function_exists( 'um_extended_blockemails_ultimatemember_requirement_notice' ) ) {
		/**
		 * Display the notice after activation
		 *
		 * @since 1.5.0
		 */
		function um_extended_blockemails_ultimatemember_requirement_notice() {

			echo '<div class="notice notice-warning"><p>';
			printf(
				wp_kses( /* translators: %1$s - The Ultimate Member - Block Disposable Domains requires the latest versio. */
					__( 'The Ultimate Member - Block Disposable Domains requires the latest version of <a href="%1$s" target="_blank" rel="noopener noreferrer">Ultimate Member</a> plugin to be installed &amp; activated.', 'champ' ),
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
	if ( um_extended_blockemails_loading_allowed() === false ) {
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
 * @return Champ\Core
 */
function um_extended_blockemails_plugin() {
	/**
	 * Load core class
	 *
	 * @var $core
	 */
	static $core;

	if ( ! isset( $core ) ) {
		$core = new \UM_Extended_Block_Emails\Core( __FILE__ );
	}

	return $core;
}

um_extended_blockemails_plugin();
