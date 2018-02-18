#!/bin/bash

apt-get install -qq mysql-client mysql-server
apt-get install -qq nginx python-certbot-nginx
apt-get install -qq php7.1 php7.1-curl php7.1-fpm php7.1-gd php7.1-mbstring php7.1-mysql php7.1-xml php7.1-zip

wget -4 https://getcomposer.org/installer && php installer --install-dir=/usr/local/bin --filename=composer

config=".my.cnf"
echo "[mysql]" > $config
echo "user=root" >> $config
echo "password='$mysqlRootPass'" >> $config

query() {
    mysql --defaults-file="$config" -e "$1" && echo " [OK]"
}

# Emulating mysql_secure_installation
echo -n "Setting root password..."
query "UPDATE mysql.user SET Password=PASSWORD('$mysqlRootPass') WHERE User='root'"
echo -n "Preventing remote root login..."
query "DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1')"
echo -n "Removing anonymous users..."
query "DELETE FROM mysql.user WHERE USER=''"
echo -n "Dropping test database..."
query "DROP DATABASE test"
echo -n "Removing related privileges..."
query "DELETE FROM mysql.db WHERE Db='test' OR Db='test\_%'"
echo -n "Reloading privileges..."
query "FLUSH PRIVILEGES"
echo -n "Cleaning up..."
rm -f $config && echo " [OK]"

# Configure some limits
echo "Increasing file and connection limits..."
echo "LimitNOFILE=100000" >> /lib/systemd/system/mysql.service
echo "max_connections = 1500" >> /etc/mysql/mysql.conf.d/mysqld.cnf
echo "mysql soft nofile 81920\nmysql hard nofile 99920" >> /etc/security/limits.conf
echo "Applying config..."
systemctl daemon-reload
systemctl restart mysql.service
