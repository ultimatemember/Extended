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
		 * @since 1.0.0
		 */
		function um_extended_blockemails_ultimatemember_requirement_notice() {

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
		$this->browser_detect();
		$this->capitalize_names();
		$this->country_flags();
		$this->cover_photo();
		$this->cron_delete_users();
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
	 * Browser Detect
	 */
	public function browser_detect() {
		if ( ! isset( $this->core->browser_detect ) ) {
			$this->core->browser_detect = new UM_Extended_Browser_Detect\Core();
		}

		return $this->core->browser_detect;
	}

	/**
	 * Capitalize Names
	 */
	public function capitalize_names() {
		if ( ! isset( $this->core->capitalize_names ) ) {
			$this->core->capitalize_names = new UM_Extended_Capitalize_Names\Core();
		}

		return $this->core->capitalize_names;
	}

	/**
	 * Country Flags
	 */
	public function country_flags() {
		if ( ! isset( $this->core->country_flags ) ) {
			$this->core->country_flags = new UM_Extended_Cover_Photo\Core();
		}

		return $this->core->country_flags;
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
	 * Cron Delete Users
	 */
	public function cron_delete_users() {
		if ( ! isset( $this->core->cron_delete_users ) ) {
			$this->core->cron_delete_users = new UM_Extended_Cron_Delete_Users\Core();
		}

		return $this->core->cron_delete_users;
	}
}

/**
 * Extended function
 */
function um_extended_plugin() {

	static $core;

	if ( empty( $core ) ) {
		$core = new UM_Extended();
	}

	return $core;
}
um_extended_plugin();
