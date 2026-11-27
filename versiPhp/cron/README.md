# Bookify scheduled jobs

On Windows, configure Task Scheduler to run `expire_member_requests.php` every 5 minutes and `send_fine_reminders.php` daily. Use the PHP CLI executable from Laragon and pass the full script path as its argument.

Weekly fine reminder emails use PHP `mail()`. Configure a working SMTP/sendmail transport in the CLI PHP configuration before enabling the reminder task. Optionally set `BOOKIFY_MAIL_FROM` to the sender address. Reminders stop when the fine is paid or 60 days have passed.
