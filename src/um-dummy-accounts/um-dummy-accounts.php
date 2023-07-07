<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              Ultimate Member Ltd.
 * @since             1.0.0
 * @package           Um_Dummy_Accounts
 *
 * @wordpress-plugin
 * Plugin Name:       Ultimate Member - Generate Dummy Accounts
 * Plugin URI:        https://ultimatemember.com/
 * Description:       This plugin enables you to generate dummies in Ultimate Member
 * Version:           1.0.0
 * Author:            Ultimate Member Ltd.
 * Author URI:        Ultimate Member Ltd.
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       um-dummy-accounts
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-um-dummy-accounts-activator.php
 */
function activate_Um_Dummy_Accounts() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-um-dummy-accounts-activator.php';
	Um_Dummy_Accounts_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-um-dummy-accounts-deactivator.php
 */
function deactivate_Um_Dummy_Accounts() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-um-dummy-accounts-deactivator.php';
	Um_Dummy_Accounts_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_Um_Dummy_Accounts' );
register_deactivation_hook( __FILE__, 'deactivate_Um_Dummy_Accounts' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-um-dummy-accounts.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_Um_Dummy_Accounts() {

	$plugin = new Um_Dummy_Accounts();
	$plugin->run();

}
run_Um_Dummy_Accounts();
