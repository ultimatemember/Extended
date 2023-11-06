<?php
/**
 * Core class
 *
 * @package UM_Extended_Dummy_Accounts\Core
 */

namespace UM_Extended_Dummy_Accounts;

use WP_User_Query;

/**
 * Class Core to handle all plugin initialization.
 *
 * @since 1.0.0
 */
class Core {

	/**
	 * Content
	 *
	 * @var $content Content.
	 */
	public $content;

	/**
	 * Init
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'um_admin_addon_hook', array( $this, 'admin_addon_hook' ) );
	}

	/**
	 * Register Admin Menu
	 */
	public function admin_menu() {

		global $ultimatemember;
		add_submenu_page( 'ultimatemember', 'Generate Dummies', 'Generate Dummies', 'manage_options', 'generate_random_users', array( $this, 'content' ) );
	}

	/**
	 * Admin Init
	 */
	public function admin_init() {
		if ( isset( $_REQUEST['um-addon-hook'] ) ) { // phpcs:disable WordPress.Security.NonceVerification
			$hook = sanitize_key( $_REQUEST['um-addon-hook'] );
			do_action( 'um_admin_addon_hook', $hook );
		}
	}

	/**
	 *  Menu content
	 */
	public function content() {
		?>

		<div class="wrap">
			<h2>Ultimate Member <sup style="font-size:15px"><?php echo esc_attr( ultimatemember_version ); ?></sup></h2>
			<h3>Generate Dummies</h3>

			<?php
			if ( isset( $this->content ) ) {
				echo wp_kses( $this->content, array() );
			} else {
				?>
				<p>This tool allows you to add dummies as Ultimate Member users. </p>
				<?php
				$this->render_form();
			}
			?>

		</div>

		<?php
	}

	/**
	 * Disable Email handler
	 */
	public function disable_emails() {
		add_filter(
			'pre_wp_mail',
			function( $pre_wp_mail, $atts ) {
				return true;
			},
			20,
			2
		);
	}

	/**
	 * Admin Render Form
	 */
	public function render_form() {
		$dummies = new WP_User_Query(
			array(
				'fields'       => array( 'ID' ),
				'meta_key'     => '_um_profile_dummy', //phpcs:ignore db slow query okay.
				'meta_value'   => true,  //phpcs:ignore db slow query okay.
				'meta_compare' => '=',
			)
		);

		$nationality = array( 'AU', 'BR', 'CA', 'CH', 'DE', 'DK', 'ES', 'FI', 'FR', 'GB', 'IE', 'IR', 'NL', 'NZ', 'TR', 'US' );
		?>

		<form method="post">
			<input type="hidden" name="page" value="generate_random_users" />
			<table class="widefat striped">
				<thead>
					<tr>
						<th scope="row">
							<label><?php esc_attr_e( 'Actions', 'um-dummy-accounts' ); ?></label>
						</th>
						<td>
							<button type="submit" name="um-addon-hook" value="generate_random_users" class="button button-primary"><?php esc_attr_e( 'Start Generating Dummies', 'um-dummy-accounts' ); ?></button>

							<?php
							if ( $dummies->total_users > 0 ) :
								?>
								<button type="submit" name="um-addon-hook" value="remove_random_users" class="button button-secondary"><?php echo esc_attr( sprintf( /* translators: Remove Generated Dummies */esc_attr__( 'Remove Generated Dummies (%d)', 'um-dummy-accounts' ), $dummies->total_users ) ); ?></button>
							<?php endif; ?>

						</td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th scope="row">
							<label for="total_users"><?php esc_attr_e( 'How many dummies?', 'um-dummy-accounts' ); ?></label>
						</th>
						<td>
							<input type="number" name="total_users" value="10" min="1" max="999" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="nationality"><?php esc_attr_e( 'Available Nationalities', 'um-dummy-accounts' ); ?></label>
						</th>
						<td>
							<select name="nationality[]" multiple style="min-width: 75px;">
								<?php
								foreach ( $nationality as $code ) {
									echo '<option value=\'' . esc_attr( strtolower( $code ) ) . '\'/> ' . esc_attr( $code ) . '</option>';
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="gender"><?php esc_attr_e( 'Gender', 'um-dummy-accounts' ); ?></label>
						</th>
						<td>
							<label><input type='radio' name="gender" value="male" /> <?php esc_attr_e( 'Male', 'um-dummy-accounts' ); ?></label>&nbsp;&nbsp;
							<label><input type='radio' name="gender" value="female" /> <?php esc_attr_e( 'Female', 'um-dummy-accounts' ); ?></label>&nbsp;&nbsp;
							<label><input type='radio' name="gender" value="both" checked="checked" /> <?php esc_attr_e( 'Both', 'um-dummy-accounts' ); ?></label>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="add_cover_photo"><?php esc_attr_e( 'Cover photos', 'um-dummy-accounts' ); ?></label>
						</th>
						<td>
							<label><input type="checkbox" name="add_cover_photo" value="1" /> <?php esc_attr_e( 'Generates random colored cover photos', 'um-dummy-accounts' ); ?></label>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="disable_emails"><?php esc_attr_e( 'Notification', 'um-dummy-accounts' ); ?></label>
						</th>
						<td>
							<label><input type="checkbox" name="disable_emails" value="1" checked="checked" /> <?php esc_attr_e( 'Disable email notification', 'um-dummy-accounts' ); ?></label>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="email_pattern"><?php esc_attr_e( 'Account Email pattern', 'um-dummy-accounts' ); ?></label>
						</th>
						<td>
							<label><input type="email" name="email_pattern" placeholder="example@gmail.com" class="regular-text" /></label>
							<p><small><?php esc_attr_e( 'If you leave this blank, the site domain will be used as a pattern.', 'um-dummy-accounts' ); ?></small></p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="password"><?php esc_attr_e( 'Account Passwords', 'um-dummy-accounts' ); ?></label>
						</th>
						<td>
							<label><input type="password" name="password" class="regular-text" /></label>
							<p><small><?php esc_attr_e( 'If you leave this blank, it will generate random strings password', 'um-dummy-accounts' ); ?></small></p>
						</td>
					</tr>
				</tbody>
			</table>
		</form>

		<?php
	}

	/**
	 * Admin Form hook
	 *
	 * @param string $hook Form hook.
	 */
	public function admin_addon_hook( $hook ) {

		switch ( $hook ) {
			case 'generate_random_users':
				$json_url = 'https://randomuser.me/api/';

				$arr_post_header = array();
				$failed_dummies  = 0;

				if ( isset( $_REQUEST['gender'] ) ) {
					$gender = sanitize_key( $_REQUEST['gender'] );
					if ( 'both' !== $gender ) {
						$json_url                  = add_query_arg( 'gender', $gender, $json_url );
						$arr_post_header['gender'] = $gender;
					}
				}

				if ( isset( $_REQUEST['nationality'] ) ) {
					$nationality = implode( ',', array_map( 'sanitize_key', $_REQUEST['nationality'] ) );
					if ( ! empty( $nationality ) ) {
						$json_url = add_query_arg( 'nat', $nationality, $json_url );
					}
					$arr_post_header['nat'] = $nationality;
				}

				if ( isset( $_REQUEST['total_users'] ) ) {
					$total_users = intval( $_REQUEST['total_users'] );
					$json_url    = add_query_arg( 'results', $total_users, $json_url );

					$arr_post_header['results'] = $total_users;
				}

				$response = wp_remote_get( $json_url, array( 'timeout' => 2220 ) );

				if ( is_wp_error( $response ) ) {
					wp_die( 'Response: ' . esc_attr( $response->get_error_message() ) );
				}

				if ( is_array( $response ) ) {
					if ( isset( $response['body'] ) && ! empty( $response['body'] ) ) {
						$json = json_decode( $response['body'] );
					}
				}

				if ( empty( $json ) ) {
					wp_die( 'Response: No data.' );
				}

				if ( ! empty( $_REQUEST['disable_emails'] ) ) {
					$this->disable_emails();
				}

				$default_role = get_option( 'default_role', true );

				foreach ( $json->results as $dummy ) {

					$login = $dummy->login->username;
					if ( username_exists( $login ) ) {
						$login = $login . '_' . wp_generate_password( 4, false );
					}

					if ( ! empty( $_REQUEST['email_pattern'] ) ) {
						$email = str_replace( '@', "+$login-dummy@", sanitize_user( $_REQUEST['email_pattern'] ) ); //phpcs:ignore
					} elseif ( isset( $_SERVER['SERVER_NAME'] ) && substr_count( sanitize_key( $_SERVER['SERVER_NAME'] ), '.' ) ) {
						$site_url = sanitize_key( $_SERVER['SERVER_NAME'] );
						$email    = "$login-dummy@{$site_url}";
					} else {
						$email = $dummy->email;
					}

					if ( ! empty( $_REQUEST['password'] ) ) {
						$password = $_REQUEST['password']; //phpcs:ignore
					} else {
						$password = wp_generate_password( 8, false );
					}

					$userdata = array(
						'display_name' => ucfirst( $dummy->name->first ) . ' ' . ucfirst( $dummy->name->last ),
						'first_name'   => ucfirst( $dummy->name->first ),
						'last_name'    => ucfirst( $dummy->name->last ),
						'user_email'   => $email,
						'user_login'   => $login,
						'user_pass'    => $password,
					);

					$user_id = wp_insert_user( $userdata );

					if ( is_wp_error( $user_id ) ) {
						$failed_dummies++;
					}

					$location = $dummy->location->street->number . ', ' . $dummy->location->street->name . ' ' . $dummy->location->city . ' ' . $dummy->location->state . ' ' . $dummy->location->country;

					$usermeta = array(
						'synced_profile_photo'      => $dummy->picture->large,
						'gender'                    => ucfirst( $dummy->gender ),
						'birth_date'                => gmdate( 'Y/m/d', strtotime( $dummy->dob->date ) ),
						'_um_last_login'            => gmdate( 'Y/m/d', strtotime( $dummy->registered->date ) ),
						'mobile_number'             => $dummy->cell,
						'phone_number'              => $dummy->phone,
						'synced_gravatar_hashed_id' => md5( strtolower( trim( $dummy->email ) ) ),
						'account_status'            => 'approved',
						'_um_profile_dummy'         => true,
						'role'                      => isset( $default_role ) ? $default_role : 'subscriber',
						'localisation'              => $location,
						'localisation_lat'          => $dummy->location->coordinates->latitude,
						'localisation_lng'          => $dummy->location->coordinates->longitude,
						'localisation_url'          => 'https://maps.google.com/?q=' . $location,
					);

					if ( isset( $_REQUEST['add_cover_photo'] ) && 1 === $_REQUEST['add_cover_photo'] ) {

						$rand  = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f' );
						$color = $rand[ wp_rand( 0, 15 ) ] . $rand[ wp_rand( 0, 15 ) ] . $rand[ wp_rand( 0, 15 ) ] . $rand[ wp_rand( 0, 15 ) ] . $rand[ wp_rand( 0, 15 ) ] . $rand[ wp_rand( 0, 15 ) ];

						$usermeta['synced_cover_photo'] = 'http://placehold.it/650x350/' . $color . '/' . $color;
					}

					foreach ( $usermeta as $key => $value ) {
						update_user_meta( $user_id, $key, $value );
					}
				}

				wp_safe_redirect( admin_url( 'admin.php?page=generate_random_users' ) );

				break;

			case 'remove_random_users':
				$dummies = new WP_User_Query(
					array(
						'fields'       => array( 'ID' ),
						'meta_key'     => '_um_profile_dummy', //phpcs:ignore slow query ok.
						'meta_value'   => true,  //phpcs:ignore slow query ok.
						'meta_compare' => '=',
					)
				);

				if ( $dummies->total_users > 0 ) {
					foreach ( $dummies->get_results() as $dummy ) {
						if ( isset( $dummy->ID ) ) {
							wp_delete_user( $dummy->ID );
						}
					}
				}

				delete_option( 'um_generated_dumies' );
				wp_safe_redirect( admin_url( 'admin.php?page=generate_random_users' ) );

				break;

		}
	}

}
