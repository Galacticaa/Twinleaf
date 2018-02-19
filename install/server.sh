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

status=1
config="/tmp/.my.cnf"
mysqlRootPass=""

function header {
    border=$(printf '#%.0s' $(seq 1 $(expr length "$1" + 8)))
    echo
    echo
    echo ' '$border
    echo ' #   '$1'   #'
    echo ' '$border
    echo
}

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

header "Server Configuration"

echo
echo
echo "A new user will be created so you don't need to use root."
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

header "Twinleaf Configuration"

read -p "Enter the domain name you'll access Twinleaf from: " twinleafUrl
read -p "Enter a name for the admin user: " twinleafUser
read -sp "Enter password: " twinleafPass

echo
echo
echo "That's it for now. Time to start setting up the server!"
echo


header "Configuring your System"
echo "Setting locales..."
echo "en_GB.UTF-8 UTF-8" > /etc/locale.gen
echo "en_GB ISO-8859-1" >> /etc/locale.gen
locale-gen
echo

echo -n "Setting timezone..."
echo "Etc/UTC" > /etc/timezone
timedatectl set-timezone Etc/UTC && echo " [OK]"

echo -n "Configuring shell..."
wget -qO /tmp/termite.terminfo https://raw.githubusercontent.com/thestinger/termite/master/termite.terminfo
tic -x /tmp/termite.terminfo && echo "[ OK]"


echo -n "Preparing skeleton user..."
mkdir /etc/skel/.ssh
cp /root/.ssh/authorized_keys2 /etc/skel/.ssh/ && echo " [OK]"


header "Installing essential software..."
apt-get update
apt-get install -qq acl build-essential curl git htop tmux tree ufw unzip wget zsh
echo


header "Preparing additional software repositories..."
add-apt-repository -y ppa:ondrej/php
add-apt-repository -y ppa:certbot/certbot
apt-get update
echo


header "Installing NodeJS..."
apt-get install -qq nodejs-legacy npm
echo


header "Installing Python..."
apt-get install -qq python python-dev python-pip python-software-properties
pip install --upgrade pip
pip install virtualenv
echo


header "Installing Java runtime..."
apt-get install -qq openjdk-8-jre-headless
echo


header "Installing MySQL..."
debconf-set-selections <<< "mysql-server mysql-server/root_password password $mysqlRootPass"
debconf-set-selections <<< "mysql-server mysql-server/root_password_again password $mysqlRootPass"
apt-get install -qq mysql-client mysql-server
echo


header "Emulating mysql_secure_installation"
echo -n "Preventing remote root login..."
query "DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1')"
echo -n "Removing anonymous users..."
query "DELETE FROM mysql.user WHERE User=''"
echo -n "Dropping test database..."
query "DROP DATABASE IF EXISTS test"
echo -n "Removing related privileges..."
query "DELETE FROM mysql.db WHERE Db='test' OR Db='test\_%'"
echo -n "Reloading privileges..."
query "FLUSH PRIVILEGES"


header "Configuring MySQL Limits"
echo "Increasing file and connection limits..."
echo "LimitNOFILE=100000" >> /lib/systemd/system/mysql.service
echo "max_connections = 1500" >> /etc/mysql/mysql.conf.d/mysqld.cnf
echo "mysql soft nofile 81920\nmysql hard nofile 99920" >> /etc/security/limits.conf
echo "Applying config..."
systemctl daemon-reload
systemctl restart mysql.service


header "Installing Nginx"
apt-get install -qq nginx python-certbot-nginx
echo


header "Installing PHP"
apt-get install -qq php7.1 php7.1-curl php7.1-fpm php7.1-gd php7.1-mbstring php7.1-mysql php7.1-xml php7.1-zip
echo


header "Installing Composer"
wget -4 https://getcomposer.org/installer && php installer --install-dir=/usr/local/bin --filename=composer
echo


header "Configuring Firewall"
echo -n "Allowing SSH... "
ufw allow OpenSSH
echo -n "Enabling SSH rate limiting..."
ufw limit ssh/tcp
echo -n "Allowing Nginx over HTTP & HTTPS..."
ufw allow Nginx\ Full
echo -n "Allowing Rsync from $userIp..."
ufw allow from $userIp to any port 873
echo -n "Allowing MySQL from $userIp..."
ufw allow from $userIp to any port 3306
echo -n "Enabling the firewall..."
ufw --force enable
ufw status
echo


header "Cloning your Dotfiles"
if [[ -z "$githubUser" ]]; then
    echo "Nothing to do, no Github username set."
else
    git clone https://github.com/$githubUser/dotfiles.git /etc/skel/dotfiles
fi
echo


header "Setting up your account"
echo -n "Creating user '$username'..."
useradd -mg users -G root,systemd-journal,www-data $username && echo " [OK]"
echo -n "Adding you to the sudoers file..."
echo "$username ALL=(ALL) ALL" > /etc/sudoers.d/$username && echo " [OK]"
echo

echo
echo
echo "All done! Proceeding to install Twinleaf..."
echo



header "Configuring Twinleaf user"
echo -n "Creating user..."
useradd -mNg www-data -G root -s /bin/zsh twinleaf && echo " [OK]"

echo -n "Setting the password..."
echo "twinleaf:$twinleafPass" | chpasswd && echo " [OK]"

echo -n "Adding twinleaf to sudoers..."
echo "twinleaf ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/twinleaf && echo " [OK]"

echo -n "Adding twinleaf to sudoers..."
echo "www-data ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/www-data && echo " [OK]"


header "Installing Twinleaf!"
cd /home/twinleaf
echo "Downloading files..."
sudo -Hu twinleaf git clone https://github.com/Galacticaa/Twinleaf.git twinleaf

cd /home/twinleaf/twinleaf
echo "Setting permissions..."
chmod 0440 /etc/sudoers.d/*
chmod -R g+s . && chmod -R ug+rwx bin storage bootstrap/cache
setfacl -Rdm g:www-data:rw /home/twinleaf/twinleaf/storage


echo -n "Creating database..."
query "CREATE DATABASE IF NOT EXISTS twinleaf" && echo " [OK]"
echo -n "Cleaning up..."
rm -f $config && echo " [OK]"

echo "Writing database config..."
sudo -Hu twinleaf cp install/.env .env
sudo -Hu twinleaf sed -i 's/_URL_/http:\/\/'$twinleafUrl'/' .env
sudo -Hu twinleaf sed -i 's/_PASS_/'$mysqlRootPass'/' .env


header "Configuring Twinleaf"
sudo -Hu twinleaf composer install
echo "Generating unique application key..."
sudo -Hu twinleaf php artisan key:generate
echo "Migrating database..."
sudo -Hu twinleaf php artisan migrate --force --seed


header "Configuring web server..."
cp -v install/vhost.conf /etc/nginx/sites-available/twinleaf.conf
sed -i 's/_HOSTNAME_/'$twinleafUrl'/g' /etc/nginx/sites-available/twinleaf.conf
echo -n "Creating web user credentials..."
echo "$twinleafUser:$(openssl passwd -crypt $twinleafPass)" >> /home/twinleaf/twinleaf/.htpasswd && echo " [OK]"
echo -n "Enabling virtual host..."
ln -fs /etc/nginx/sites-available/twinleaf.conf /etc/nginx/sites-enabled/ && echo " [OK]"
echo -n "Applying configuration..."
systemctl restart nginx.service && echo " [OK]"


header "Installing crontab entries..."
crontab -u twinleaf -l > /tmp/crontab
echo "* * * * * cd /home/twinleaf/twinleaf && /usr/bin/php artisan schedule:run >> /home/twinleaf/twinleaf/storage/logs/cron.log 2>&1" >> /tmp/crontab
crontab -u twinleaf /tmp/crontab
crontab -u twinleaf -l
rm /tmp/crontab


header "Configuring Git"
echo "Creating SSH key for Twinleaf..."
sudo -Hu twinleaf ssh-keygen -t rsa -b 4096 -N '' -f /home/twinleaf/.ssh/id_rsa
echo "Saving host key for Bitbucket..."
ssh-keyscan -t rsa bitbucket.org | sudo -Hu twinleaf tee -a /home/twinleaf/.ssh/known_hosts
echo "Saving host key for Github..."
ssh-keyscan -t rsa github.com | sudo -Hu twinleaf tee -a /home/twinleaf/.ssh/known_hosts


echo
header "All done!"
echo "The public key for Twinleaf is printed below."
echo "You'll need to add this to Github or Bitbucket"
echo "if your map is hosted as a private repository."
echo
cat /home/twinleaf/.ssh/id_rsa.pub
echo
echo
