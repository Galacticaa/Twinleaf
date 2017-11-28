#!/bin/bash

clear && cat <<HEADER

  88888888888            d8b          888                    .d888
      888                Y8P          888                   d88P"
      888                             888                   888
      888  888  888  888 888 88888b.  888  .d88b.   8888b.  888888
      888  888  888  888 888 888 "88b 888 d8P  Y8b     "88b 888
      888  888  888  888 888 888  888 888 88888888 .d888888 888
      888  Y88b 888 d88P 888 888  888 888 Y8b.     888  888 888
      888   "Y8888888P"  888 888  888 888  "Y8888  "Y888888 888

    This script will automatically install everything necessary
    for Twinleaf to run. Lets take some details and get started!

HEADER

function header {
    border=$(printf '#%.0s' $(seq 1 $(expr length "$1" + 8)))
    echo
    echo
    echo ' '$border
    echo ' $   '$1'   #'
    echo ' '$border
    echo
}

timedatectl set-timezone Etc/UTC

read -p "Enter the domain name you'll access Twinleaf from: " _HOSTNAME
read -p "Enter a name for the admin user: " _USERNAME
read -p "Enter password: " _PASSWORD

header "Preparing software repositories..."
apt-get update
add-apt-repository -y ppa:ondrej/php
add-apt-repository -y ppa:certbot/certbot
apt-get update

header "Installing required software..."
apt-get install -y build-essential curl git htop tmux tree unzip wget zsh
apt-get install -y python python-dev python-pip python-software-properties
apt-get install -y nginx nodejs-legacy npm python-certbot-nginx openjdk-8-jre-headless
pip install --upgrade pip

apt-get install -y mysql-client mysql-server php7.1 php7.1-fpm php7.1-mbstring php7.1-mysql php7.1-xml

header "Configuring MySQL..."
mysql_secure_installation
echo "LimitNOFILE=100000" >> /lib/systemd/system/mysql.service
echo "max_connections = 1500" >> /etc/mysql/mysql.conf.d/mysqld.cnf
echo "mysql soft nofile 81920\nmysql hard nofile 99920" >> /etc/security/limits.conf
systemctl daemon-reload
systemctl restart mysql.service

header "Configuring Twinleaf user..."
echo "twinleaf ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/twinleaf
chmod 0440 /etc/sudoers.d/twinleaf
useradd -mNg www-data -G root -s /bin/zsh twinleaf
passwd twinleaf

header "Installing Composer..."
wget -4 https://getcomposer.org/installer && php installer --install-dir=/usr/local/bin --filename=composer

header "Installing Twinleaf..."
cd /home/twinleaf
sudo -Hu twinleaf git clone https://github.com/Galacticaa/Twinleaf.git twinleaf
read -p "Enter the password for the MySQL root user: " _PASSWORD
echo "CREATE DATABASE twinleaf" | mysql -u root -p$_PASSWORD
cd /home/twinleaf/twinleaf
cp install/.env .env
sed -ie 's/_URL_/http:\/\/'$_HOSTNAME'/' .env
sed -ie 's/_PASS_/'$_PASSWORD'/' .env
sudo -Hu twinleaf composer install
sudo -Hu twinleaf php artisan key:generate
sudo -Hu twinleaf php artisan migrate --seed


header "Configuring web server..."
cp install/vhost.conf /etc/nginx/sites-available/twinleaf.conf
sed -ie 's/_HOSTNAME_/'$_HOSTNAME'/g' /etc/nginx/sites-available/twinleaf.conf
echo "$_USERNAME:$(openssl passwd -crypt $_PASSWORD)\n" >> /home/twinleaf/twinleaf/.htpasswd
ln -fs /etc/nginx/sites-available/twinleaf.conf /etc/nginx/sites-enabled/
systemctl restart nginx.service

echo
header "All done!"
