#!/usr/bin/env bash

here=$(readlink -f $(dirname "$0"))
cd "$here"
/usr/bin/php cron.php 1>> >(ts >> cronnit.log) 2>> >(ts >> cronnit.log.err)
