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


# This file is only updated once a night, please only cron it once a 
# day and in the early hours of the morning

#Get the data file from ORG
wget https://api.blocked.org.uk/data/export.csv.gz -O /tmp/export.csv.gz

#Put it somewhere
gunzip /tmp/export.csv.gz

#UNIX tools are awesome
cat export.csv | grep blocked | cut -d "," -f 1 | sort | uniq > /tmp/blocked

#Import the new details to memcache
php /tmp/blockedBlockedMemcache.php
