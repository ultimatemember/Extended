<?php
/**
 * Plugin Name:       Ultimate Member - Regenerate Thumbnails
 * Plugin URI:        https://ultimatemember.com
 * Description:       Regenerates profile photo thumbnails
 * Version:           1.0
 * Author:            UMDevs.com
 * Author URI:        https://ultimatemember.com
 * Text Domain:       um-regenerate-thumbnails
 * Domain Path:       /languages
 */

require_once( ABSPATH.'wp-admin/includes/plugin.php' );

$plugin_data = get_plugin_data( __FILE__ );

define( 'um_regenerate_thumbs_url', plugin_dir_url( __FILE__  ) );
define( 'um_regenerate_thumbs_path', plugin_dir_path( __FILE__ ) );
define( 'um_regenerate_thumbs_plugin', plugin_basename( __FILE__ ) );
define( 'um_regenerate_thumbs_extension', $plugin_data['Name'] );
define( 'um_regenerate_thumbs_version', $plugin_data['Version'] );

define('um_regenerate_thumbs_requires', '2.0');

function um_regenerate_thumbs_plugins_loaded() {
    load_plugin_textdomain( 'um-regenerate-thumbnails', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'um_regenerate_thumbs_plugins_loaded', 0 );

add_action( 'plugins_loaded', 'um_regenerate_thumbs_check_dependencies', -20 );

if ( ! function_exists( 'um_regenerate_thumbs_check_dependencies' ) ) {
    function um_regenerate_thumbs_check_dependencies() {
        if ( defined( 'um_path' ) ) {
   
                require_once um_regenerate_thumbs_path . 'includes/core/um-regenerate-thumbnails-init.php';
        }
    }
}


register_activation_hook( um_regenerate_thumbs_plugin, 'um_regenerate_thumbs_activation_hook' );
function um_regenerate_thumbs_activation_hook() {
    //first install
    $version = get_option( 'um_regenerate_thumbs_version' );
    if ( ! $version )
        update_option( 'um_regenerate_thumbs_last_version_upgrade', um_regenerate_thumbs_version );

    if ( $version != um_regenerate_thumbs_version )
        update_option( 'um_regenerate_thumbs_version', um_regenerate_thumbs_version );
}