<?php
/**
 * Core class
 *
 * @package UM_Extended_Block_Emails\Core
 */

namespace UM_Extended_Block_Emails;

/**
 * Class Core to handle all plugin initialization.
 *
 * @since 1.0.0
 */
class Core {

	/**
	 * CDN API URL
	 */
	const API_URL = 'https://rawcdn.githack.com/disposable/disposable-email-domains/master/domains.json';

	/**
	 * Init
	 *
	 * @since 1.0.0
	 *
	 * @param string $file Name of the File.
	 */
	public function __construct( $file ) {

		register_activation_hook( $file, array( $this, 'plugin_activation' ) );

		/**
		 * Update List Daily
		 */
		add_action( 'um_daily_scheduled_events', array( $this, 'update_blacklist' ) );

		add_filter( 'um_settings_structure', array( $this, 'settings' ) );

		add_filter( 'um_get_option_filter__blocked_emails', array( $this, 'merge_disposable_emails' ) );

	}

	/**
	 *  Sync list on plugin activation
	 *
	 * @since 1.0.0
	 */
	public function plugin_activation() {
		$this->update_blacklist();
	}

	/**
	 * Update Disposable Email Providers
	 *
	 * @since 1.0.0
	 */
	public function update_blacklist() {

		$response = wp_remote_get( self::API_URL );
		if ( is_array( $response ) && ! is_wp_error( $response ) ) {
			$body                = $response['body'];
			$arr_email_providers = json_decode( $body );
			if ( is_array( $arr_email_providers ) ) {
				$arr_email_formatted = array_map(
					function( $host ) {
						return '*@' . $host;
					},
					$arr_email_providers
				);
			}

			\UM()->options()->update( 'blocked_disposable_emails', implode( PHP_EOL, $arr_email_formatted ) );

		}

	}

	/**
	 * Add settings for Disposable Email Domains
	 *
	 * @param array $fields Fields Settings.
	 */
	public function settings( $fields ) {

		$new_field = array(
			'id'          => 'blocked_disposable_emails',
			'type'        => 'textarea',
			'label'       => __( 'Blocked Disposable Email Domains', 'ultimate-member' ),
			'description' => __( 'This updates automatically & daily so you don\'t need to modify this field. This merges with the Blocked Email Addresses option above.', 'ultimate-member' ),
		);

		$fields['access']['sections']['other']['fields'][] = $new_field;

		return $fields;
	}

	/**
	 * Merge Disposble Emails with Blocked Emails
	 *
	 * @param string $emails Existing emails.
	 */
	public function merge_disposable_emails( $emails ) {

		if ( is_admin() && ! wp_doing_ajax() ) {
			return $emails;
		}

		return $emails . PHP_EOL . \UM()->options()->get( 'blocked_disposable_emails' );
	}

}
