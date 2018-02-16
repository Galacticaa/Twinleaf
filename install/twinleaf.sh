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

read -p "Enter the domain name you'll access Twinleaf from: " _HOSTNAME
read -p "Enter a name for the admin user: " _USERNAME
echo "Enter password: "
read -s _PASSWORD



header "Configuring Twinleaf user"
echo "Creating user..."
useradd -mNg www-data -G root -s /bin/zsh twinleaf
passwd twinleaf
echo "Adding users to sudoers..."
echo "twinleaf ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/twinleaf
echo "www-data ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/www-data


header "Installing Twinleaf!"
echo "Downloading files..."
cd /home/twinleaf
sudo -Hu twinleaf git clone https://github.com/Galacticaa/Twinleaf.git twinleaf

cd /home/twinleaf/twinleaf
echo "Setting permissions..."
chmod 0440 /etc/sudoers.d/twinleaf
chmod -R g+s . && chmod -R ug+rwx bin storage bootstrap/cache


echo "Creating database..."
read -p "Enter the password for the MySQL root user: " _PASSWORD
echo "CREATE DATABASE twinleaf" | mysql -u root -p$_PASSWORD

echo "Writing database config..."
sudo -Hu twinleaf cp install/.env .env
sudo -Hu twinleaf sed -i 's/_URL_/http:\/\/'$_HOSTNAME'/' .env
sudo -Hu twinleaf sed -i 's/_PASS_/'$_PASSWORD'/' .env

echo "Installing dependencies..."
sudo -Hu twinleaf composer install
echo "Generating unique application key..."
sudo -Hu twinleaf php artisan key:generate
echo "Migrating database..."
sudo -Hu twinleaf php artisan migrate --seed


header "Configuring web server..."
cp install/vhost.conf /etc/nginx/sites-available/twinleaf.conf
sed -i 's/_HOSTNAME_/'$_HOSTNAME'/g' /etc/nginx/sites-available/twinleaf.conf
echo "$_USERNAME:$(openssl passwd -crypt $_PASSWORD)" >> /home/twinleaf/twinleaf/.htpasswd
ln -fs /etc/nginx/sites-available/twinleaf.conf /etc/nginx/sites-enabled/
echo "Applying configuration..."
systemctl restart nginx.service


header "Installing crontab entries..."
sudo -Hu twinleaf crontab -l > /tmp/crontab
echo "* * * * * cd /home/twinleaf/twinleaf && /usr/bin/php artisan schedule:run >> /home/twinleaf/twinleaf/storage/logs/cron.log 2>&1" >> /tmp/crontab
sudo -Hu twinleaf crontab /tmp/crontab
rm /tmp/crontab


header "Configuring Git"
echo "Creating SSH key for Twinleaf..."
sudo -u twinleaf ssh-keygen -t rsa -b 4096 -N '' -f /home/twinleaf/.ssh/id_rsa
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
