#!/bin/bash
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
