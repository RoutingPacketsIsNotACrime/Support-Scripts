#!/bin/bash

# Copyright (C) 2014 - Gareth Llewellyn
#
# This file is part of PacketFlagon - https://PacketFlagon.is
#
# This program is free software: you can redistribute it and/or modify it
# under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful, but WITHOUT
# ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
# FOR A PARTICULAR PURPOSE. See the GNU General Public License
# for more details.
#
# You should have received a copy of the GNU General Public License along with
# this program. If not, see <http://www.gnu.org/licenses/>


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
