<?php
/**
 * Core class
 *
 * @package UM_Extended_Profile_Photo\Core
 */

namespace UM_Extended_Profile_Photo;

/**
 * Class Core to handle all plugin initialization.
 *
 * @since 1.0.0
 */
class Core {

	/**
	 * Init
	 */
	public function __construct() {
		add_filter( 'um_predefined_fields_hook', array( $this, 'predefined_fields' ), 99999, 1 );
		add_filter( 'um_core_fields_hook', array( $this, 'modify_field_option' ) );
		add_filter( 'um_image_upload_handler_overrides__register_profile_photo', array( $this, 'upload_handler' ), 99999 );

		add_action( 'um_admin_field_edit_hook_min_width', array( $this, 'field_settings_type_min_width' ), 10, 1 );
		add_action( 'um_admin_field_edit_hook_min_height', array( $this, 'field_settings_type_min_height' ), 10, 1 );
		add_action( 'um_after_user_account_updated', array( $this, 'set_profile_photo' ), 1, 2 );
		add_action( 'um_registration_set_extra_data', array( $this, 'set_profile_photo' ), 1, 2 );
		add_action( 'template_redirect', array( $this, 'set_temp_user_id' ) );
		add_action( 'wp_footer', array( $this, 'profile_photo_script' ) );
		add_action( 'wp_ajax_um_remove_file', array( $this, 'delete_profile_photo_from_account' ), 1 );
	}

	/**
	 * Remove Profile Photo field from the default uploader for Profile forms
	 *
	 * @param array $files Files.
	 *
	 * @return array $files
	 */
	public function um_user_pre_updating_files_array( $files ) {

		if ( um_is_core_page( 'account' ) ) {
			unset( $files['register_profile_photo'] );
		}

		return $files;
	}

	/**
	 * Add new predefined field "Profile Photo" in UM Form Builder.
	 *
	 * @param array $arr field array settings.
	 */
	public function predefined_fields( $arr ) {

		$arr['register_profile_photo'] = array(
			'title'       => __( 'Profile Photo', 'um-extended' ),
			'metakey'     => 'register_profile_photo',
			'type'        => 'image',
			'label'       => __( 'Change your Profile Photo', 'um-extended' ),
			'upload_text' => __( 'Upload profile photo here', 'um-extended' ),
			'icon'        => 'um-faicon-camera',
			'crop'        => 2,
			'editable'    => 1,
			'max_size'    => UM()->options()->get( 'profile_photo_max_size' ) ? UM()->options()->get( 'profile_photo_max_size' ) : 999999999,
			'min_width'   => str_replace( 'px', '', UM()->options()->get( 'profile_photosize' ) ),
			'min_height'  => str_replace( 'px', '', UM()->options()->get( 'profile_photosize' ) ),
		);
		return $arr;
	}

	/**
	 *  Min Width field option
	 *
	 * @param string $edit_mode_value Field value.
	 */
	public function field_settings_type_min_width( $edit_mode_value ) {
		if ( isset( $_REQUEST['arg3'] ) && 'register_profile_photo' === $_REQUEST['arg3'] ) {  // phpcs:ignore WordPress.Security.NonceVerification
			?>

		<p><label for="_min_width"><?php esc_attr_e( 'Mininum Image Width', 'um-extended' ); ?> <?php UM()->tooltip( __( 'Set Minimum Profile Photo Width for this section', 'um-extended' ) ); ?></label>
			<input type="text" name="_min_width" id="_min_width" value="<?php echo esc_attr( $edit_mode_value ? $edit_mode_value : UM()->options()->get( 'profile_photosize' ) ); ?>" />
		</p>
			<?php
		}
	}

	/**
	 *  Min Height field option
	 *
	 * @param string $edit_mode_value Field value.
	 */
	public function field_settings_type_min_height( $edit_mode_value ) {
		if ( isset( $_REQUEST['arg3'] ) && 'register_profile_photo' === $_REQUEST['arg3'] ) {  // phpcs:ignore WordPress.Security.NonceVerification
			?>

		<p><label for="_min_height"><?php esc_attr_e( 'Mininum Image Height', 'um-extended' ); ?> <?php UM()->tooltip( __( 'Set Minimum Profile Photo Height for this section', 'um-extended' ) ); ?></label>
			<input type="text" name="_min_height" id="_min_height" value="<?php echo esc_attr( $edit_mode_value ? $edit_mode_value : UM()->options()->get( 'profile_photosize' ) ); ?>" />
		</p>
			<?php
		}
	}

	/**
	 * Add columns in field settings
	 *
	 * @param array $fields Field settings.
	 *
	 * @return array $fields
	 */
	public function modify_field_option( $fields ) {

		// phpcs:disable WordPress.Security.NonceVerification
		if ( isset( $_REQUEST['form_mode'] ) && 'register' !== $_REQUEST['form_mode'] ) {
			return $fields;
		}

		if ( isset( $_REQUEST['act_id'] ) && 'um_admin_show_fields' === $_REQUEST['act_id'] ) {
			return $fields;

		}

		if ( ! isset( $_REQUEST['arg3'] ) ) {
			return $fields;
		}

		if ( 'register_profile_photo' === $_REQUEST['arg3'] ) {
			$fields['image']['col2'][] = '_min_height';
			$fields['image']['col2'][] = '_min_width';
		}
		// phpcs:enable WordPress.Security.NonceVerification

		return $fields;
	}

	/**
	 *  Multiply Profile Photo with different sizes
	 *
	 * @param integer $user_id the user ID.
	 * @param array   $args Field settings.
	 */
	public function set_profile_photo( $user_id, $args ) {

		if ( isset( $args['form_id'] ) ) {
			$req = 'register_profile_photo-' . $args['form_id'];
		} elseif ( null !== UM()->form()->form_id ) {
			$req = 'register_profile_photo-' . UM()->form()->form_id;
		} else {
			$req = 'register_profile_photo';
		}

		if ( ! isset( $_REQUEST[ $req ] ) || empty( $_REQUEST[ $req ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}

		if ( is_user_logged_in() && strpos( sanitize_key( $_REQUEST[ $req ] ), '_temp' ) > -1 ) { // phpcs:ignore WordPress.Security.NonceVerification
			UM()->files()->delete_core_user_photo( $user_id, 'profile_photo' );
			delete_user_meta( $user_id, 'profile_photo' );
		}

		$user_basedir = UM()->uploader()->get_upload_user_base_dir( $user_id, true );

		$temp_dir = UM()->uploader()->get_core_temp_dir() . DIRECTORY_SEPARATOR;

		$temp_profile_photo = array_slice( scandir( $temp_dir ), 2 );

		$temp_profile_id = isset( $_COOKIE['um-register-profile-photo'] ) ? sanitize_key( $_COOKIE['um-register-profile-photo'] ) : null;

		foreach ( $temp_profile_photo as $i => $p ) {
			if ( strpos( $p, "profile_photo_{$temp_profile_id}_temp" ) !== false ) {
				$profile_p = $p;
			}
		}

		if ( empty( $profile_p ) ) {
			return;
		}
		$temp_image_path = $temp_dir . DIRECTORY_SEPARATOR . $profile_p;
		$new_image_path  = $user_basedir . DIRECTORY_SEPARATOR . $profile_p;

			$image = wp_get_image_editor( $temp_image_path );

		$file_info = wp_check_filetype_and_ext( $temp_image_path, $profile_p );

		$ext = $file_info['ext'];

		$new_image_name = str_replace( $profile_p, "profile_photo.{$ext}", $new_image_path );

		$sizes = UM()->options()->get( 'profile_thumb_sizes' );

		$quality = UM()->options()->get( 'image_compression' );

		if ( ! is_wp_error( $image ) ) {

			$image->save( $new_image_name );

			$image->set_quality( $quality );

			$sizes_array = array();

			foreach ( $sizes as $size ) {
				$sizes_array[] = array( 'width' => $size );
			}

			$image->multi_resize( $sizes_array );

			delete_user_meta( $user_id, 'synced_profile_photo' );
			update_user_meta( $user_id, 'profile_photo', "profile_photo.{$ext}" );
			update_user_meta( $user_id, 'register_profile_photo', "profile_photo.{$ext}" );
			wp_delete_file( $temp_image_path );

		}
	}

	/**
	 * Set Temporary user id
	 */
	public function set_temp_user_id() {

		$temp_profile_id = isset( $_COOKIE['um-register-profile-photo'] ) ? sanitize_key( $_COOKIE['um-register-profile-photo'] ) : null;
		if ( ! $temp_profile_id ) {
			setcookie( 'um-register-profile-photo', md5( time() ), time() + 3600, COOKIEPATH, COOKIE_DOMAIN );
		}
	}

	/**
	 * Set handler callback for filename
	 *
	 * @param array $override_handler WP Media uploader handler.
	 */
	public function upload_handler( $override_handler ) {

		if ( 'stream_photo' === UM()->uploader()->upload_image_type && 'register_profile_photo' === UM()->uploader()->field_key ) {
			if ( defined( 'UM_IS_EXTENDED' ) ) {
				$override_handler['unique_filename_callback'] = array( um_extended_plugin()->profile_photo(), 'photo_name' );
			} else {
				$override_handler['unique_filename_callback'] = array( um_extended_profilephoto_plugin(), 'photo_name' );
			}
		}

		return $override_handler;
	}

	/**
	 * Change filename
	 *
	 * @param string $dir Directory name.
	 * @param string $filename Uploading file name.
	 * @param string $ext Extension of the uploading file.
	 */
	public function photo_name( $dir, $filename, $ext ) {

		$temp_profile_id = isset( $_COOKIE['um-register-profile-photo'] ) ? sanitize_key( $_COOKIE['um-register-profile-photo'] ) : null;

		if ( empty( $ext ) ) {
				$image_type = wp_check_filetype( $filename );
				$ext        = strtolower( trim( $image_type['ext'], ' \/.' ) );
		} else {
				$ext = strtolower( trim( $ext, ' \/.' ) );
		}

		$filename = "profile_photo_{$temp_profile_id}_temp.{$ext}";

		UM()->uploader()->delete_existing_file( $filename, $ext, $dir );

		return $filename;
	}

	/**
	 * Clear Profile Photo cache
	 */
	public function profile_photo_script() {

		if ( ! um_is_core_page( 'account' ) ) {
			return;
		}
		?>
		<script type="text/javascript">
			jQuery(document).on("ready", function(){
			setTimeout(() => {
				var register_profile_photo = jQuery("div[data-key='register_profile_photo']");
				register_profile_photo.find(".um-field-area").find(".um-single-image-preview").find("img").attr("src", register_profile_photo.data("profile_photo"));
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

	/**
	 * Delete Profile Photo via the account form
	 */
	public function delete_profile_photo_from_account() {

		if ( isset( $_REQUEST['mode'] ) && in_array( $_REQUEST['mode'], array( 'account', 'profile' ), true ) ) {  // phpcs:ignore WordPress.Security.NonceVerification
			$fname = '';
			if ( isset( $_REQUEST['filename'] ) ) {  // phpcs:ignore WordPress.Security.NonceVerification
				$fname = pathinfo( sanitize_key( $_REQUEST['filename'] ), PATHINFO_FILENAME );  // phpcs:ignore WordPress.Security.NonceVerification
			}
			if ( 'profile_photo' === $fname ) {
				UM()->files()->delete_core_user_photo( get_current_user_id(), $fname );
				delete_user_meta( get_current_user_id(), 'profile_photo' );
				UM()->files()->delete_core_user_photo( get_current_user_id(), 'register_profile_photo' );
				wp_send_json_success( $fname );
			}
		}
	}
}
