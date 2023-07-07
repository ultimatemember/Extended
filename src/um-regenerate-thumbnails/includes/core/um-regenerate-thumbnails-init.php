<?php
class UM_Regenerate_Thumbnails_API{

	function __construct( ){
		add_action("admin_menu",array( $this, "um_regenerate_thumb_menu"), 99 );
		add_action("admin_init",array($this,"process_resize_image") );
		$this->processed_users = '';
	
	}

	public function um_regenerate_thumb_menu(){
		
			add_submenu_page('ultimatemember', 'Regenerate Thumbnails', 'Regenerate Thumbnails', 'manage_options', 'um_regenerate_thumbnails', array( $this, 'admin_settings_content')  );
			
		
	}
       
	public function process_resize_image(){
        
        // Profile Photo
		if( isset( $_REQUEST["um_regenerate_pp_thumbnails"] ) ){	

			if( $_REQUEST['width'] == "" || $_REQUEST['height'] == "" ){
				$this->processed_users = "Invalid size input";
			}else{

				$width = $_REQUEST['width'];
				$height = $_REQUEST['height'];
				$quality = $_REQUEST['quality'];

				$args = array( 
					'number' => -1,
					'meta_key' => 'profile_photo',
				 );
				// The Query
				$user_query = new WP_User_Query( $args );

				// User Loop
				if ( ! empty( $user_query->get_results() ) ) {

					foreach ( $user_query->get_results() as $user ) {
						um_fetch_user( $user->ID );
						
						if( function_exists('UM') ){
							$original_file = UM()->files()->upload_basedir."{$user->ID}/".um_profile("profile_photo");
						}else{
							$original_file = UM()->files()->upload_basedir."{$user->ID}/".um_profile("profile_photo");
						}

						if( 0 == $height ){
							$append_size = $width;
						}else{
							$append_size = "{$width}x{$height}";
						}

						$new_file_name_swap = str_replace( "profile_photo", "profile_photo-{$append_size}", um_profile("profile_photo") );
						
						if( function_exists('UM') ){
							$new_file = UM()->files()->upload_basedir."{$user->ID}/{$new_file_name_swap}";
						}else{
							$new_file = UM()->files()->upload_basedir."{$user->ID}/{$new_file_name_swap}";
						}

						$this->processed_users .= "<strong>".um_user('display_name')."</strong><span style=\"color:green\" class=\"dashicons dashicons-yes\"></span></br>&nbsp;&nbsp; - Original: {$original_file}<br/>&nbsp;&nbsp; - New: {$new_file}";

						if ( ! file_exists( $original_file ) || ! @copy( $original_file, $new_file ) ) {
						    $this->processed_users .= "&nbsp;<strong style='color:red;'>Failed to copy</strong>";
						}

						$this->processed_users .= "<br/>";

						$image = wp_get_image_editor( $original_file);
						if ( ! is_wp_error( $image ) ) {

							$size = $image->get_size();

							$uploaded_height = $size['height'];
							$uploaded_width = $size['width'];
							
						    $image->set_quality( $quality );
						   if( $height == 0 ){
								$image->resize( $width, $uploaded_height, true );
						    }elseif( $width == 0 ){
								$image->resize( $uploaded_width, $height, true );
						    }else{
								$image->resize( $width, $height, true );
	                        }
						    $image->save( $new_file  );
						}
					}

				} else {
					$this->processed_users = 'No users found.';
				}
			}

		}

		// Cover Photo
		if( isset( $_REQUEST["um_regenerate_cp_thumbnails"] ) ){	

			if( $_REQUEST['width'] == "" || $_REQUEST['height'] == "" ){
				$this->processed_users = "Invalid size input";
			}else{

				$width = $_REQUEST['width'];
				$height = $_REQUEST['height'];
				$quality = $_REQUEST['quality'];

				$args = array( 
					'number' => -1,
					'meta_key' => 'cover_photo',
				 );
				// The Query
				$user_query = new WP_User_Query( $args );

				// User Loop
				if ( ! empty( $user_query->get_results() ) ) {

					foreach ( $user_query->get_results() as $user ) {
						um_fetch_user( $user->ID );
						
						if( function_exists('UM') ){
							$original_file = UM()->files()->upload_basedir."{$user->ID}/".um_profile("cover_photo");
						}else{
							$original_file = UM()->files()->upload_basedir."{$user->ID}/".um_profile("cover_photo");
						}

						if( 0 == $height ){
							$append_size = $width;
						}else{
							$append_size = "{$width}x{$height}";
						}

						$new_file_name_swap = str_replace( "cover_photo", "cover_photo-{$append_size}", um_profile("cover_photo") );
						
						if( function_exists('UM') ){
							$new_file = UM()->files()->upload_basedir."{$user->ID}/{$new_file_name_swap}";
						}else{
							$new_file = UM()->files()->upload_basedir."{$user->ID}/{$new_file_name_swap}";
						}

						$this->processed_users .= "<strong>".um_user('display_name')."</strong><span style=\"color:green\" class=\"dashicons dashicons-yes\"></span></br>&nbsp;&nbsp; - Original: {$original_file}<br/>&nbsp;&nbsp; - New: {$new_file}";

						if ( ! file_exists( $original_file ) || ! @copy( $original_file, $new_file ) ) {
						    $this->processed_users .= "&nbsp;<strong style='color:red;'>Failed to copy</strong>";
						}

						$this->processed_users .= "<br/>";

						$image = wp_get_image_editor( $original_file);
						if ( ! is_wp_error( $image ) ) {

							list( $uploaded_width, $uploaded_height ) = $image->get_size();
						
							$image->set_quality( $quality );
							if( $height == 0 ){
								$image->resize( $width, $uploaded_height, true );
						    }elseif( $width == 0 ){
								$image->resize( $uploaded_width, $height, true );
						    }else{
							    $image->resize( $width, $height, true );
	                        }
						    $image->save( $new_file  );
						}
					}

				} else {
					$this->processed_users = 'No users found.';
				}
			}

		}
	}

	public function admin_settings_content(){
		?>

		<div class="wrap">
		
			<h2>Ultimate Member <sup style="font-size:15px"><?php echo ultimatemember_version; ?></sup> - Regenerate Profile Photo Thumbnails</h2>

			<br/>
			<form method="post">
				<label>Width: <input type="text" name="width" value="120"/></label>
				<label>Height: <input type="text" name="height" value="120"/></label>
				<label>Image Quality: <input type="text" name="quality" value="100"/></label>
				<br>
				<input type="submit" value="Regenerate Profile Photo Thumbnails" name="um_regenerate_pp_thumbnails" class="button primary" />
				<input type="submit" value="Regenerate Cover Photo Thumbnails" name="um_regenerate_cp_thumbnails" class="button primary" />
			</form>
			<br/>
			<br/>

			<?php 
			if( ! empty( $this->processed_users ) ){ 

				echo $this->processed_users;

			}
			?>
			
		</div>

		<?php
	}

}

new UM_Regenerate_Thumbnails_API();