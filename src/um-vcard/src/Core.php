<?php
/**
 * Core class
 *
 * @package UM_Extended_Vcard\Core
 */

namespace UM_Extended_Vcard;

use JeroenDesloovere\VCard\Formatter\Formatter;
use JeroenDesloovere\VCard\Formatter\VcfFormatter;
use JeroenDesloovere\VCard\Parser\Parser;
use JeroenDesloovere\VCard\Parser\VcfParser;
use JeroenDesloovere\VCard\Property\Address;
use JeroenDesloovere\VCard\Property\Anniversary;
use JeroenDesloovere\VCard\Property\Birthdate;
use JeroenDesloovere\VCard\Property\Email;
use JeroenDesloovere\VCard\Property\FullName;
use JeroenDesloovere\VCard\Property\Gender;
use JeroenDesloovere\VCard\Property\Logo;
use JeroenDesloovere\VCard\Property\Name;
use JeroenDesloovere\VCard\Property\Nickname;
use JeroenDesloovere\VCard\Property\Note;
use JeroenDesloovere\VCard\Property\Parameter\Kind;
use JeroenDesloovere\VCard\Property\Parameter\Revision;
use JeroenDesloovere\VCard\Property\Parameter\Type;
use JeroenDesloovere\VCard\Property\Parameter\Value;
use JeroenDesloovere\VCard\Property\Parameter\Version;
use JeroenDesloovere\VCard\Property\Photo;
use JeroenDesloovere\VCard\Property\Telephone;
use JeroenDesloovere\VCard\Property\Title;
use JeroenDesloovere\VCard\Property\Role;
use JeroenDesloovere\VCard\Property\Url;
use JeroenDesloovere\VCard\VCard;

use WP_Filesystem_Direct;

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
		add_filter( 'um_predefined_fields_hook', array( $this, 'add_field' ), 10, 1 );

		// Don't display the field in Edit View.
		add_filter( 'um_vcard_form_edit_field', '__return_empty_string' );

		// Don't remove the vcard.vcf from the members' folder.
		add_filter( 'um_can_remove_uploaded_file', array( $this, 'block_removing' ), 10, 3 );

		add_action( 'um_after_user_updated', array( $this, 'generate' ) );
		add_action( 'um_registration_complete', array( $this, 'generate' ) );

		// Remove unused field options.
		add_filter(
			'um_core_fields_hook',
			function ( $fields ) {
				if ( isset( $_REQUEST['arg3'] ) && 'vcard' === $_REQUEST['arg3'] ) { // phpcs:ignore WordPress.Security.NonceVerification
					$fields['file']['col1'] = array( '_title', '_metakey', '_help', '_visibility' );
					$fields['file']['col2'] = array( '_label', '_public', '_roles', '_icon' );
					$fields['file']['col3'] = array();
				}
				return $fields;
			}
		);
	}


	/**
	 * Don't remove the vcard.vcf file from the members' folder.
	 *
	 * @see \um\core\Uploader::remove_unused_uploads()
	 *
	 * @param boolean $can_unlink Can unlink or not.
	 * @param int     $user_id    User ID.
	 * @param string  $str        File name.
	 *
	 * @return boolean
	 */
	public function block_removing( $can_unlink, $user_id, $str ) {
		if ( 'vcard.vcf' === $str ) {
			$can_unlink = false;
		}
		if ( 0 === strpos( $str, 'vcard-120x120.' ) ) {
			$can_unlink = false;
		}
		return $can_unlink;
	}


	/**
	 * Generate VCard on profile update
	 *
	 * @param integer $user_id The current user's ID.
	 */
	public function generate( $user_id ) {

		global $wp_filesystem;
		// Initialize the WP filesystem, no more using 'file-put-contents' function.
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}

		um_fetch_user( $user_id );
		$lastname   = um_user( 'last_name' );
		$firstname  = um_user( 'first_name' );
		$additional = um_user( 'display_name' );
		$prefix     = um_user( 'prefix' );
		$suffix     = um_user( 'suffix' );
		$file_type  = '';

		$user_dir = UM()->uploader()->get_upload_user_base_dir( $user_id ) . DIRECTORY_SEPARATOR;

		$vcard = new VCard( Kind::individual(), Version::version3() );
		$vcard->add( new Name( $lastname, $firstname ) );
		$vcard->add( new Title( um_user( 'title' ) ) );
		$vcard->add( new Role( um_user( 'role' ) ) );
		$full_name = um_user( 'full_name' );
		if ( $full_name ) {
			$vcard->add( new FullName( $full_name ) );
		}

		$nickname = um_user( 'nickname' );
		if ( $nickname ) {
			$vcard->add( new Nickname( $nickname ) );
		}

		$birth_date = um_user( 'birth_date' );
		if ( $birth_date ) {
			$temestamp = strtotime( $birth_date );
			$vcard->add( new Birthdate( gmdate( 'Ymd', $temestamp ) ) );
		}

		$gender = um_user( 'gender' );
		if ( $gender ) {
			switch ( $gender ) {
				case 'Female':
					$value = 'F';
					break;

				case 'Male':
					$value = 'M';
					break;

				case 'None':
					$value = 'N';
					break;

				case 'Other':
					$value = 'O';
					break;

				case 'Unknown':
					$value = 'U';
					break;

				default:
					$value = '';
					break;
			}

			$vcard->add( new Gender( $value ) );
		}

		$description = um_user( 'description' );
		if ( $description ) {
			$note = apply_filters( 'um_vcard_property_note', $description, $user_id );
			$vcard->add( new Note( $note ) );
		}

		$email = um_user( 'user_email' );
		if ( $email ) {
			$vcard->add( new Email( $email ) );
		}

		$mobile_number = um_user( 'mobile_number' );
		if ( $mobile_number ) {
			$telephone_number = preg_replace( '/[^0-9+]/i', '', $mobile_number );
			$type             = new Type( 'work' );
			$value            = new Value( 'text' );
			$vcard->add( new Telephone( $telephone_number, $type, $value ) );
		}

		$phone_number = um_user( 'phone_number' );
		if ( $phone_number ) {
			$telephone_number = preg_replace( '/[^0-9+]/i', '', $phone_number );
			$type             = new Type( 'home' );
			$value            = new Value( 'text' );
			$vcard->add( new Telephone( $telephone_number, $type, $value ) );
		}

		$user_url = um_user_profile_url();
		if ( $user_url ) {
			$vcard->add( new Url( $user_url ) );
		}

		if ( file_exists( $user_dir . um_profile( 'profile_photo' ) ) && is_file( $user_dir . um_profile( 'profile_photo' ) ) ) {

			$avatar_dir_path = UM()->uploader()->get_upload_user_base_dir( $user_id ) . DIRECTORY_SEPARATOR . um_profile( 'profile_photo' );
			$img             = wp_get_image_editor( $avatar_dir_path );
			$filetype        = wp_check_filetype( $avatar_dir_path );
			$file_type       = $filetype['type'];
			$vcard_filename  = 'vcard-120x120.' . $filetype['ext'];

			$vcard_avatar_dir_path = UM()->uploader()->get_upload_user_base_dir( $user_id ) . DIRECTORY_SEPARATOR . $vcard_filename;

			try {
				$img->resize( 120, null, false );
				$img->set_quality( 100 );
				$img->save( UM()->uploader()->get_upload_user_base_dir( $user_id ) . DIRECTORY_SEPARATOR . $vcard_filename );
				$vcard->add( new Photo( $this->image_encode( $vcard_avatar_dir_path ) ) );
				$vcard->add( new Logo( $this->image_encode( $vcard_avatar_dir_path ) ) );
			} catch ( \Exception $e ) {
				wp_die( esc_attr( $e->getMessage() . ' - ' . $vcard_avatar_dir_path ) );
			}
		}

		add_action( 'um_vcard_before_save', $vcard, $user_id );

		$formatter = new Formatter( new VcfFormatter(), 'vcard' );
		$formatter->addVCard( $vcard );
		$content = str_replace( 'PHOTO:data:' . $file_type . ';', 'PHOTO;', $formatter->getContent() );
		$content = str_replace( 'LOGO:data:' . $file_type . ';', 'LOGO;', $content );
		$formatter->save( $user_dir );

		if ( ! $wp_filesystem->put_contents( $user_dir . 'vcard.vcf', $content, 0644 ) ) {
			return wp_die( esc_attr__( 'Failed to overwrite vcard file', 'um-extended' ) );
		}

		update_user_meta( $user_id, 'vcard', 'vcard.vcf' );
	}

	/**
	 * Add Vcard predefined field.
	 *
	 * @param array $fields predefined fields array.
	 */
	public function add_field( $fields ) {
		$fields['vcard'] = array(
			'title'    => __( 'VCard', 'ultimate-member' ),
			'metakey'  => 'vcard',
			'type'     => 'file',
			'label'    => __( 'VCard', 'ultimate-member' ),
			'required' => 0,
			'public'   => 1,
			'editable' => false,
			'icon'     => 'um-icon-card',
			'color'    => '#6441A4',
		);
		return $fields;
	}

	/**
	 * Image Encode
	 *
	 * @param string $path Vcard File path.
	 * @return string
	 */
	public function image_encode( $path ) {
		$image    = file_get_contents( $path ); //phpcs:ignore
		$finfo    = new \finfo( FILEINFO_MIME_TYPE );
		$type     = $finfo->buffer( $image );
		$big_type = strtoupper( str_replace( 'image/', '', $type ) );
		return 'data:' . $type . ';ENCODING=b;TYPE=' . $big_type . ':' . base64_encode( $image ); //phpcs:ignore
	}
}
