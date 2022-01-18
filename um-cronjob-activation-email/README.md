## Ultimate Member - Resend the Account Email Confirmation notification with WP Cronjob

Resends the Account Email Confirmation notification with WP Cronjob when the activation link has expired.

##Filter Hook

Change the WP Cronjob schedule to trigger the resending of email confirmation notification.

-  default: `daily`. Enum: `daily`,`hourly`,`twicedaily`,`weekly`

```
add_filter("um_cron_resend_activation_link_recurrence","um_cron_resend_activation_link_recurrence");
function um_cron_resend_activation_link_recurrence( $recurrence ){

    return "hourly";
}
```

##Notes

-  Ensure that the option `Activation link lifetime` is set in WP Admin > Ultimate Member > Settings > General Users for this plugin to work.

## License

GNU Version 2 or Any Later Version
