<?php
/**
 * Core class
 *
 * @package UM_Extended_Regenerate_Thumbnails\Core
 */

namespace UM_Extended_Regenerate_Thumbnails;

use WP_User_Query;

/**
 * Class Core to handle all plugin initialization.
 *
 * @since 1.0.0
 */
class Core {

	/**
	 * Processed Users
	 *
	 * @var $proccessed_users
	 */
	public $processed_users = '';

	/**
	 * Init
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_submenu' ), 99 );
		add_action( 'admin_init', array( $this, 'process_resize' ) );
	}

	/**
	 * Add Submenu
	 */
	public function add_submenu() {
		add_submenu_page( 'ultimatemember', 'Regenerate Thumbnails', 'Regenerate Thumbnails', 'manage_options', 'um_regenerate_thumbnails', array( $this, 'menu_content' ) );
	}

	/**
	 * Form
	 */
	public function menu_content() {
		?>

		<div class="wrap">
			<h2>Ultimate Member <sup style="font-size:15px"><?php echo esc_attr( ultimatemember_version ); ?></sup> - Regenerate Profile Photo Thumbnails</h2>

			<br/>
			<form method="post">
				<label>Width: <input type="text" name="width" value="120" style="width:60px"/></label>
				<label>Height: <input type="text" name="height" value="120"  style="width:60px"/></label>
				<label>Image Quality: <input type="text" name="quality" value="100" style="width:60px"/></label>
				<div style="padding: 10px 0px 10px">
					<input type="submit" value="Regenerate Profile Photo Thumbnails" name="um_regenerate_pp_thumbnails" class="button primary" />
					<input type="submit" value="Regenerate Cover Photo Thumbnails" name="um_regenerate_cp_thumbnails" class="button primary" />
				</div>
			</form>
			<br/>
			<br/>

			<?php
			if ( ! empty( $this->processed_users ) ) {
				echo wp_kses( $this->processed_users, UM()->get_allowed_html( 'template' ) );
			}
			?>
		</div>

		<?php
	}

	/**
	 * Regenerate Thumbnails
	 */
	public function process_resize() {

		// Profile Photo.
		if ( isset( $_REQUEST['um_regenerate_pp_thumbnails'] ) ) {  // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( empty( $_REQUEST['width'] ) || empty( $_REQUEST['height'] ) || empty( $_REQUEST['quality'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$this->processed_users = __( 'Invalid Thumbnail Requirements', 'um-extended' );
			} else {

				// phpcs:disable WordPress.Security.NonceVerification.Recommended
				$width   = sanitize_key( $_REQUEST['width'] );
				$height  = sanitize_key( $_REQUEST['height'] );
				$quality = sanitize_key( $_REQUEST['quality'] );
				// phpcs:enable WordPress.Security.NonceVerification.Recommended

				$args = array(
					'number'   => -1,
					'meta_key' => 'profile_photo', //phpcs:ignore db slow query.
				);

				// The Query.
				$user_query = new WP_User_Query( $args );

				// User Loop.
				if ( ! empty( $user_query->get_results() ) ) {

					foreach ( $user_query->get_results() as $user ) {
						um_fetch_user( $user->ID );
						$original_file = UM()->common()->filesystem()->get_user_uploads_dir( $user->ID ) . DIRECTORY_SEPARATOR . um_profile( 'profile_photo' );

						if ( 0 === $height ) {
							$append_size = $width;
						} else {
							$append_size = "{$width}x{$height}";
						}

						$new_file_name_swap = str_replace( 'profile_photo', 'profile_photo-' . $append_size, um_profile( 'profile_photo' ) );
						$new_file           = UM()->common()->filesystem()->get_user_uploads_dir( $user->ID ) . DIRECTORY_SEPARATOR . $new_file_name_swap;

						$this->processed_users .= '<strong>' . um_user( 'display_name' ) . '</strong><span style="color:green" class="dashicons dashicons-yes"></span></br>&nbsp;&nbsp; - Original: ' . $original_file . '<br/>&nbsp;&nbsp; - New: ' . $new_file;

						if ( ! file_exists( $original_file ) || ! @copy( $original_file, $new_file ) ) { //phpcs:ignore
							$this->processed_users .= "&nbsp;<strong style='color:red;'>Failed to copy</strong>";
						}

						$this->processed_users .= '<br/>';

						$image = wp_get_image_editor( $original_file );
						if ( ! is_wp_error( $image ) ) {

							$size = $image->get_size();

							$uploaded_height = $size['height'];
							$uploaded_width  = $size['width'];

							$image->set_quality( $quality );
							if ( 0 === $height ) {
								$image->resize( $width, $uploaded_height, true );
							} elseif ( 0 === $width ) {
								$image->resize( $uploaded_width, $height, true );
							} else {
								$image->resize( $width, $height, true );
							}
							$image->save( $new_file );
						}
					}
				} else {
					$this->processed_users = 'No users found with Profile Photos.';
				}
			}
		}

		// Cover Photo.
		if ( isset( $_REQUEST['um_regenerate_cp_thumbnails'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( empty( $_REQUEST['width'] ) || empty( $_REQUEST['height'] ) || empty( $_REQUEST['quality'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$this->processed_users = __( 'Invalid Thumbnail Requirements', 'um-extended' );
			} else {

				// phpcs:disable WordPress.Security.NonceVerification.Recommended
				$width   = sanitize_key( $_REQUEST['width'] );
				$height  = sanitize_key( $_REQUEST['height'] );
				$quality = sanitize_key( $_REQUEST['quality'] );
				// phpcs:enable WordPress.Security.NonceVerification.Recommended

				$args = array(
					'number'   => -1,
					'meta_key' => 'cover_photo', //phpcs:ignore
				);

				// The Query.
				$user_query = new WP_User_Query( $args );

				// User Loop.
				if ( ! empty( $user_query->get_results() ) ) {

					foreach ( $user_query->get_results() as $user ) {
						um_fetch_user( $user->ID );

						$original_file = UM()->common()->filesystem()->get_user_uploads_dir( $user->ID ) . DIRECTORY_SEPARATOR . um_profile( 'cover_photo' );

						if ( 0 === $height ) {
							$append_size = $width;
						} else {
							$append_size = "{$width}x{$height}";
						}

						$new_file_name_swap = str_replace( 'cover_photo', 'cover_photo-' . $append_size, um_profile( 'cover_photo' ) );

						$new_file = UM()->common()->filesystem()->get_user_uploads_dir( $user->ID ) . DIRECTORY_SEPARATOR . $new_file_name_swap;

						$this->processed_users .= '<strong>' . um_user( 'display_name' ) . '</strong><span style="color:green" class="dashicons dashicons-yes"></span></br>&nbsp;&nbsp; - Original: ' . $original_file . '<br/>&nbsp;&nbsp; - New: ' . $new_file;

						if ( ! file_exists( $original_file ) || ! @copy( $original_file, $new_file ) ) { //phpcs:ignore
							$this->processed_users .= "&nbsp;<strong style='color:red;'>Failed to copy</strong>";
						}

						$this->processed_users .= '<br/>';

						$image = wp_get_image_editor( $original_file );
						if ( ! is_wp_error( $image ) ) {
							$size = $image->get_size();

							$uploaded_height = $size['height'];
							$uploaded_width  = $size['width'];

							$image->set_quality( $quality );
							if ( 0 === $height ) {
								$image->resize( $width, $uploaded_height, true );
							} elseif ( 0 === $width ) {
								$image->resize( $uploaded_width, $height, true );
							} else {
								$image->resize( $width, $height, true );
							}
							$image->save( $new_file );
						}
					}
				} else {
					$this->processed_users = __( 'No users found with Cover Photos.', 'um-extended' );
				}
			}
		}
	}
}
