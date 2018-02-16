#!/bin/bash

apt-get install -qq mysql-client mysql-server
apt-get install -qq nginx python-certbot-nginx
apt-get install -qq php7.1 php7.1-curl php7.1-fpm php7.1-gd php7.1-mbstring php7.1-mysql php7.1-xml php7.1-zip

wget -4 https://getcomposer.org/installer && php installer --install-dir=/usr/local/bin --filename=composer

mysql_secure_installation
echo "Increasing file and connection limits..."
echo "LimitNOFILE=100000" >> /lib/systemd/system/mysql.service
echo "max_connections = 1500" >> /etc/mysql/mysql.conf.d/mysqld.cnf
echo "mysql soft nofile 81920\nmysql hard nofile 99920" >> /etc/security/limits.conf
echo "Applying config..."
systemctl daemon-reload
systemctl restart mysql.service
