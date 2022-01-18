<?php
/*
Plugin Name: Ultimate Member - User Meta Shortcodes
Plugin URI: http://ultimatemember.com/
Description: Adds a shortcode functionality to render User Meta data in Pages/Posts
Version: 1.0.0
Author: UM Devs
Author URI: https://ultimatemember.com
*/

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Returns a user meta value
 * Usage [um_user user_id="" meta_key="" ] // leave user_id empty if you want to retrive the current user's meta value.
 * meta_key is the field name that you've set in the UM form builder
 * You can modify the return meta_value with filter hook 'um_user_shortcode_filter__{$meta_key}'
 */
function um_user_shortcode( $atts ) {
	$atts = extract( shortcode_atts( array(
		'user_id' => um_profile_id(),
		'meta_key' => '',
	), $atts ) );
	
	if ( empty( $meta_key ) ) return;
	
	if( empty( $user_id ) ) $user_id = um_profile_id(); 
    
    $meta_value = get_user_meta( $user_id, $meta_key, true );
    if( is_serialized( $meta_value ) ){
       $meta_value = unserialize( $meta_value );
    } 
    if( is_array( $meta_value ) ){
         $meta_value = implode(",",$meta_value );
    }  
    return apply_filters("um_user_shortcode_filter__{$meta_key}", $meta_value );
 
}
add_shortcode( 'um_user', 'um_user_shortcode' );