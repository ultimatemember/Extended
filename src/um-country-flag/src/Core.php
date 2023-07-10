<?php
/**
 * Core class
 *
 * @package UM_Extended_Country_Flags\Core
 */

namespace UM_Extended_Country_Flags;

/**
 * Class Core to handle all plugin initialization.
 *
 * @since 1.0.0
 */
class Core {

	/**
	 * Init
	 */
	public function __construct() {
		add_action( 'um_after_profile_header_name', array( $this, 'display_flag' ) ); // Display country flag to user profiles pages.
		add_action( 'um_members_after_user_name', array( $this, 'display_flag' ) ); // Display country flag to Member Directory.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_library' ) ); // Enqueue the country flag library.
	}

	/**
	 * Get Plugin URL
	 */
	public function plugin_url() {

		if ( defined( 'UM_EXTENDED_PLUGIN_URL' ) && \UM_EXTENDED_PLUGIN_URL ) {
			return \UM_EXTENDED_PLUGIN_URL . '/src/um-country-flag/';
		}

		return UM_EXTENDED_COUNTRY_FLAG_URL;
	}

	/**
	 * Display Country Flag.
	 */
	public function display_flag() {

		$user_country = um_user( 'country' ); // Get the user's country.
		$array_can    = UM()->builtin()->get( 'countries' ); // Get the array of all available countries.
		$result_array = array_search( $user_country, $array_can, true ); // Pull the country code of the user's country.

		// Output the country flag.
		if ( ! empty( $result_array ) ) {
			?>
			<span class="fi fi-<?php echo esc_attr( strtolower( $result_array ) ); ?>" aria-label="<?php echo esc_attr( $user_country ); ?>"></span>
			<?php
		}
	}

	/**
	 * Enqueue Country Flag library.
	 */
	public function enqueue_library() {
		wp_register_style( 'country_flag_lib', $this->plugin_url() . 'assets/flag-icons.min.css', array(), '1.0.0', 'all' ); // Register the flag-icon.min.css library from CDN.
		wp_enqueue_style( 'country_flag_lib' ); // Enqueue the library.
	}
}
