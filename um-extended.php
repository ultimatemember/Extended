<?php
/**
 * Plugin Name: Ultimate Member - Extended Features & Functionalities
 * Plugin URI: https://www.ultimatemember.com/
 * Description: Extended features & functionalities of Ultimate Member
 * Version: 1.0.0
 * Author: Ultimate Member
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

if ( ! function_exists( 'um_extended_blockemails_loading_allowed' ) ) {
	/**
	 * Don't allow to run the plugin when WP-MAIL-SMTP plugin is not active/installed
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
				wp_kses( /* translators: %1$s - The Ultimate Member - Extended Features & Functionalities plugin requires the latest versio. */
					__( 'The Ultimate Member - Extended Features & Functionalities plugin requires the latest version of <a href="%1$s" target="_blank" rel="noopener noreferrer">Ultimate Member</a> plugin to be installed &amp; activated.', 'champ' ),
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
 * Extended
 */
final class UM_Extended {

	/**
	 * Core object
	 *
	 * @var object $core
	 */
	public $core;

	/**
	 * Init
	 */
	public function __construct() {
		$this->block_emails();
	}

	/**
	 * Block Disposable Email Domains
	 */
	public function block_emails() {
		if ( ! isset( $this->core->block_emails ) ) {
			$this->core->block_emails = new UM_Extended_Block_Emails\Core( __FILE__ );
		}

		return $this->core->block_emails;
	}

	/**
	 * Cover Photo
	 */
	public function cover_photo() {
		if ( ! isset( $this->core->cover_photo ) ) {
			$this->core->cover_photo = new UM_Extended_Cover_Photo\Core();
		}

		return $this->core->cover_photo;
	}
	/**
	 * Global function-holder. Works similar to a singleton's instance().
	 *
	 * @since 1.0.0
	 *
	 * @return Champ\Core
	 */
	public function um_extended_plugin() {
		/**
		 * Load core class
		 *
		 * @var $core
		 */
		$core = new stdClass();

		if ( ! isset( $core->browser_detect ) ) {
			$core->browser_detect = new UM_Extended_Browser_Detect\Core();
		}

		if ( ! isset( $core->capitalize_names ) ) {
			$core->capitalize_names = new UM_Extended_Capitalize_Names\Core();
		}

		if ( ! isset( $core->country_flags ) ) {
			$core->country_flags = new UM_Extended_Country_Flags\Core();
		}

		return $core;
	}
}

/**
 * Extended function
 */
function um_extended_plugin() {
	return new UM_Extended();
}
um_extended_plugin();
