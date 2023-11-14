<?php
/**
 * Core class
 *
 * @package UM_WPCLI\Commands
 */

namespace UM_WPCLI\Commands;

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

		if ( defined( 'WP_CLI' ) && \WP_CLI ) {
			\WP_CLI::add_command( 'um security test', array( $this, 'test_security_settings' ) );
			\WP_CLI::add_command( 'um security affected users', array( $this, 'test_security_settings_affected_users' ) );
			\WP_CLI::add_command( 'um security flagged users', array( $this, 'test_security_settings_total_flagged_accounts' ) );
		}

	}

	/**
	 * Test Security Settings.
	 * Command: wp um security test user=<user_id>
	 *
	 * @param array $args Command arguments.
	 * @param array $assoc_args Associated arguments.
	 */
	public function test_security_settings( $args, $assoc_args ) {
		if ( ! isset( $args[0] ) ) {
			\WP_CLI::error( /* translators: User Id is required. e.g user=123 */ __( 'User ID is required. e.g. `wp um security test user=<user_id>`', 'ultimate-member' ) );
			return;
		}
		$args = wp_parse_args(
			$args[0],
			array(
				'user' => null,
			)
		);

		\WP_CLI::success( sprintf( /* translators: Account with user ID %s has been validated */ __( 'Account with user ID %s has been validated. ', 'ultimate-member' ), $args['user'] ) );

		add_filter( 'um_secure_blocked_user_redirect_immediately', '__return_false' );

		$is_secured = UM()->frontend()->secure()->secure_user_capabilities( $args['user'] );
		if ( $is_secured ) {
			\WP_CLI::success( sprintf( /* translators: Account with user ID %s has been validated */ \WP_CLI::colorize( 'Account with user ID %s has been %Gsecured and %Rflagged as suspicious account.' ), $args['user'] ) );
		} else {
			\WP_CLI::success( sprintf( /* translators: Account with user ID %s has been validated */ \WP_CLI::colorize( 'Account with user ID %s is %Yalready %Ysecured. ' ), $args['user'] ) );
		}

	}

	/**
	 * Test Affected Users by Banned Capabilities
	 * Command: wp um security affected users
	 */
	public function test_security_settings_affected_users() {

		$arr_banned_caps = array();
		if ( UM()->options()->get( 'banned_capabilities' ) ) {
			$arr_banned_caps = array_keys( UM()->options()->get( 'banned_capabilities' ) );
		} else {
			$arr_banned_caps = UM()->secure()->banned_admin_capabilities;
		}

		foreach ( $arr_banned_caps as $k => $cap ) {
			$args          = array(
				'capability'   => $cap,
				'role__not_in' => array( 'administrator' ),
			);
			$wp_user_query = new \WP_User_Query( $args );
			$count_users   = $wp_user_query->get_total();
			if ( $count_users <= 0 ) {
				\WP_CLI::success( \WP_CLI::colorize( '`' . $cap . '` is %Gsafe', 'ultimate-member' ) );
			} else {
				\WP_CLI::success( \WP_CLI::colorize( '`%Y' . $cap . '`%N' ) . ' has ' . \WP_CLI::colorize( '%Raffected `' . $count_users . '`%N user accounts. ' ) );
			}
		}
	}

	/**
	 * Total Flagged accounts
	 * Command: wp um security flagged users interval=<today|last_hour>
	 *
	 * @param array $args Command arguments.
	 * @param array $assoc_args Associated arguments.
	 */
	public function test_security_settings_total_flagged_accounts( $args, $assoc_args ) {
		$args = wp_parse_args(
			isset( $args[0] ) ? $args[0] : '',
			array(
				'interval' => '',
			),
		);

		if ( 'today' === $args['interval'] ) {
			$query_args = array(
				'fields'     => 'ID',
				'relation'   => 'AND',
				'meta_query' => array( //phpcs:ignore slow query ok.
					'relation' => 'AND',
					array(
						'key'     => 'um_user_blocked__datetime',
						'value'   => gmdate( 'Y-m-d H:i:s', strtotime( '-1 day' ) ),
						'compare' => '>=',
						'type'    => 'DATE',
					),
					array(
						'key'     => 'um_user_blocked__datetime',
						'value'   => gmdate( 'Y-m-d H:i:s', strtotime( 'now' ) ),
						'compare' => '<=',
						'type'    => 'DATE',
					),
				),
			);

			$users = new \WP_User_Query( $query_args );

			\WP_CLI::success( \WP_CLI::colorize( /* translators: */ sprintf( _n( 'There\s %d account that has been blocked today.', 'There are %d accounts that have been blocked today.', $users->get_total(), 'ultimate-member' ), $users->get_total() ) ) );

		} elseif ( 'last_hour' === $args['interval'] ) {
			$query_args = array(
				'fields'     => 'ID',
				'meta_query' => array( //phpcs:ignore slow query ok.
					'relation' => 'AND',
					array(
						'key'     => 'um_user_blocked__datetime',
						'value'   => gmdate( 'Y-m-d H:i:s', strtotime( '-1 hour' ) ),
						'compare' => '>=',
						'type'    => 'DATETIME',
					),
				),
			);

			$users = new \WP_User_Query( $query_args );

			\WP_CLI::success( \WP_CLI::colorize( /* translators: */ sprintf( _n( 'There\s %d account that has been blocked within the last hour.', 'There are %d accounts that have been blocked within the last hour.', $users->get_total(), 'ultimate-member' ), $users->get_total() ) ) );

		} elseif ( empty( $args['interval'] ) ) {
			$query_args = array(
				'fields'     => 'ID',
				'relation'   => 'AND',
				'meta_query' => array(  //phpcs:ignore slow query ok.
					'relation' => 'AND',
					array(
						'key'     => 'um_user_blocked__datetime',
						'compare' => 'EXISTS',
					),
				),
			);

			$users = new \WP_User_Query( $query_args );
			\WP_CLI::success( \WP_CLI::colorize( /* translators: */ sprintf( _n( 'There\s %d account that has been blocked on your site.', 'There are %d accounts that have been blocked on your site.', $users->get_total(), 'ultimate-member' ), $users->get_total() ) ) );

		}
	}
}
