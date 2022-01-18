<?php
/*
Plugin Name: Ultimate Member - Resend the Account Email Confirmation notification with WP Cronjob
Plugin URI: https://www.ultimatemember/com
Description: Resends the Account Email Confirmation notification with WP Cronjob when the activation link has expired.
Version: 1.0.0
Author: Ultimate Member Ltd.
Author URI: https://www.ultimatemember.com/
Text Domain: um-cronjob-activation-email
*/

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

define( 'UM_CRONJOB_ACCOUNT_ACTIVATION_EMAIL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'UM_CRONJOB_ACCOUNT_ACTIVATION_EMAIL_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Resend email confirmation link to those users with expired secret hash
 */
function um_cron_resend_activation_notify() {

    $args = array(
        'fields'     => 'ID',
        'number'     => -1,
        'meta_query' => array(
            'relation' => 'OR',
            array(
                'key'       => 'account_secret_hash_expiry',
                'value'     => time(),
                'compare'   => '<=',
            )
        )
    );
   

    $users = get_users( $args );
 
    foreach( $users as $user_id ) { 
        
        um_fetch_user( $user_id );

        $status = um_user( 'account_status' );
        if( $status == 'awaiting_email_confirmation' ) {
            UM()->user()->email_pending();
            um_reset_user();
        }
    }
}
add_action( 'um_cron_resend_activation_link', 'um_cron_resend_activation_notify' );


if ( ! wp_next_scheduled( 'um_cron_resend_activation_link' ) ) {
    $recurrence = apply_filters( 'um_cron_resend_activation_link_recurrence', 'hourly' );
    wp_schedule_event( time(), $recurrence, 'um_cron_resend_activation_link' );
}