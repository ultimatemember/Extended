<?php
/*
Plugin Name: Ultimate Member - Enable Profile Photo in Register form
Plugin URI: http://ultimatemember.com/
Description: Enable users to upload their profile photo in Register form
Version: 1.0.0
Author: Ultimate Member Ltd.
Author URI: https://ultimatemember.com
*/

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add new predefined field "Profile Photo" in UM Form Builder.
 *
 * @param array $arr field array settings.
 */
function um_predefined_fields_hook_profile_photo( $arr ) {

	$arr['register_profile_photo'] = array(
		'title' => __( 'Profile Photo', 'ultimate-member' ),
		'metakey' => 'register_profile_photo',
		'type' => 'image',
		'label' => __('Change your profile photo','ultimate-member'),
		'upload_text' => __('Upload your photo here','ultimate-member'),
		'icon' => 'um-faicon-camera',
		'crop' => 1,
		'max_size' => ( UM()->options()->get('profile_photo_max_size') ) ? UM()->options()->get('profile_photo_max_size') : 999999999,
		'min_width' => str_replace('px','',UM()->options()->get('profile_photosize')),
		'min_height' => str_replace('px','',UM()->options()->get('profile_photosize')),
	);

	return $arr;

}
add_filter( 'um_predefined_fields_hook', 'um_predefined_fields_hook_profile_photo', 99999, 1 );

/**
 *  Multiply Profile Photo with different sizes
 *
 * @param integer $user_id the user ID.
 */
function um_registration_set_profile_photo( $user_id ) {

	$user_basedir = UM()->uploader()->get_upload_user_base_dir( $user_id, true );

	$temp_dir = UM()->uploader()->get_core_temp_dir() . DIRECTORY_SEPARATOR;

	$temp_profile_photo = array_slice( scandir( $temp_dir ), 2);
	
	$temp_profile_id =  isset( $_COOKIE['um-register-profile-photo'] ) ? $_COOKIE['um-register-profile-photo'] : null;

	if( empty( $temp_profile_photo ) ) return;

	foreach( $temp_profile_photo as $i => $p ){
		if ( strpos($p, "_photo_{$temp_profile_id}_temp") !== false ) {
			$profile_p = $p;
		}
	}

	if( empty( $profile_p ) ) return;

	$temp_image_path = $temp_dir . DIRECTORY_SEPARATOR . $profile_p;
	$new_image_path = $user_basedir . DIRECTORY_SEPARATOR . $profile_p;
	
    $image = wp_get_image_editor( $temp_image_path );

	$file_info = wp_check_filetype_and_ext( $image_path, $profile_p );
 
	$ext = $file_info['ext'];
	
	$new_image_name = str_replace( $profile_p,  "profile_photo.{$ext}", $new_image_path );
	
	$sizes = UM()->options()->get( 'photo_thumb_sizes' );

    $quality = UM()->options()->get( 'image_compression' );
	
	if ( ! is_wp_error( $image ) ) {
			
		$image->save( $new_image_name );

		$image->set_quality( $quality );

		$sizes_array = array();

		foreach( $sizes as $size ) {
			$sizes_array[ ] = array ( 'width' => $size );
		}

		$image->multi_resize( $sizes_array );

		delete_user_meta( $user_id, 'synced_profile_photo' );
		update_user_meta( $user_id, 'profile_photo', "profile_photo.{$ext}" ); 
		@unlink( $temp_image_path );

	} 

}
add_action( 'um_registration_set_extra_data', 'um_registration_set_profile_photo', 1, 1 );

/**
 * Set Temporary user id
 */
function um_register_profile_photo_set_temp_user_id(){
	$temp_profile_id = isset( $_COOKIE['um-register-profile-photo'] ) ? $_COOKIE['um-register-profile-photo'] : null;
	if ( ! $temp_profile_id ) {
		setcookie( 'um-register-profile-photo', md5( time() ), time() + 3600, COOKIEPATH, COOKIE_DOMAIN );
	}

}
add_action( 'template_redirect', 'um_register_profile_photo_set_temp_user_id' );

/**
 * Set handler callback for filename
 */
function um_register_profile_photo_upload_handler( $override_handler ) {

	if ( 'stream_photo' == UM()->uploader()->upload_image_type ) {

		$override_handler['unique_filename_callback'] = "um_register_profile_photo_name";
	}

	return $override_handler;
}
add_filter( 'um_image_upload_handler_overrides__register_profile_photo', 'um_register_profile_photo_upload_handler', 99999 );

/**
 * Change filename
 */
function um_register_profile_photo_name( $dir, $filename, $ext ) {
	$temp_profile_id =  isset( $_COOKIE['um-register-profile-photo'] ) ? $_COOKIE['um-register-profile-photo'] : null;
	
	return "profile_photo_{$temp_profile_id}_temp{$ext}";

}
