<?php
/**
 * Core class
 *
 * @package UM_Extended_Set_Password\Core
 */

namespace UM_Extended_Set_Password;

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
		add_filter( 'um_template_tags_patterns_hook', array( $this, 'add_email_template_placeholder' ), 10, 1 );
		add_filter( 'um_template_tags_replaces_hook', array( $this, 'set_password_add_replace_placeholder' ), 10, 1 );
		add_action( 'template_redirect', array( $this, 'set_password_text' ) );
		add_action( 'um_after_changing_user_password', array( $this, 'password_has_changed' ) );
		add_filter( 'um_custom_success_message_handler', array( $this, 'change_password_set_message' ), 10, 2 );
	}

	/**
	 * Add set password placeholder tag
	 *
	 * @param array $placeholders Placeholder array.
	 */
	public function add_email_template_placeholder( $placeholders ) {
		$placeholders[] = '{set_password_link}';

		return $placeholders;
	}

	/**
	 * Generate set password link for placeholder
	 *
	 * @param array $replace_placeholders .
	 */
	public function set_password_add_replace_placeholder( $replace_placeholders ) {

		// A fix to avoid internal caching in the class um\core\User.
		static $last_user_id = 0;
		$user_id             = um_user( 'ID' );
		if ( $last_user_id !== $user_id ) {
			$last_user_id = $user_id;
		}

		$set_password = get_user_meta( $user_id, 'um_set_password_required', true );
		if ( ! empty( $set_password ) ) {
			$hash = wp_hash( $user_id );
			update_user_meta( $user_id, 'um_set_password_required_hash', $hash );

			$url = add_query_arg(
				array(
					'act'  => 'umext_set_pass',
					'hash' => $hash,
				),
				um_get_core_page( 'password-reset' )
			);

			$replace_placeholders[] = $url;
		} else {
			$url                    = UM()->password()->reset_url();
			$replace_placeholders[] = add_query_arg( array( 'set_pass' => 'new_user' ), $url );
		}

		return $replace_placeholders;
	}

	/**
	 * Set Password text
	 */
	public function set_password_text() {

		if ( isset( $_REQUEST['act'] ) && ! empty( $_REQUEST['hash'] ) && um_is_core_page( 'password-reset' ) && 'umext_set_pass' === $_REQUEST['act'] ) {  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$hash =  esc_attr( $_REQUEST['hash'] );  // phpcs:ignore 
			$args = array(
				'meta_key'   => 'um_set_password_required_hash', //phpcs:ignore
				'meta_value' => $hash, //phpcs:ignore
				'number'     => 1 //phpcs:ignore
			);

			$users = get_users( $args );
			$user  = reset( $users );
			um_fetch_user( $user->ID );
			delete_user_meta( $user->ID, 'um_set_password_required_hash' );
			wp_safe_redirect( UM()->password()->reset_url() );
			exit;
		}

		if ( ! isset( $_REQUEST['set_pass'] ) ) {  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		add_filter(
			'um_edit_label_user_password',
			function ( $text ) {
				return __( 'Set your Password', 'um-extended' );
			}
		);

		add_filter(
			'gettext',
			function ( $translated_text, $untranslated_text, $domain ) {
				if ( 'Change my password' === $translated_text ) {
					return __( 'Save my password', 'um-extended' );
				}
				return $translated_text;
			},
			10,
			3
		);
	}

	/**
	 * Redirect with parameter to set Password text
	 *
	 * @param integer $user_id User ID.
	 */
	public function password_has_changed( $user_id ) {
		if ( isset( $_REQUEST['set_pass'] ) && 'new_user' === sanitize_key( $_REQUEST['set_pass'] ) ) {  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			um_fetch_user( $user_id );
			UM()->common()->users()->approve( $user_id, true );
		}
		wp_safe_redirect( um_get_core_page( 'login', 'password_set' ) );
		exit;
	}

	/**
	 * Add custom message for Login
	 *
	 * @param string $success Success Message.
	 * @param string $key Message key.
	 */
	public function change_password_set_message( $success, $key ) {
		if ( 'password_set' === $key ) {
			$success = __( 'Your password has been set. Please login below.', 'um-extended' );
		}
		return $success;
	}
}
