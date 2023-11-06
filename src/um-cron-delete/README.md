## Ultimate Member - Schedule User Deletions with WP Cronjob

Deletes users by status after X number of days/months/years

Available filter hooks:

-  `um_cron_delete_users_after` - default: `5 days ago midnight`. See [Date Query](https://developer.wordpress.org/reference/classes/wp_query/#date-parameters)
-  `um_cron_delete_users_status` - default: `awaiting_email_confirmation`. Enum: `approved`, `awaiting_admin_review`, `awaiting_email_confirmation`, `inactive`, `rejected`.
-  `um_cron_delete_users_recurrence` - default: `daily`. Enum: `daily`,`hourly`,`twicedaily`,`weekly`

## License

GNU Version 2 or Any Later Version
