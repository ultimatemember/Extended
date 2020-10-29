<?php
/*
Plugin Name: Ultimate Member - Enable Profile Photo in Register form
Plugin URI: http://ultimatemember.com/
Description: Enable users to upload their profile photo in Register form
Version: 1.0.0
Author: UM Devs
Author URI: https://ultimatemember.com
*/

if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Add new predefined field "Profile Photo" in UM Form Builder.
*/
add_filter("um_predefined_fields_hook","um_predefined_fields_hook_profile_photo", 99999, 1 );
function um_predefined_fields_hook_profile_photo( $arr ){


	$arr['profile_photo'] = array(
		'title' => __('Profile Photo','ultimate-member'),
		'metakey' => 'profile_photo',
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

/**
 *  Multiply Profile Photo with different sizes
*/
add_action( 'um_registration_set_extra_data', 'um_registration_set_profile_photo', 999999, 1 );
function um_registration_set_profile_photo( $user_id ){

	$user_basedir = UM()->uploader()->get_upload_user_base_dir( $user_id, true );

    $profile_photo = array_slice(scandir($user_basedir), 2);
    
    if( empty( $profile_photo ) ) return;

    $image_path = $user_basedir . DIRECTORY_SEPARATOR . $profile_photo[0];
    
    $image = wp_get_image_editor( $image_path );

	$file_info = wp_check_filetype_and_ext( $image_path, $profile_photo[0] );
 
	$ext = $file_info['ext'];
	
    $new_image_name = str_replace( $profile_photo[0],  "profile_photo.".$ext, $image_path );

	$sizes = UM()->options()->get( 'photo_thumb_sizes' );

    $quality = UM()->options()->get( 'image_compression' );
    
	if ( ! is_wp_error( $image ) ) {
			
		$image->save( $new_image_name );

		$image->set_quality( $quality );

		$sizes_array = array();

		foreach( $sizes as $size ){
			$sizes_array[ ] = array ('width' => $size );
		}

		$image->multi_resize( $sizes_array );

		delete_user_meta( $user_id, 'synced_profile_photo' );
		update_user_meta( $user_id, 'profile_photo', "profile_photo.{$ext}" ); 
		@unlink( $image_path );

	} 

}

