<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       Ultimate Member Ltd.
 * @since      1.0.0
 *
 * @package    Um_Dummy_Accounts
 * @subpackage Um_Dummy_Accounts/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Um_Dummy_Accounts
 * @subpackage Um_Dummy_Accounts/includes
 * @author     Ultimate Member Ltd. <support@ultimatemember.com>
 */
class Um_Dummy_Accounts_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
		$locale = ( get_locale() != '' ) ? get_locale() : 'en_US';
        load_textdomain( 'um-dummy-accounts', WP_LANG_DIR . '/plugins/um-generate-dummy-user-accounts-' . $locale . '.mo');
		load_plugin_textdomain(
			'um-dummy-accounts',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
