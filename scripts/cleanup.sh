#!/bin/bash

log=$(date -I)
datetime=$(date '+%Y-%m-%d %H:%M:%S');
echo "MESSAGE $datetime: Starting cleanup" >> /home/pi/log/cec/$log.log

# Delete old log files
find /home/pi/log/cec -mtime +30 -type f -delete
