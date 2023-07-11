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
	 * Init
	 */
	public function um_extended_construct() {
		$this->block_emails();
		$this->browser_detect();
		$this->capitalize_names();
		$this->country_flags();
		$this->cover_photo();
		$this->cron_delete_users();
		$this->cron_resend_activate_email();
		$this->dummy_accounts();
		$this->field_counter();
	}

	/**
	 * Block Disposable Email Domains
	 */
	public function block_emails() {
		if ( ! isset( $this->classes['block_emails'] ) ) {
			$this->classes['block_emails'] = new UM_Extended_Block_Emails\Core( __FILE__ );
		}

		return $this->classes['block_emails'];
	}

	/**
	 * Browser Detect
	 */
	public function browser_detect() {
		if ( ! isset( $this->classes['browser_detect'] ) ) {
			$this->classes['browser_detect'] = new UM_Extended_Browser_Detect\Core();
		}

		return $this->classes['browser_detect'];
	}

	/**
	 * Capitalize Names
	 */
	public function capitalize_names() {
		if ( ! isset( $this->classes['capitalize_names'] ) ) {
			$this->classes['capitalize_names'] = new UM_Extended_Capitalize_Names\Core();
		}

		return $this->classes['capitalize_names'];
	}

	/**
	 * Country Flags
	 */
	public function country_flags() {
		if ( ! isset( $this->classes['country_flags'] ) ) {
			$this->classes['country_flags'] = new UM_Extended_Country_Flags\Core();
		}

		return $this->classes['country_flags'];
	}

	/**
	 * Cover Photo
	 */
	public function cover_photo() {
		if ( ! isset( $this->classes['cover_photo'] ) ) {
			$this->classes['cover_photo'] = new UM_Extended_Cover_Photo\Core();
		}

		return $this->classes['cover_photo'];
	}

	/**
	 * Cron Delete Users
	 */
	public function cron_delete_users() {
		if ( ! isset( $this->classes['cron_delete_users'] ) ) {
			$this->classes['cron_delete_users'] = new UM_Extended_Cron_Delete_Users\Core();
		}

		return $this->classes['cron_delete_users'];
	}

	/**
	 * Cron Resend Activation Email
	 */
	public function cron_resend_activate_email() {
		if ( ! isset( $this->classes['cron_resend_activate_email'] ) ) {
			$this->classes['cron_resend_activate_email'] = new UM_Extended_CronJob_Email_Activation\Core();
		}

		return $this->classes['cron_resend_activate_email'];
	}

	/**
	 * Dummy Accounts
	 */
	public function dummy_accounts() {
		if ( ! isset( $this->classes['dummy_accounts'] ) ) {
			$this->classes['dummy_accounts'] = new UM_Extended_Dummy_Accounts\Core();
		}

		return $this->classes['dummy_accounts'];
	}

	/**
	 * Field Counter
	 */
	public function field_counter() {
		if ( ! isset( $this->classes['field_counter'] ) ) {
			$this->classes['field_counter'] = new UM_Extended_Field_Counter\Core();
		}

		return $this->classes['field_counter'];
	}
}

/**
 * Extended function
 */
function um_extended_plugin() {

	return UM_Extended::instance();
}
um_extended_plugin();
