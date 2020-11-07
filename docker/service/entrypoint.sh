#!/bin/sh

service cron start

# service php7.4-fpm start does not pass env variables to process.
/etc/init.d/php7.4-fpm start

# Hand off to the CMD
exec "$@"
