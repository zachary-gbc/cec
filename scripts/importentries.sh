#!/bin/bash

log=$(date -I)
datetime=$(date '+%Y-%m-%d %H:%M:%S');
echo "MESSAGE $datetime: Starting importentires" >> /home/pi/log/cec/$log.log

output=$(curl http://localhost/cec/importentries.php)
echo $output >> /home/pi/log/cec/$log.log