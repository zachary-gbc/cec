#!/bin/bash

sleep 62

. /var/www/conf/csdb.conf
lastupdate=$(</home/pi/cec_lastupdatecommit)
mac=$(cat /sys/class/net/wlan0/address | sed 's/://g')
log=$(date -I)
datetime=$(date '+%Y-%m-%d %H:%M:%S');
echo "MESSAGE $datetime: Starting ghupdate" >> /home/pi/log/cec/$log.log

sudo rm -r -f /home/pi/cec
git clone --depth=1 https://github.com/zachary-gbc/cec /home/pi/cec
cd /home/pi/cec
lastcommit=$(git log --pretty=format:"%H")

if [[ $lastcommit != $lastupdate ]]
then
    find . -name '*DS_Store*' -delete
    mv /home/pi/cec/scripts/ghupdate.sh /home/pi/cec-ghupdate.sh
    ( sleep 60; mv /home/pi/cec-ghupdate.sh /home/pi/scripts/cec/ghupdate.sh ) & 

# Scripts
sudo mv -f /home/pi/cec/scripts/* /home/pi/scripts/cec/

# Crons
sudo mv -f /home/pi/cec/cec.cron /etc/cron.d/cec
sudo chown root:root /etc/cron.d/cec
sudo chmod 600 /etc/cron.d/cec

# Website
sudo rsync -avu "/home/pi/cec/website/" "/var/www/html/cec"

echo $lastcommit > /home/pi/cec_lastupdatecommit
fi

sudo rm -r -f /home/pi/cec
