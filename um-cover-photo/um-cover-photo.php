<?php
/*
Plugin Name: Ultimate Member - Enable Cover Photo in Register form
Plugin URI: http://ultimatemember.com/
Description: Enable users to upload their cover photo in Register form
Version: 1.0.3
Author: Ultimate Member Ltd.
Author URI: https://ultimatemember.com
*/

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Remove cover photo field from the default uploader for Profile forms
 */
add_filter( 'um_user_pre_updating_files_array', function( $files ) {
	
	if ( um_is_core_page( 'account' ) ) {
		unset( $files['register_cover_photo'] );
	}

	return $files;
}, 10, 1);

/**
 * Add new predefined field "Cover Photo" in UM Form Builder.
 *
 * @param array $arr field array settings.
 */
function um_predefined_fields_hook_cover_photo( $arr ) {

	$arr['register_cover_photo'] = array(
			'title' => __('Cover Photo','ultimate-member'),
			'metakey' => 'register_cover_photo',
			'type' => 'image',
			'label' => __('Change your cover photo','ultimate-member'),
			'upload_text' => __('Upload profile cover here','ultimate-member'),
			'icon' => 'um-faicon-picture-o',
			'crop' => 2,
            'editable' => 1,
			'max_size' => ( UM()->options()->get('cover_photo_max_size') ) ? UM()->options()->get('cover_photo_max_size') : 999999999,
			'modal_size' => 'large',
			'ratio' => str_replace(':1','',UM()->options()->get('profile_cover_ratio')),
			'min_width' => UM()->options()->get('cover_min_width')		
	);
	
	
	return $arr;
}
add_filter( 'um_predefined_fields_hook', 'um_predefined_fields_hook_cover_photo', 99999, 1 );


/**
 * Min Width field option
 */
function um_cover_photo_register_add_field_settings_type_min_width( $edit_mode_value, $form_id, $edit_array ) {
	if ( isset( $_REQUEST['arg3'] ) && 'register_cover_photo' === $_REQUEST['arg3'] ){
	?>

	<p><label for="_min_width"><?php _e( 'Mininum Image Width', 'ultimate-member' ) ?> <?php UM()->tooltip( __( 'Set Minimum Cover Photo Width for this section', 'ultimate-member' ) ); ?></label>
		<input type="text" name="_min_width" id="_min_width" value="<?php echo ( $edit_mode_value ) ? $edit_mode_value : UM()->options()->get('profile_photosize'); ?>" />
	</p>

	<?php
	}

}
add_action( 'um_admin_field_edit_hook_min_width', 'um_cover_photo_register_add_field_settings_type_min_width', 10, 3 );

/**
 * Min Height field option
 */
function um_cover_photo_register_add_field_settings_type_min_height( $edit_mode_value, $form_id, $edit_array ) {
	if ( isset( $_REQUEST['arg3'] ) && 'register_cover_photo' === $_REQUEST['arg3'] ){
	?>

	<p><label for="_min_height"> <?php _e( 'Mininum Image Height', 'ultimate-member' ) ?> <?php UM()->tooltip( __( 'Set Minimum Cover Photo Height for this section', 'ultimate-member' ) ); ?></label>
		<input type="text" name="_min_height" id="_min_height" value="<?php echo ( $edit_mode_value ) ? $edit_mode_value : UM()->options()->get('profile_photosize'); ?>" />
	</p>

	<?php
	}

}
add_action( 'um_admin_field_edit_hook_min_height', 'um_cover_photo_register_add_field_settings_type_min_height', 10, 3 );

/**
 * Add columns in field settings
 *
 * @param array $fields
 */
function um_cover_photo_modify_field_option( $fields ) {

	if ( isset( $_REQUEST['form_mode'] ) && 'register' !== $_REQUEST['form_mode'] ) {
		return $fields;
	}

	if ( isset( $_REQUEST['act_id'] ) && 'um_admin_show_fields' == $_REQUEST['act_id'] ) {
		return $fields;

	}
	
	if( ! isset( $_REQUEST['arg3'] ) ){
		return $fields;
	}

	if ( 'register_cover_photo' === $_REQUEST['arg3'] ){
		$fields['image']['col2'][ ] = '_min_height';
		$fields['image']['col2'][ ] = '_min_width';
	}
	
	return $fields;
}
add_filter( 'um_core_fields_hook', 'um_cover_photo_modify_field_option' );

/**
 *  Multiply Cover Photo with different sizes
 *
 * @param integer $user_id the user ID.
 */
function um_registration_set_cover_photo( $user_id, $args ) {
	
	if( isset( $args['form_id'] )  ) {
		$req = 'register_cover_photo-' . $args['form_id'];
	}elseif( null !== UM()->form()->form_id  ){
		$req = 'register_cover_photo-' . UM()->form()->form_id;
	}else{
		$req = 'register_cover_photo';
	}

	if ( ! isset( $_REQUEST[ $req ] ) || empty( $_REQUEST[ $req ] ) ) return;

	if( is_user_logged_in() && strpos( $_REQUEST[ $req ], '_temp' ) > -1 ) {
		UM()->files()->delete_core_user_photo( $user_id, 'cover_photo' );
		delete_user_meta( $user_id, 'cover_photo' );
	}

	$user_basedir = UM()->uploader()->get_upload_user_base_dir( $user_id, true );

	$temp_dir = UM()->uploader()->get_core_temp_dir() . DIRECTORY_SEPARATOR;

	$temp_profile_photo = array_slice( scandir( $temp_dir ), 2);
	
	$temp_profile_id =  isset( $_COOKIE['um-register-cover-photo'] ) ? $_COOKIE['um-register-cover-photo'] : null;

	foreach( $temp_profile_photo as $i => $p ){
		if ( strpos($p, "cover_photo_{$temp_profile_id}_temp") !== false ) {
			$profile_p = $p;
		}
	}
	
	if( empty( $profile_p ) ) return;

	$temp_image_path = $temp_dir . DIRECTORY_SEPARATOR . $profile_p;
	$new_image_path = $user_basedir . DIRECTORY_SEPARATOR . $profile_p;
	
        $image = wp_get_image_editor( $temp_image_path );

	$file_info = wp_check_filetype_and_ext( $temp_image_path, $profile_p );
 
	$ext = $file_info['ext'];
	
	$new_image_name = str_replace( $profile_p,  "cover_photo.{$ext}", $new_image_path );
	
	$sizes = UM()->options()->get( 'cover_thumb_sizes' );

	$quality = UM()->options()->get( 'image_compression' );
	
	
	if ( ! is_wp_error( $image ) ) {
			
		$image->save( $new_image_name );

		$image->set_quality( $quality );

		$sizes_array = array();

		foreach( $sizes as $size ) {
			$sizes_array[ ] = array ( 'width' => $size );
		}

		$image->multi_resize( $sizes_array );

		delete_user_meta( $user_id, 'synced_cover_photo' );
		update_user_meta( $user_id, 'cover_photo', "cover_photo.{$ext}" ); 
		update_user_meta( $user_id, 'register_cover_photo', "cover_photo.{$ext}" ); 
		@unlink( $temp_image_path );

	} 

}

add_action( 'um_after_user_account_updated', 'um_registration_set_cover_photo', 1, 2 );
add_action( 'um_registration_set_extra_data', 'um_registration_set_cover_photo', 1, 2 );


/**
 * Set Temporary user id
 */
function um_register_cover_photo_set_temp_user_id() {

	$temp_profile_id = isset( $_COOKIE['um-register-cover-photo'] ) ? $_COOKIE['um-register-cover-photo'] : null;
	if ( ! $temp_profile_id ) {
		setcookie( 'um-register-cover-photo', md5( time() ), time() + 3600, COOKIEPATH, COOKIE_DOMAIN );
	}

}
add_action( 'template_redirect', 'um_register_cover_photo_set_temp_user_id' );

/**
 * Set handler callback for filename
 */
function um_register_cover_photo_upload_handler( $override_handler ) {
	
	if ( 'stream_photo' == UM()->uploader()->upload_image_type && 'register_cover_photo' == UM()->uploader()->field_key ) {
		
		$override_handler['unique_filename_callback'] = 'um_register_cover_photo_name';
	}

	return $override_handler;
}
add_filter( 'um_image_upload_handler_overrides__register_cover_photo', 'um_register_cover_photo_upload_handler', 99999 );

/**
 * Change filename
 */
function um_register_cover_photo_name( $dir, $filename, $ext ) {
	
	$temp_profile_id =  isset( $_COOKIE['um-register-cover-photo'] ) ? $_COOKIE['um-register-cover-photo'] : null;

	if ( empty( $ext ) ) {
			$image_type = wp_check_filetype( $filename );
			$ext = strtolower( trim( $image_type['ext'], ' \/.' ) );
	} else {
			$ext = strtolower( trim( $ext, ' \/.' ) );
	}

	$filename = "cover_photo_{$temp_profile_id}_temp.{$ext}";

	UM()->uploader()->delete_existing_file( $filename, $ext, $dir );

	return $filename;
}

/**
 * Support cover photo uploader in Account form
 */
function um_register_display_cover_photo_in_account( $field_atts, $key, $data ) {

	if ( 'register_cover_photo' == $key && um_is_core_page( 'account' ) ) {

		$cover_photo = UM()->uploader()->get_upload_base_url() . um_user( 'ID' ) . DIRECTORY_SEPARATOR . um_profile( 'cover_photo' ) . '?ts=' . current_time( 'timestamp' );

		$field_atts['data-cover_photo'] = array( $cover_photo );
	}

	return $field_atts;
}
add_filter( 'um_field_extra_atts', 'um_register_display_cover_photo_in_account', 10, 3 );

/**
 * Clear cover photo cache
 */
function um_register_display_cover_photo_script() {

	if( ! um_is_core_page( 'account' ) ) return; 

	?>
	<script type="text/javascript">
	    jQuery(document).on("ready", function(){
		  setTimeout(() => {
			var register_profile_photo = jQuery("div[data-key='register_cover_photo']");
			
			register_profile_photo.find(".um-field-area").find(".um-single-image-preview").find("img").attr("src", register_profile_photo.data("cover_photo"));
			}, 1000);

			var account_small_avatar = jQuery(".um-account-meta-img-b").find("a").find("img");
			account_small_avatar.attr("src", account_small_avatar.attr("src") + "?ts=" + Math.floor(Date.now() / 1000) );
		
			jQuery(document).ajaxSuccess(function(event, xhr, settings) {
				if( typeof settings.data.indexOf !== "undefined" ){
					if (settings.data.indexOf("action=um_resize_image") > -1) {
						jQuery(".um-account .um-form form").submit();
					}
				}
			});
		});
	</script>
	<?php
}
add_action( 'wp_footer', 'um_register_display_cover_photo_script' );

/**
 * Delete cover photo viam the account form
 */
function um_register_delete_cover_photo_from_account() {

	if( isset( $_REQUEST['mode'] ) && in_array( $_REQUEST['mode'], array( 'account', 'profile' ) ) ) {
		
		$fname = pathinfo( $_REQUEST['filename'], PATHINFO_FILENAME );

		if( 'cover_photo' === $fname ){
			UM()->files()->delete_core_user_photo( get_current_user_id(), $fname );
			delete_user_meta( get_current_user_id(), 'cover_photo' );
			UM()->files()->delete_core_user_photo( get_current_user_id(), 'register_cover_photo' );
			wp_send_json_success( $fname  );
		}
		
	}
	
	

}
add_action( 'wp_ajax_um_remove_file', 'um_register_delete_cover_photo_from_account', 1 );
