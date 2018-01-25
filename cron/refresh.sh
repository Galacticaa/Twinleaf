#!/bin/bash

# This script will search for scans (with accounts to write) then
# attempt to update said account files and restart the scan after

echo "-----------------------------------------------------------"
echo -n "Account Refresh - running at "
echo $(date)
echo "-----------------------------------------------------------"

cd /home/twinleaf/twinleaf

if [ ! -f "storage/restart.txt" ]; then
    echo "Nothing to restart!"
    echo
    echo
    exit
fi

while read -ra line; do
    slug=${line[0]}
    code=${line[1]}
    pid=$(ps axf | grep "runserver.py" | grep "$slug" | awk '{ print $1 }')

    echo "Restarting $slug!"

    if [ ! -z "$pid" ]; then
        echo -n "Stopping $pid... " && kill -15 $pid && echo "[DONE]"
    fi

    sleep 3

    echo -n "Starting... " && cd storage/maps/rocketmap &&
        sudo -Hu twinleaf tmux new-session -s "$code" -d ./bin/python runserver.py -cf "config/$slug.ini" &&
        echo "[DONE]" && cd -
    echo

    php artisan log:restart "$slug"

done < "storage/restart.txt"

echo -n "Cleaning up restart list... "
rm storage/restart.txt

echo "[DONE]" && echo && echo
