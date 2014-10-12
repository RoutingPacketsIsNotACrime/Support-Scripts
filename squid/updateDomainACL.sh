#!/bin/bash
# Version 0.1 - When PacketFlagon goes live this will no longer work as you'll need
#               to authenticate to get the ACL list

DATE=$(date +"%m-%d-%y")

wget https://routingpacketsisnotacrime.uk/acl.php -O /etc/squid/whitelist.txt.new

DIFF=$(diff /etc/squid/whitelist.txt /etc/squid/whitelist.txt.new)

if [ "$DIFF" != "" ]
then
        echo "URLs ACL has changed"
        mv /etc/squid/whitelist.txt /etc/squid/whitelist.txt.bak-$DATE
        mv /etc/squid/whitelist.txt.new /etc/squid/whitelist.txt
        /etc/init.d/squid reload

        if [ $? -eq 0 ]
        then
                echo "All Good"
        else
                echo "Reload failed" | mail -s "Squid-1-1 Failed to Reload" security@routingpacketsisnotacrime.uk
        fi
else
        echo "URLs ACL has not changed"
fi
