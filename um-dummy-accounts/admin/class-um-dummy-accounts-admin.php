<?php
// class PHPMailerNO
require_once 'class-php-mailer-no.php';

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Um_Dummy_Accounts
 * @subpackage Um_Dummy_Accounts/admin
 * @author     Ultimate Member Ltd. <support@ultimatemember.com>
 */
class Um_Dummy_Accounts_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Um_Dummy_Accounts_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Um_Dummy_Accounts_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/um-dummy-accounts-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Um_Dummy_Accounts_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Um_Dummy_Accounts_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/um-dummy-accounts-admin.js', array( 'jquery' ), $this->version, false );
	}

	public function admin_menu() {

		global $ultimatemember;
		add_submenu_page( 'ultimatemember', 'Generate Dummies', 'Generate Dummies', 'manage_options', 'generate_random_users', array( &$this, 'content' ) );
	}

	public function admin_init() {
		if( isset( $_REQUEST[ 'um-addon-hook' ] ) ) {
			$hook = $_REQUEST[ 'um-addon-hook' ];
			do_action( "um_admin_addon_hook", $hook );
		}
	}

	public function content() {
		?>

		<div class="wrap">
			<h2>Ultimate Member <sup style="font-size:15px"><?php echo ultimatemember_version; ?></sup></h2>
			<h3>Generate Dummies</h3>

			<?php
			if( isset( $this->content ) ) {
				echo $this->content;
			}
			else {
				?><p>This tool allows you to add dummies as Ultimate Member users. </p><?php
				$this->render_form();
			}
			?>

		</div>

		<?php
	}

	public function disable_emails() {
		global $phpmailer;
		$phpmailer = new PHPMailerNO( true );
	}

	public function render_form() {
		$dummies = new WP_User_Query(
				array(
				'fields'			 => array( 'ID' ),
				'meta_key'		 => '_um_profile_dummy',
				'meta_value'	 => true,
				'meta_compare' => '='
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
							<label><?php _e( 'Actions', 'um-dummy-accounts' ); ?></label>
						</th>
						<td>
							<button type="submit" name="um-addon-hook" value="generate_random_users" class="button button-primary"><?php _e( 'Start Generating Dummies', 'um-dummy-accounts' ); ?></button>

							<?php if( $dummies->total_users > 0 ): ?>
								<button type="submit" name="um-addon-hook" value="remove_random_users" class="button button-secondary"><?php printf( __( "Remove Generated Dummies (%d)", 'um-dummy-accounts' ), $dummies->total_users ); ?></button>
							<?php endif; ?>

						</td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th scope="row">
							<label for="total_users"><?php _e( 'How many dummies?', 'um-dummy-accounts' ); ?></label>
						</th>
						<td>
							<input type="number" name="total_users" value="10" min="1" max="999" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="nationality"><?php _e( 'Available Nationalities', 'um-dummy-accounts' ); ?></label>
						</th>
						<td>
							<select name="nationality[]" multiple style="min-width: 75px;">
								<?php
								foreach( $nationality as $code ) {
									echo "<option value='" . strtolower( $code ) . "'/> " . $code . "</option>";
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="gender"><?php _e( 'Gender', 'um-dummy-accounts' ); ?></label>
						</th>
						<td>
							<label><input type='radio' name="gender" value="male" /> <?php _e( 'Male', 'um-dummy-accounts' ); ?></label>&nbsp;&nbsp;
							<label><input type='radio' name="gender" value="female" /> <?php _e( 'Female', 'um-dummy-accounts' ); ?></label>&nbsp;&nbsp;
							<label><input type='radio' name="gender" value="both" checked="checked" /> <?php _e( 'Both', 'um-dummy-accounts' ); ?></label>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="add_cover_photo"><?php _e( 'Cover photos', 'um-dummy-accounts' ); ?></label>
						</th>
						<td>
							<label><input type="checkbox" name="add_cover_photo" value="1" /> <?php _e( 'Generates random colored cover photos', 'um-dummy-accounts' ); ?></label>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="disable_emails"><?php _e( 'Notification', 'um-dummy-accounts' ); ?></label>
						</th>
						<td>
							<label><input type="checkbox" name="disable_emails" value="1" checked="checked" /> <?php _e( 'Disable email notification', 'um-dummy-accounts' ); ?></label>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="email_pattern"><?php _e( 'Account Email pattern', 'um-dummy-accounts' ); ?></label>
						</th>
						<td>
							<label><input type="email" name="email_pattern" placeholder="example@gmail.com" class="regular-text" /></label>
							<p><small><?php _e( 'If you leave this blank, Admin email will be used as a pattern.', 'um-dummy-accounts' ); ?></small></p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="password"><?php _e( 'Account Passwords', 'um-dummy-accounts' ); ?></label>
						</th>
						<td>
							<label><input type="password" name="password" class="regular-text" /></label>
							<p><small><?php _e( 'If you leave this blank, it will generate random strings password', 'um-dummy-accounts' ); ?></small></p>
						</td>
					</tr>
				</tbody>
			</table>
		</form>

		<?php
	}

	public function um_admin_addon_hook( $hook ) {
		switch( $hook ) {
			case 'generate_random_users':
				$json_url = "https://randomuser.me/api/";

				$arr_post_header = array();
				$failed_dummies = 0;

				if( isset( $_REQUEST[ 'gender' ] ) ) {
					$gender = $_REQUEST[ 'gender' ];
					if( $gender != 'both' ) {
						$json_url = add_query_arg( 'gender', $gender, $json_url );
						$arr_post_header[ 'gender' ] = $gender;
					}
				}

				if( isset( $_REQUEST[ 'nationality' ] ) ) {
					$nationality = implode( ",", $_REQUEST[ 'nationality' ] );
					if( !empty( $nationality ) ) {
						$json_url = add_query_arg( 'nat', $nationality, $json_url );
					}
					$arr_post_header[ 'nat' ] = $nationality;
				}

				if( isset( $_REQUEST[ 'total_users' ] ) ) {
					$total_users = intval( $_REQUEST[ 'total_users' ] );
					$json_url = add_query_arg( 'results', $total_users, $json_url );
					$arr_post_header[ 'results' ] = $total_users;
				}

				$response = wp_remote_get( $json_url, array( 'timeout' => 2220 ) );

				if( is_wp_error( $response ) ) {
					wp_die( 'Response: ' . $response->get_error_message() );
				}

				if( is_array( $response ) ) {
					if( isset( $response[ 'body' ] ) && !empty( $response[ 'body' ] ) ) {
						$json = json_decode( $response[ 'body' ] );
					}
				}

				if( empty( $json ) ) {
					wp_die( 'Response: No data.' );
				}

				if( !empty( $_REQUEST[ 'disable_emails' ] ) ) {
					$this->disable_emails();
				}

				$default_role = get_option( "default_role", true );

				/* EXAMPLE:
				  object(stdClass)[993]
				  public 'gender' => string 'male' (length=4)
				  public 'name' =>
				  object(stdClass)[967]
				  public 'title' => string 'Mr' (length=2)
				  public 'first' => string 'Dean' (length=4)
				  public 'last' => string 'Rodriguez' (length=9)
				  public 'location' =>
				  object(stdClass)[1062]
				  public 'street' =>
				  object(stdClass)[1067]
				  public 'number' => int 4915
				  public 'name' => string 'The Avenue' (length=10)
				  public 'city' => string 'Preston' (length=7)
				  public 'state' => string 'Rutland' (length=7)
				  public 'country' => string 'United Kingdom' (length=14)
				  public 'postcode' => string 'EV07 4YF' (length=8)
				  public 'coordinates' =>
				  object(stdClass)[1068]
				  public 'latitude' => string '5.3315' (length=6)
				  public 'longitude' => string '-98.7511' (length=8)
				  public 'timezone' =>
				  object(stdClass)[1069]
				  public 'offset' => string '+2:00' (length=5)
				  public 'description' => string 'Kaliningrad, South Africa' (length=25)
				  public 'email' => string 'Dean.Rodriguez@example.com' (length=26)
				  public 'login' =>
				  object(stdClass)[1070]
				  public 'uuid' => string '3c87dab7-204f-4a86-a175-7d829a18f835' (length=36)
				  public 'username' => string 'lazyfrog502' (length=11)
				  public 'password' => string 'beagle' (length=6)
				  public 'salt' => string 'lcs8QHzi' (length=8)
				  public 'md5' => string '1a5a047d80cfbb642643ef2549633fe7' (length=32)
				  public 'sha1' => string '7aa81aa5453f9ea4faaddd8349e37cda67f10aa1' (length=40)
				  public 'sha256' => string 'fefa956293b6710410bdcf3434afeafad633e31fa2954ff1cee22adf3a2280bd' (length=64)
				  public 'dob' =>
				  object(stdClass)[1071]
				  public 'date' => string '1989-02-16T07:51:43.115Z' (length=24)
				  public 'age' => int 30
				  public 'registered' =>
				  object(stdClass)[1072]
				  public 'date' => string '2012-01-13T23:20:53.373Z' (length=24)
				  public 'age' => int 7
				  public 'phone' => string '015394 94578' (length=12)
				  public 'cell' => string '0792-059-087' (length=12)
				  public 'id' =>
				  object(stdClass)[1073]
				  public 'name' => string 'NINO' (length=4)
				  public 'value' => string 'EL 50 74 82 Y' (length=13)
				  public 'picture' =>
				  object(stdClass)[1074]
				  public 'large' => string 'https://randomuser.me/api/portraits/men/14.jpg' (length=46)
				  public 'medium' => string 'https://randomuser.me/api/portraits/med/men/14.jpg' (length=50)
				  public 'thumbnail' => string 'https://randomuser.me/api/portraits/thumb/men/14.jpg' (length=52)
				  public 'nat' => string 'GB' (length=2)
				 */

				foreach( $json->results as $dummy ) {

					$login = $dummy->login->username;
					if( username_exists( $login ) ) {
						$login = $login . '_' . wp_generate_password( 4, false );
					}

					if( !empty( $_REQUEST[ 'email_pattern' ] ) ) {
						$email = str_replace( '@', "+$login-dummy@", $_REQUEST[ 'email_pattern' ] );
					}
					else {
						$site_url = @$_SERVER[ 'SERVER_NAME' ];
						$email = "$login-dummy@{$site_url}";
					}

					if( !empty( $_REQUEST[ 'password' ] ) ) {
						$password = $_REQUEST[ 'password' ];
					}
					else {
						$password = wp_generate_password( 8, false );
					}

					$userdata = array(
							'display_name' => ucfirst( $dummy->name->first ) . " " . ucfirst( $dummy->name->last ),
							'first_name'	 => ucfirst( $dummy->name->first ),
							'last_name'		 => ucfirst( $dummy->name->last ),
							'user_email'	 => $email,
							'user_login'	 => $login,
							'user_pass'		 => $password,
					);

					$user_id = wp_insert_user( $userdata );

					if( is_wp_error( $user_id ) ) {
						$failed_dummies++;
					}

					$location = $dummy->location->street->number.', '.$dummy->location->street->name.' '.$dummy->location->city.' '.$dummy->location->state.' '.$dummy->location->country;

					$usermeta = array(
							'synced_profile_photo'			 => $dummy->picture->large,
							'gender'										 => ucfirst( $dummy->gender ),
							'birth_date'								 => date( "Y/m/d", strtotime( $dummy->dob->date ) ),
							'_um_last_login'						 => date( "Y/m/d", strtotime( $dummy->registered->date ) ),
							'mobile_number'							 => $dummy->cell,
							'phone_number'							 => $dummy->phone,
							'synced_gravatar_hashed_id'	 => md5( strtolower( trim( $dummy->email ) ) ),
							'account_status'						 => 'approved',
							'_um_profile_dummy'					 => true,
							'role'											 => isset( $default_role ) ? $default_role : 'subscriber',
							'localisation' => $location,
							'localisation_lat' => $dummy->location->coordinates->latitude,
							'localisation_lng' => $dummy->location->coordinates->longitude,
							'localisation_url' => "https://maps.google.com/?q=".$location,
					);

					if( isset( $_REQUEST[ 'add_cover_photo' ] ) && $_REQUEST[ 'add_cover_photo' ] == 1 ) {

						$rand = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f' );
						$color = $rand[ rand( 0, 15 ) ] . $rand[ rand( 0, 15 ) ] . $rand[ rand( 0, 15 ) ] . $rand[ rand( 0, 15 ) ] . $rand[ rand( 0, 15 ) ] . $rand[ rand( 0, 15 ) ];

						$usermeta[ 'synced_cover_photo' ] = 'http://placehold.it/650x350/' . $color . '/' . $color;
					}

					foreach( $usermeta as $key => $value ) {
						update_user_meta( $user_id, $key, $value );
					}
				}

				wp_redirect( admin_url( "admin.php?page=generate_random_users" ) );
				exit;

				break;

			case 'remove_random_users':

				$dummies = new WP_User_Query(
						array(
						'fields'			 => array( 'ID' ),
						'meta_key'		 => '_um_profile_dummy',
						'meta_value'	 => true,
						'meta_compare' => '='
						)
				);

				if( $dummies->total_users > 0 ) {
					foreach( $dummies->get_results() as $dummy ) {

						if( isset( $dummy->ID ) ) {
							wp_delete_user( $dummy->ID );
						}
					}
				}

				delete_option( 'um_generated_dumies' );
				wp_redirect( admin_url( "admin.php?page=generate_random_users" ) );
				exit;

				break;

			default:

				break;
		}
	}

}
