<?php
/*
Plugin Name: Ultimate Member - Fields Character and Words Counter
Plugin URI: https://www.ultimatemember.com
Description: Adds a counter for character and words length in textbox and textarea fields
Version: 1.0.1
Author: Ultimate Member Ltd.
Author URI: https://www.ultimatemember.com
Text Domain: um-fields-counter
*/

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

define( 'UM_FIELDS_COUNTER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'UM_FIELDS_COUNTER_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Load assets
 */
function um_fields_counter_assets() {
	wp_register_script( 'um-fields-counter', UM_FIELDS_COUNTER_PLUGIN_URL . 'assets/js/um-fields-counter.js', array( 'jquery' ), '1.0.0', true );
	wp_enqueue_script( 'um-fields-counter' );
}
add_action( 'init', 'um_fields_counter_assets' );

/**
 * Add field attributes
 *
 * @param array  $field_atts Field attributes.
 * @param string $key Field key / meta key.
 * @param array  $data Field data.
 */
function um_fields_counter_field_length_limit( $field_atts, $key, $data ) {

	if ( isset( $data['max_chars'] ) ) {
        $field_atts['data-max_chars'] = array( $data['max_chars'] );
	}

	if ( isset( $data['max_words'] ) ) {
        $field_atts['data-max_words'] = array( $data['max_words'] );
	}

	return $field_atts;
}
add_filter( 'um_field_extra_atts', 'um_fields_counter_field_length_limit', 10, 3 );
