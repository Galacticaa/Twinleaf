#!/bin/bash

apt-get update && apt-get install -qq build-essential curl git htop tmux tree ufw unzip wget zsh

add-apt-repository -y ppa:ondrej/php
add-apt-repository -y ppa:certbot/certbot
apt-get update
