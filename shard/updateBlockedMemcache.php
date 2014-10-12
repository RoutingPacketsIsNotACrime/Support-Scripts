<?php
    /*
    * Copyright (C) 2014 - Gareth Llewellyn
    *
    * This file is part of PacketFlagon - https://PacketFlagon.is
    *
    * This program is free software: you can redistribute it and/or modify it
    * under the terms of the GNU General Public License as published by
    * the Free Software Foundation, either version 3 of the License, or
    * (at your option) any later version.
    *
    * This program is distributed in the hope that it will be useful, but WITHOUT
    * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
    * FOR A PARTICULAR PURPOSE. See the GNU General Public License
    * for more details.
    *
    * You should have received a copy of the GNU General Public License along with
    * this program. If not, see <http://www.gnu.org/licenses/>
    */

        $file_handle = fopen("/tmp/blocked", "r");
        $memcache = memcache_connect('localhost', 11211);
        $MemcacheShard = 0;
        while (!feof($file_handle))
        {
                $line = trim(fgets($file_handle));
                $set = false;
                if(!empty($line) && $line != "" && $line != "\n" && $line != "\r")
                        $set = $memcache->set("$line", "blocked", false, 0);

                if($set)
                {
                        print("SUCCESS - $line\n");
                }
                else
                {
                        print("FAIL - $line\n");
                }

        }
        fclose($file_handle);


        print("Test: ");
        print($memcache->get('zwame.pt') ."\n");

