<?php
/*
Plugin Name: Ultimate Member - Password Strength Meter
Plugin URI: https://ultimatemember.com/extensions/yoast/
Description: Displays password strength meter in Register, Reset Password and Change Password forms.
Version: 1.0
Author: Ultimate Member
Author URI: http://ultimatemember.com/
Text Domain: um-pass-strength
Domain Path: /languages
UM version: 2.5.0
*/

if ( ! defined( 'ABSPATH' ) ) exit;

require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

$plugin_data = get_plugin_data( __FILE__ );

define( 'um_pass_strength_url', plugin_dir_url( __FILE__  ) );
define( 'um_pass_strength_path', plugin_dir_path( __FILE__ ) );
define( 'um_pass_strength_plugin', plugin_basename( __FILE__ ) );
define( 'um_pass_strength_extension', $plugin_data['Name'] );
define( 'um_pass_strength_version', $plugin_data['Version'] );
define( 'um_pass_strength_textdomain', 'um-pass-strength' );
define( 'um_pass_strength_requires', '2.5.0' );



/**
* Enqueue scripts & styles
*/
function um_pass_strength_enqueue() {
     wp_enqueue_script( 'um-pass-strength', um_pass_strength_url . 'assets/js/um-pass-strength.js', array( 'um_scripts', 'jquery' ), um_pass_strength_version, true );
     wp_enqueue_script( 'zxcvbn', um_pass_strength_url . 'assets/js/zxcvbn.min.js', array( 'um-pass-strength' ), '4.2.0', true );
     wp_enqueue_style( 'um-pass-strength', um_pass_strength_url . 'assets/css/um-pass-strength.css', array(), um_pass_strength_version );

     wp_localize_script( 'um-pass-strength', 'um_pass_strength', array(
        'show_score' => apply_filters( 'um_pass_strength_show_score', true ),
        'show_warning' => apply_filters( 'um_pass_strength_show_warning', true ),
        'show_suggestions' => apply_filters( 'um_pass_strength_show_suggestions', true ),
    ) );
}
add_action( 'wp_enqueue_scripts', 'um_pass_strength_enqueue' );

