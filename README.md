# Calendar Entry Checker
Used to Identify New Calendar Entries

Steps for initialization of a pi:

1. Follow steps to install new operating system and login using ssh or keyboard
    - Must set username to *pi*
1. sudo raspi-config (if not setup when installing OS)
    - System Options
        - Update Hostname (recommended to know which device is which but not required)
        - Wireless LAN
        - Update password (recommended but not required)
        - Admin Password (set to No)
    - Localisation Options
        - Timezone
    - Reboot
1. sudo apt-get update
1. sudo apt-get install git -y
1. git clone --depth=1 https://github.com/zachary-gbc/cec /home/pi/cec
1. bash /home/pi/cec/install.sh
    - Follow prompts on screen
