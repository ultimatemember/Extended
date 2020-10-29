<?php
/*
Plugin Name: Ultimate Member - Schedule User Deletions with WP Cronjob
Plugin URI: http://ultimatemember.com/
Description: Deletes users by status after X number of days/months/years
Version: 1.0.0
Author: UM Devs
Author URI: https://umdevs.com
*/

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'um_cron_delete_users_cron', 'um_delete_users_awaiting_email' );
function um_delete_users_awaiting_email(){

    $after_x = apply_filters("um_cron_delete_users_after", "5 days ago midnight");
    $user_status = apply_filters("um_cron_delete_users_status", "awaiting_email_confirmation");

    $args = array(
      'fields'     => 'ID',
      'number'     => -1,
      'date_query' => array(
          array( 'after'  => $after_x, 'inclusive' => true ),
      ),
      'meta_query' => array(
            "relation" => "AND",
            array(
                  "key" => "status",
                  "value" => $user_status,
                  "compare" => "=" 
            )
      )
  );
   
  $users = get_users( $args );

  foreach( $users as $user ){ 
      um_fetch_user( $user->ID );
      UM()->user()->delete();
  }

}

if ( ! wp_next_scheduled( 'um_cron_delete_users_cron' ) ) {
    $recurrence = apply_filters("um_cron_delete_users_recurrence", "daily");
    wp_schedule_event( time(), 'daily', 'um_cron_delete_users_cron' );
}