<?php

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

