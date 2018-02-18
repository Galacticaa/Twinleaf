#!/bin/bash

status=1
config=".my.cnf"
mysqlRootPass=""

set_mysql_pass() {
    read -sp "New password: " password
    echo
    read -sp "Confirm new password: " passconf
    echo

    ret=1
    if [ "$password" != "$passconf" ]; then
        echo "Sorry, passwords do not match."
    elif [ "$password" = "" ]; then
        echo "Sorry, you can't use an empty password here."
    else
        mysqlRootPass=$password; ret=0
    fi

    echo; return $ret
}

query() {
    mysql --defaults-file="$config" -e "$1" && echo " [OK]"
}

echo
echo
echo "During install, a new user will be created for you."
read -p "Pick a username: " username
echo

echo
echo
echo "If you have a dotfiles repository on Github, it can"
echo "be automatically cloned to the skeleton user."
read -p "Enter your Github username: " githubUser
echo

echo
echo
echo "It's never a good idea to leave MySQL without a root"
echo "password. Choose one now and we'll set it for you."
while [ $status -eq 1 ]; do
    set_mysql_pass
    status=$?
done
echo "[mysql]" > $config
echo "user=root" >> $config
echo "password='$mysqlRootPass'" >> $config
echo

echo
echo
echo "MySQL and Rsync will be restricted by firewall."
read -p "Enter your IP Address: " userIp
echo

echo
echo
echo "Time to start setting up the server!"
echo


echo
echo
echo -n "Setting locales..."
echo "en_GB.UTF-8 UTF-8" > /etc/locale.gen
echo "en_GB ISO-8859-1" >> /etc/locale.gen
locale-gen && echo " [OK]"
echo


echo
echo
echo -n "Setting timezone..."
echo "Etc/UTC" > /etc/timezone
timedatectl set-timezone Etc/UTC && echo " [OK]"
echo


echo
echo
echo -n "Preparing skeleton user..."
mkdir /etc/skel/.ssh
cp /root/.ssh/authorized_keys2 /etc/skel/.ssh/ && echo " [OK]"
echo


echo
echo
echo "Installing essential software..."
apt-get update
apt-get install -qq build-essential curl git htop tmux tree ufw unzip wget zsh
echo

echo
echo
echo "Preparing additional software repositories..."
add-apt-repository -y ppa:ondrej/php
add-apt-repository -y ppa:certbot/certbot
apt-get update
echo

echo
echo
echo "Installing NodeJS..."
apt-get install -qq nodejs-legacy npm
echo

echo
echo
echo "Installing Python..."
apt-get install -qq python python-dev python-pip python-software-properties
pip install --upgrade pip
pip install virtualenv
echo

echo
echo
echo "Installing Java runtime..."
apt-get install -qq openjdk-8-jre-headless
echo

echo
echo
echo "Installing MySQL..."
debconf-set-selections <<< "mysql-server mysql-server/root_password password $mysqlRootPass"
debconf-set-selections <<< "mysql-server mysql-server/root_password_again password $mysqlRootPass"
apt-get install -qq mysql-client mysql-server
echo

# Emulating mysql_secure_installation
echo -n "Preventing remote root login..."
query "DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1')"
echo -n "Removing anonymous users..."
query "DELETE FROM mysql.user WHERE User=''"
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

echo
echo
echo "Installing Nginx..."
apt-get install -qq nginx python-certbot-nginx
echo

echo
echo
echo "Installing PHP..."
apt-get install -qq php7.1 php7.1-curl php7.1-fpm php7.1-gd php7.1-mbstring php7.1-mysql php7.1-xml php7.1-zip
echo

echo
echo
echo "Installing Composer..."
wget -4 https://getcomposer.org/installer && php installer --install-dir=/usr/local/bin --filename=composer
echo


echo
echo
echo "Configuring Firewall..."
ufw allow OpenSSH
ufw limit ssh/tcp
ufw allow Nginx\ Full
ufw allow from $userIp to any port 873  # rsync
ufw allow from $userIp to any port 3306 # mysql
ufw --force enable
ufw status
echo


echo
echo
if [[ -z "$githubUser" ]]; then
    echo "Skipping dotfiles, no Github username set."
else
    echo "Cloning your dotfiles from Github..."
    git clone https://github.com/$githubUser/dotfiles.git /etc/skel/dotfiles
fi
echo


echo
echo
echo -n "Creating your personal non-root user '$username'..."
useradd -mg users -G root,systemd-journal,www-data $username && echo " [OK]"
echo -n "Adding you to the sudoers file..."
echo "$username ALL_(ALL) ALL" > /etc/sudoers.d/$username && echo " [OK]"
echo

echo
echo
echo "All done!"
echo

echo
echo
read -n1 -srp "Press any key to install Twinleaf..."
echo

wget -qO /tmp/twinleaf-install https://raw.githubusercontent.com/Galacticaa/Twinleaf/master/install/twinleaf.sh
. /tmp/twinleaf-install
