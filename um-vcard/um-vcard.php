<?php
/*
Plugin Name: Ultimate Member - VCard
Plugin URI: https://www.ultimatemember.com
Description: Adds a predefined field to generate VCard for users to download from their profiles.
Version: 1.0.0
Author: Ultimate Member Ltd.
Author URI: https://www.ultimatemember.com
Text Domain: um-vcard
*/

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

define( 'UM_VCARD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'UM_VCARD_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

// Composer dependencies.
if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
}

use JeroenDesloovere\VCard\Formatter\Formatter;
use JeroenDesloovere\VCard\Formatter\VcfFormatter;
use JeroenDesloovere\VCard\Parser\Parser;
use JeroenDesloovere\VCard\Parser\VcfParser;
use JeroenDesloovere\VCard\Property\Address;
use JeroenDesloovere\VCard\Property\Anniversary;
use JeroenDesloovere\VCard\Property\Birthdate;
use JeroenDesloovere\VCard\Property\Email;
use JeroenDesloovere\VCard\Property\Gender;
use JeroenDesloovere\VCard\Property\Logo;
use JeroenDesloovere\VCard\Property\Name;
use JeroenDesloovere\VCard\Property\Nickname;
use JeroenDesloovere\VCard\Property\Note;
use JeroenDesloovere\VCard\Property\Parameter\Kind;
use JeroenDesloovere\VCard\Property\Parameter\Revision;
use JeroenDesloovere\VCard\Property\Parameter\Type;
use JeroenDesloovere\VCard\Property\Parameter\Version;
use JeroenDesloovere\VCard\Property\Photo;
use JeroenDesloovere\VCard\Property\Telephone;
use JeroenDesloovere\VCard\Property\Title;
use JeroenDesloovere\VCard\Property\Role;
use JeroenDesloovere\VCard\Property\Url;
use JeroenDesloovere\VCard\VCard;

/**
 * Generate VCard on profile update
 *
 * @param integer $user_id The current user's ID.
 */
function um_vcard_generate( $user_id ) {

	um_fetch_user( $user_id );
	$lastname   = um_user( 'last_name' );
	$firstname  = um_user( 'first_name' );
	$additional = um_user( 'display_name' );
	$prefix     = um_user( 'prefix' );
	$suffix     = um_user( 'suffix' );

	$user_dir = UM()->uploader()->get_upload_user_base_dir( $user_id ) . DIRECTORY_SEPARATOR;

	$vcard = new VCard( Kind::organization() );
	$vcard->add( new Name( $lastname, $firstname, $additional, $prefix, $suffix ) );
	$vcard->add( new Title( um_user( 'title' ) ) );
	$vcard->add( new Role( um_user( 'role' ) ) );
	$vcard->add( new Telephone( um_user( 'mobile_number' ) ) );
	$vcard->add( new Photo( file_get_contents( $user_dir . um_profile( 'profile_photo' ) ) ) );
	$vcard->add( new Logo( file_get_contents( $user_dir . um_profile( 'profile_photo' ) ) ) );

	add_action( 'um_vcard_before_save', $vcard, $user_id );

	$formatter = new Formatter( new VcfFormatter(), 'vcard' );
	$formatter->addVCard( $vcard );
	$formatter->save( $user_dir );

	update_user_meta( $user_id, 'vcard', 'vcard.vcf' );

}
add_action( 'um_after_user_updated', 'um_vcard_generate' );


/**
 * Add Vcard predefined field.
 *
 * @param array $fields predefined fields array.
 */
function um_vcard_add_field( $fields ) {
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
add_filter( 'um_predefined_fields_hook', 'um_vcard_add_field', 10, 1 );

// Don't display the field in Edit View.
add_filter( 'um_vcard_form_edit_field', '__return_empty_string' );

