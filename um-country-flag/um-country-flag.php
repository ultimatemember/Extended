<?php
/*
Plugin Name: Ultimate Member - Country Flag
Plugin URI: https://ultimatemember.com/extensions/country-flag
Description: Display Country flag in Member Directory and User Profiles.
Version: 1.1
Author: Ultimate Member
Author URI: http://ultimatemember.com/
Text Domain: um-country-flag
Domain Path: /languages
UM version: 2.1.0
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'UM_COUNTRY_FLAG_URL', plugin_dir_url( __FILE__ ) );

/**
 * The main UM_Country_Flag class.
 */
class UM_Country_Flag {

	/**
	 * Setup class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'um_after_profile_header_name', array( $this, 'display_flag' ) ); // Display country flag to user profiles pages.
		add_action( 'um_members_after_user_name', array( $this, 'display_flag' ) ); // Display country flag to Member Directory.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_library' ) ); // Enqueue the country flag library.
	}

	/**
	 * Display Country Flag.
	 */
	public function display_flag() {

		// Check if Ultimate Member plugin is active.
		if ( class_exists( 'UM' ) ) {

			$user_country = um_user( 'country' ); // Get the user's country.
			$array_can = UM()->builtin()->get( 'countries' ); // Get the array of all available countries.
			$result_array = array_search( $user_country, $array_can ); // Pull the country code of the user's country.

			// Output the country flag.
			if ( ! empty( $user_country ) ) {
				?>
				<span class="fi fi-<?php echo esc_attr( strtolower( $result_array ) ); ?>" aria-label="<?php echo esc_attr( $user_country ); ?>"></span>
				<?php
			}
		}
	}

	/**
	 * Enqueue Country Flag library.
	 */
	public function enqueue_library() {
		wp_register_style( 'country_flag_lib', UM_COUNTRY_FLAG_URL . 'assets/flag-icons.min.css' ); // Register the flag-icon.min.css library from CDN.
		wp_enqueue_style( 'country_flag_lib' ); // Enqueue the library.
	}
}

new UM_Country_Flag();