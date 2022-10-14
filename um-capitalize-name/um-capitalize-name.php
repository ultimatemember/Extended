<?php
/*
Plugin Name: Ultimate Member - Force Capitalization of Display Name( First and Last Names )
Plugin URI: http://ultimatemember.com/
Description: This forces the Display Name to be capitalized with special prepositions globally
Version: 1.0.0
Author: Ultimate Member Ltd.
Author URI: https://ultimatemember.com
*/

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'UM_CAPITALIZE_NAMES_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Include library
 */
require_once UM_CAPITALIZE_NAMES_PATH . '/includes/libs/capitlize-names.php';

/**
 * Apply capitlization to the Display Name globally
 */
function um_extended_capitalize_display_name( $name ) {

    if( ! extension_loaded( 'mbstring' ) ) return $name;

    return um_extended_capitalize_names( $name );

}

/**
 * Init
 */
function um_extended_capitlize_display_name_init() {

    /**
     * Force capitalization
     *
     * By default, this is set to TRUE. This may affect performance of the server.
     * If set to FALSE, display names will capitlized on form submissions instead.
     */
    $is_forced = apply_filters( 'um_extended_capitalize_name_forced', true );
    if( $is_forced ){
        add_filter( 'um_user_display_name_filter', 'um_extended_capitalize_display_name', 10, 1 );
    }else{
        add_filter( 'pre_user_display_name', 'um_extended_capitalize_display_name', 10, 1 );
        add_filter( 'pre_user_first_name', 'um_extended_capitalize_display_name', 10, 1 );
        add_filter( 'pre_user_last_name', 'um_extended_capitalize_display_name', 10, 1 );
    }

}

add_action( 'init', 'um_extended_capitlize_display_name_init' );

/**
 * Adds Display Name in the column
 */ 
function um_extended_capitalize_column_content( $value, $column_name, $user_id ) {
   
	if ( 'um_display_name' == $column_name ){
        um_fetch_user( $user_id );
        return um_user("display_name");
    }
    return $value;
}
add_filter( 'manage_users_custom_column', 'um_extended_capitalize_column_content', 10, 3 );

/**
 * Adds Custom Column To Users List Table
 */
function um_extended_add_display_name_column( $columns ) {
    $columns['um_display_name'] = 'UM Display Name';
    return $columns;
}
add_filter( 'manage_users_columns', 'um_extended_add_display_name_column', 1 );


