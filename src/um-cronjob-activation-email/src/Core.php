<?php
/**
 * Core class
 *
 * @package UM_Extended_CronJob_Email_Activation\Core
 */

namespace UM_Extended_CronJob_Email_Activation;

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

		add_action( 'um_cron_resend_activation_link', array( $this, 'resend_activation_notify' ) );

		if ( ! wp_next_scheduled( 'um_cron_resend_activation_link' ) ) {
			$recurrence = apply_filters( 'um_cron_resend_activation_link_recurrence', 'hourly' );
			wp_schedule_event( time(), $recurrence, 'um_cron_resend_activation_link' );
		}
	}

	/**
	 * Resend email confirmation link to those users with expired secret hash
	 */
	public function resend_activation_notify() {

		$args = array(
			'fields'     => 'ID',
			'number'     => -1,
			'meta_query' => array( //phpcs:ignore
				'relation' => 'OR',
				array(
					'key'     => 'account_secret_hash_expiry',
					'value'   => time(),
					'compare' => '<=',
				),
			),
		);

		$users = get_users( $args );

		foreach ( $users as $user_id ) {

			um_fetch_user( $user_id );

			$status = um_user( 'account_status' );
			if ( 'awaiting_email_confirmation' === $status ) {
				UM()->user()->email_pending();
				um_reset_user();
			}
		}
	}
}
