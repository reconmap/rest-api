#!/bin/sh

printenv | grep "REDIS_" > /home/reconmapper/crontab.env
service cron start

# 'service php-fpm start' does not pass env variables to process.
/etc/init.d/php8.3-fpm start

# Hand off to the CMD
exec "$@"
