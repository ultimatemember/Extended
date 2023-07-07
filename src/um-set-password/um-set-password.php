<?php
/*
  Plugin Name: Ultimate Member - Set Password
  Plugin URI: https://www.ultimatemember.com
  Description: Generates a link for email templates to allow users to set password on account activation/registration.
  Version: 1.0.1
  Author: Ultimate Member Ltd.
  Author URI: https://www.ultimatemember.com
  Text Domain: um-set-password
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Add set password placeholder tag
 *
 * @param array $placeholders Placeholder array.
 */
function um_custom_set_password_add_placeholder( $placeholders ) {
	$placeholders[] = '{set_password_link}';

	return $placeholders;
}
add_filter( 'um_template_tags_patterns_hook', 'um_custom_set_password_add_placeholder', 10, 1 );

/**
 * Generate set password link for placeholder
 *
 * @param array $replace_placeholders .
 */
function um_custom_set_password_add_replace_placeholder( $replace_placeholders ) {

	// A fix to avoid internal caching in the class um\core\User.
	static $last_user_id = 0;
	$user_id = um_user( 'ID' );
	if ( $last_user_id !== $user_id ) {
		UM()->user()->password_reset_key = '';
		$last_user_id                    = $user_id;
	}

	$url = UM()->password()->reset_url();

	$replace_placeholders[] = add_query_arg( array( 'set_pass' => 'new_user' ), $url );
	return $replace_placeholders;
}
add_filter( 'um_template_tags_replaces_hook', 'um_custom_set_password_add_replace_placeholder', 10, 1 );

/**
 * Set Password text
 */
function um_custom_set_password_text() {
	if ( ! isset( $_REQUEST['set_pass'] ) ) {
		return;
	}

	add_filter( 'um_edit_label_user_password', function( $text ) {
		return __( 'Set your Password', 'ultimate-member' );
	} );

	add_filter( 'gettext', function ( $translated_text, $untranslated_text, $domain ) {
		if ( 'Change my password' == $translated_text ) {
			return __( 'Save my password', 'ultimate-member' );
		}
		return $translated_text;
	}, 10, 3 );
}
add_action( "template_redirect", "um_custom_set_password_text" );

/**
 * Redirect with parameter to set Password text
 *
 * @param integer $user_id User ID.
 */
function um_custom_password_has_changed( $user_id ) {
	if ( isset( $_REQUEST['set_pass'] ) && 'new_user' === sanitize_key( $_REQUEST['set_pass'] ) ) {
		um_fetch_user( $user_id );
		UM()->user()->approve( false );
	}
	exit( wp_redirect( um_get_core_page( 'login', 'password_set' ) ) );
}
add_action( 'um_after_changing_user_password', 'um_custom_password_has_changed' );

/**
 * Add custom message for Login
 *
 * @param string $success Success Message.
 * @param string $key Message key.
 */
function um_custom_password_set_message( $success, $key ) {
	if ( 'password_set' == $key ) {
		$success = __( 'Your password has been set. Please login below.', 'ultimate-member' );
	}
	return $success;
}
add_filter( 'um_custom_success_message_handler', 'um_custom_password_set_message', 10, 2 );
