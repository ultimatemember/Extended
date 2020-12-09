<?php
/*
Plugin Name: Ultimate Member - Browser Detect
Plugin URI: https://ultimatemember.com/extensions/browser-detect
Description: Detects User's IP Address, Operating Systems and Browsers
Version: 1.0
Author: Ultimate Member
Author URI: http://ultimatemember.com/
Text Domain: um-browser
Domain Path: /languages
UM version: 2.1.0
*/

if( ! class_exists('Browser') ){
    include_once "includes/libs/Browser.php";
}

if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Add ability to add User's IP Address and Web Browser
 */

add_filter("um_predefined_fields_hook", function( $predefined_fields ){

    $predefined_fields['user_ip_address']  = array(
        'title'         => __( 'User IP Address', 'ultimate-member' ),
        'metakey'       => 'user_ip_address',
        'type'          => 'user_ip_address',
        'label'         => __( 'User IP Address', 'ultimate-member' ),
        'required'      => 1,
        'public'        => 1,
        'editable'      => 1,
    );

    $predefined_fields['user_web_browser']  = array(
        'title'         => __( 'User Web Browser', 'ultimate-member' ),
        'metakey'       => 'user_web_browser',
        'type'          => 'user_web_browser',
        'label'         => __( 'User Web Browser', 'ultimate-member' ),
        'required'      => 1,
        'public'        => 1,
        'editable'      => 1,
    );

    $predefined_fields['user_operating_system']  = array(
        'title'         => __( 'User Operating System', 'ultimate-member' ),
        'metakey'       => 'user_operating_system',
        'type'          => 'user_operating_system',
        'label'         => __( 'User Operating System', 'ultimate-member' ),
        'required'      => 1,
        'public'        => 1,
        'editable'      => 1,
    );

    return $predefined_fields;

});
/*
add_filter("um_core_fields_hook",function( $data ){

    $data['user_ip_address'] = array(
        'name' => 'User IP Address',
        'col1' => array('_title'),
        'col2' => array('_metakey'),
        'conditional_support' => 0,
		'validate' => array(
            '_metakey' => array(
                'mode' => 'unique',
            ),
        )
    );

    $data['user_web_browser'] = array(
        'name' => 'User Web Browser',
        'col1' => array('_title'),
        'col2' => array('_metakey'),
        'conditional_support' => 0,
		'validate' => array(
            '_metakey' => array(
                'mode' => 'unique',
            ),
        )
    );

    $data['user_operating_system'] = array(
        'name' => 'User Operating System',
        'col1' => array('_title'),
        'col2' => array('_metakey'),
        'conditional_support' => 0,
		'validate' => array(
            '_metakey' => array(
                'mode' => 'unique',
            ),
        )
    );

    return $data;

}, 99999, 1);
*/

/**
 * Add hidden fields to front-end
 */

// IP Address
add_filter("um_edit_field_register_user_ip_address", function( $output, $data ){
    return "<input type='hidden' name='user_ip_address' value='".esc_attr( $_SERVER['REMOTE_ADDR']  )."'/>";
}, 10, 2);

// Web Browser
add_filter("um_edit_field_register_user_web_browser", function( $output, $data ){

    $browser = new Browser();

    return "<input type='hidden' name='user_web_browser' value='".esc_attr( $browser->getUserAgent() )."'/>";
}, 10, 2);

// Operating System
add_filter("um_edit_field_register_user_operating_system", function( $output, $data ){


    $browser = new Browser();

    return "<input type='hidden' name='user_operating_system'  value='".esc_attr( $browser->getPlatform() )."'/>";
}, 10, 2);
