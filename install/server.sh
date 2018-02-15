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
echo -n "Creating your personal non-root user '$username'..."
useradd -mg users -G root,systemd-journal,www-data $username && echo " [OK]"
echo "Adding you to the sudoers file..."
echo "$username ALL_(ALL) ALL" > /etc/sudoers.d/$username && echo " [OK]"


echo
echo
echo "Installing essential software..."
apt-get update
apt-get install -qq build-essential curl git htop tmux tree ufw unzip wget zsh


echo
echo
if [[ -z "$githubUser" ]]; then
    echo "Skipping dotfiles, no Github username set."
else
    echo "Clone the dotfiles to user's homedir..."
    git clone https://github.com/$githubUser/dotfiles.git /etc/skel/dotfiles
fi
echo
