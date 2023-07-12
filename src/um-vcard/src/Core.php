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

		add_action( 'um_after_user_updated', array( $this, 'generate' ) );
		add_action( 'um_registration_complete', array( $this, 'generate' ) );

	}

	/**
	 * Generate VCard on profile update
	 *
	 * @param integer $user_id The current user's ID.
	 */
	public function generate( $user_id ) {

		um_fetch_user( $user_id );
		$lastname   = um_user( 'last_name' );
		$firstname  = um_user( 'first_name' );
		$additional = um_user( 'display_name' );
		$prefix     = um_user( 'prefix' );
		$suffix     = um_user( 'suffix' );

		$user_dir = UM()->uploader()->get_upload_user_base_dir( $user_id ) . DIRECTORY_SEPARATOR;

		$vcard = new VCard( null, Version::version3() );
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
			$type            = new Type( 'work' );
			$value           = new Value( 'text' );
			$vcard->add( new Telephone( $telephone_number, $type, $value ) );
		}

		$phone_number = um_user( 'phone_number' );
		if ( $phone_number ) {
			$telephone_number = preg_replace( '/[^0-9+]/i', '', $phone_number );
			$type            = new Type( 'home' );
			$value           = new Value( 'text' );
			$vcard->add( new Telephone( $telephone_number, $type, $value ) );
		}

		$user_url = um_user( 'user_url' );
		if ( $user_url ) {
			$vcard->add( new Url( $user_url ) );
		}

		if ( file_exists( $user_dir . um_profile( 'profile_photo' ) ) && is_file( $user_dir . um_profile( 'profile_photo' ) ) ) {

			$avatar   = $user_dir . um_profile( 'profile_photo' );
			$data     = file_get_contents( $avatar );
			// we should split this large string into smaller 72-symbols strings, to correspond the standard.

			$vcard->add( new Photo( $data ) );
			$vcard->add( new Logo( $data ) );

		}

		add_action( 'um_vcard_before_save', $vcard, $user_id );

		$formatter = new Formatter( new VcfFormatter(), 'vcard' );
		$formatter->addVCard( $vcard );
		$formatter->save( $user_dir );

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


}
