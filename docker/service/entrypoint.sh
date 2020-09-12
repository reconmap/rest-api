#!/bin/sh

service cron start
service php7.4-fpm start

# Hand off to the CMD
exec "$@"
