#!/bin/bash

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

status=1
while [ $status -eq 1 ]; do
    set_mysql_pass

    status=$?
done
echo

echo
echo
echo "Thanks! Let's start configuring some things."
echo


echo
echo
echo -n "Setting locales..."
echo "en_GB.UTF-8 UTF-8" > /etc/locale.gen
echo "en_GB ISO-8859-1" >> /etc/locale.gen
locale-gen && echo " [OK]"


echo
echo
echo -n "Setting timezone..."
echo "Etc/UTC" > /etc/timezone
timedatectl set-timezone Etc/UTC && echo " [OK]"


echo
echo
echo -n "Preparing skeleton user..."
mkdir /etc/skel/.ssh
cp /root/.ssh/authorized_keys2 /etc/skel/.ssh/ && echo " [OK]"


echo
echo
echo "Installing essential software..."
. install/software/core.sh
. install/software/rocketmap.sh
. install/software/kinan.sh
. install/software/web.sh
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
echo "Adding you to the sudoers file..."
echo "$username ALL_(ALL) ALL" > /etc/sudoers.d/$username && echo " [OK]"
echo
