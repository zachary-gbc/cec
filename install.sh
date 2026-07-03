#!/bin/bash

if [[ ! -f "/var/www/conf/csdb.conf" ]]
then
    git clone --depth=1 https://github.com/zachary-gbc/csdb /home/pi/csdb
    bash /home/pi/csdb/install.sh subinstall
    sleep 5
else
    sudo apt-get update
    sudo apt-get upgrade -y
fi

. /var/www/conf/csdb.conf

mkdir -p /home/pi/log/cec
install_log="/home/pi/log/cec/install.log"
echo "Initiating Install" > $install_log

mkdir -p /home/pi/scripts/cec
cp /home/pi/cec/scripts/ghupdate.sh /home/pi/scripts/cec/ghupdate.sh
mkdir -p /var/www/html/cec/uploads
sudo chown www-data:www-data /var/www/html/cec/uploads

sudo mysql --host="$database_ip" --user="$database_username" --password="$database_password" --database="$database_name" < /home/pi/cec/db.txt

echo "never" > /home/pi/cec_lastupdatecommit
sudo cp -f /home/pi/cec/cec.cron /etc/cron.d/cec
sudo chown root:root /etc/cron.d/cec

echo ""
echo "----------------------"
echo "-- Main Pi IP: $database_ip --"
echo "-- Check Conf if IP Incorrect --"
echo "----------------------"

echo ""
echo "----------------------"
echo "-- Install Complete --"
echo "----------------------"
echo "-- Plase Reboot Now --"
echo "----------------------"
