<?php
/**
 * Core class
 *
 * @package UM_Extended_Cron_Delete_Users\Core
 */

namespace UM_Extended_Cron_Delete_Users;

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
		add_action( 'um_daily_scheduled_events', array( $this, 'delete_users_awaiting_email' ) );
	}

	/**
	 * Delete Users awating email
	 */
	public function delete_users_awaiting_email() {

		$after_x     = apply_filters( 'um_cron_delete_users_after', '5 days ago midnight' );
		$before_x    = apply_filters( 'um_cron_delete_users_before', '1 day ago' );
		$user_status = apply_filters( 'um_cron_delete_users_status', 'awaiting_email_confirmation' );

		$args = array(
			'fields'     => 'ids',
			'number'     => -1,
			'date_query' => array(
				array(
					'after'     => $after_x,
					'before'    => $before_x,
					'inclusive' => true,
				),
			),
			'meta_query' => array( //phpcs:ignore slow query ok.
				'relation' => 'AND',
				array(
					'key'     => 'account_status',
					'value'   => $user_status,
					'compare' => '=',
				),
			),
		);

		$users = get_users( $args );

		foreach ( $users as $user ) {
			um_fetch_user( $user->ID );
			UM()->user()->delete();
		}
	}
}
