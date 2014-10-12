<?php
    /*
    Blocked.org.uk API Credentials
    - Used for submitting a URL to be tested
    - Used for checking on the block status of a URL
    */
    $ORG_HMAC = '';
    $ORG_EMAIL = '';
    $ORG_BASE = 'https://api.blocked.org.uk/1.2/';

    /*
    Gandi.net API Credentials
    - Used for registering domains
    */
    //Production
    $GANDI_API_KEY = '';
    $GANDI_BASE = 'https://rpc.gandi.net/xmlrpc/';
    $GANDI_HANDLE = 'XXXXXX-GANDI';

    //OT&E 
    //$GANDI_API_KEY = '';
    //$GANDI_BASE = 'https://rpc.ote.gandi.net/xmlrpc/';

    /*
    Digital Ocean API Credentials
    - Used for creating VMs to host the proxy shard
    */
    $DIGITALOCEAN_CLIENTID = '';
    $DIGITALOCEAN_APIKEY = '';
    $DIGITALOCEAN_SSHKEY = 000000;
   
    /*
    Twitter API & OAUTH credentials
    - Used to communicate the new proxy shard
    */
    $TWITTER_APIKEY = '';
    $TWITTER_APISECRET = '';
    $TWITTER_OAUTHTOKEN = '';
    $TWITTER_ACCESSTOKEN = '';
    include(dirname(__FILE__).'/twitteroauth/twitteroauth.php');

    //-------------------------------------------------------------------------------------------------
    //Start the deadhand process
    $memcache = new Memcache;
    $memcache->addServer('127.0.0.1', 11211);

    print('__________                __           __ ___________.__                                
\______   \_____    ____ |  | __ _____/  |\_   _____/|  | _____     ____   ____   ____  
 |     ___/\__  \ _/ ___\|  |/ // __ \   __\    __)  |  | \__  \   / ___\ /  _ \ /    \ 
 |    |     / __ \\  \___|    <\  ___/|  | |     \   |  |__/ __ \_/ /_/  >  <_> )   |  \
 |____|    (____  /\___  >__|_ \\___  >__| \___  /   |____(____  /\___  / \____/|___|  /
                \/     \/     \/    \/         \/              \//_____/             \/ 
________                     .______ ___                    .___                        
\______ \   ____ _____     __| _/   |   \_____    ____    __| _/                        
 |    |  \_/ __ \\__  \   / __ /    ~    \__  \  /    \  / __ |                         
 |    `   \  ___/ / __ \_/ /_/ \    Y    // __ \|   |  \/ /_/ |                         
/_______  /\___  >____  /\____ |\___|_  /(____  /___|  /\____ |                         
        \/     \/     \/      \/      \/      \/     \/      \/');
    print("\n\n\n");

    //Uncomment to get your ORG credentials
    /*$payload = array('password' => md5(date('his')),'email' => $ORG_EMAIL);
    //$payload['signature'] = ORGsign($ORG_HMAC, $payload, array("url"));

    $fields_string = "";
    foreach($payload as $key=>$value)
    {
        $fields_string .= $key.'='.$value.'&';
    }
    rtrim($fields_string, '&');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $ORG_BASE.'register/user');
    curl_setopt($ch, CURLOPT_HEADER, TRUE);
    curl_setopt($ch, CURLOPT_POST, count($payload));
    curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $data = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    //What did we get
    print_r($data);

    return;*/



    //Get the last URL we registered
    $URL = $memcache->get('deadhand-last-reg');
    //$URL = 'routingpacketsisnotacrime.uk';

    $payload = array('url' => $URL,'email' => $ORG_EMAIL);
    $payload['signature'] = ORGsign($ORG_HMAC, $payload, array("url"));

    //Create the POST payload
    $fields_string = "";
    foreach($payload as $key=>$value)
    {
        $fields_string .= $key.'='.$value.'&';
    }
    rtrim($fields_string, '&');
 
    //Perform the CURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $ORG_BASE.'submit/url');
    curl_setopt($ch, CURLOPT_HEADER, TRUE);
    curl_setopt($ch, CURLOPT_POST, count($payload));
    curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $data = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
 

    //What did we get
    print_r($data);


    //Let's wait for the probes to do their work
    sleep(30);

    //Check the status
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $ORG_BASE.'status/url?'.$fields_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $data = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $json = json_decode($data,true);

    $blocked = false;
    foreach($json['results'] as $result)
    {
        if($result['status'] == "blocked")
        {
            print($result['network_name'] . " : BLOCKED - Activating Deadhand\n");
            $blocked = true;
            break;
        }
        else
        {
            print($result['network_name'] . " : OK\n");
        }
    }

    if($blocked === false)
    {
        print("Nothing more to do exiting");
        return;
    }

    //Time to activate the deadhand
   
    require_once 'XML/RPC2/Client.php';

    $domainSuggestFile = "/root/deadhand/suggest.txt";
    $fh = fopen($domainSuggestFile, 'r');
    $domains = fread($fh, filesize($domainSuggestFile));
    fclose($fh);
    $domains = unserialize($domains);
    if(count($domains) == 0)
    {
        print("We're out of domains to register, exiting\n");
        die();
    }

    $domain = $domains[0];
    print("Got $domain\n");
    unset($domains[0]);
    sort($domains);
    $fh = fopen($domainSuggestFile, 'w');
    $serialized = serialize($domains);
    fwrite($fh, $serialized);
    fclose($fh);

    $domain_api = XML_RPC2_Client::create($GANDI_BASE,array( 'prefix' => 'domain.' ));
    $result = $domain_api->available($GANDI_API_KEY, array($domain));

    print("Initial Result:\n");
    print_r($result); 

    while ( $result[strtolower($domain)] == 'pending')
    {
        print("Waiting......\n");
        usleep(700000);
        $result = $domain_api->available($GANDI_API_KEY, array($domain));
    }
 
    print_r($result);

    if($result[strtolower($domain)] == 'available')
    {
        $domain_spec = array(
            'owner' => $GANDI_HANDLE,
            'admin' => $GANDI_HANDLE,
            'bill' => $GANDI_HANDLE,
            'tech' => $GANDI_HANDLE,
            'nameservers' => array('a.dns.gandi-ote.net', 'b.dns.gandi-ote.net',
                           'c.dns.gandi-ote.net'),
            'duration' => 1);
        $op = $domain_api->__call('create', array($GANDI_API_KEY, $domain,$domain_spec));
        print("Domain Registered: $op");
    }
    else
    {
        //Remove the URL from the list and wait for the next dead hand period call
        print("That URL cannot be registered, lets bide our time.....");
        return;
    }

    //Time to spin up the VM that will host this domain

    //Size ID 66        = 512Mb
    //Region ID 7       = London
    //Image ID 6372108  = CentOS 6.5 x64
    $dropletOptions = array('client_id' => $DIGITALOCEAN_CLIENTID, 'api_key' => $DIGITALOCEAN_APIKEY, 'name' => $domain,'size_id' => 66, 'image_id' => 6372108, 'region_id' => 7,'ssh_key_ids' => $DIGITALOCEAN_SSHKEY);

    $fields_string = "";
    foreach($dropletOptions as $key=>$value)
    {
        $fields_string .= $key.'='.$value.'&';
    }
    rtrim($fields_string, '&');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.digitalocean.com/v1/droplets/new?'.$fields_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $data = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $DOjson = json_decode($data,true);
    print_r($DOjson);
    
    $DOIP = "";

    $DOjson['status'] = 'OK';
    $DOjson['droplet']['id'] = 2779505;

    if($DOjson['status'] == 'OK')
    {
        $dropletOptions = array('client_id' => $DIGITALOCEAN_CLIENTID, 'api_key' => $DIGITALOCEAN_APIKEY);

        $fields_string = "";
        foreach($dropletOptions as $key=>$value)
        {
            $fields_string .= $key.'='.$value.'&';
        }
        rtrim($fields_string, '&');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.digitalocean.com/v1/droplets/' .$DOjson['droplet']['id']. '?'.$fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $DOjson = json_decode($data,true);
        print_r($DOjson);

        $DOIP = $DOjson['droplet']['ip_address'];

        while(empty($DOIP))
        {
            print("Waiting......\n");
            usleep(700000);        
            $data = curl_exec($ch);
            $DOjson = json_decode($data,true);
        }

        curl_close($ch);
    }
    else
    {
        //Send a warning (but who will read it - this is a dead hand system :( )
        print("There was an error starting that VM");
        return;
    }


    //Bootstrap
    $JSON = addslashes(json_encode(array('packetflagon' => array('fqdn' => $domain, 'contact' => $PACKETFLAGON_CONTACT,'apikey' => $PACKETFLAGON_APIKEY))));
    exec("knife bootstrap $DOIP --ssh-user root -i /tmp/key.rsa -c /tmp/knife.rb -j \"$JSON\" --node-name $domain --run-list 'role[packetflagon_frontend]' --bootstrap-version 11.12.8",$Output,$exitcode);

    print_r($Output);


    //Tell the world we're ready to rock
    $connection = new TwitterOAuth($TWITTER_APIKEY,
                                   $TWITTER_APISECRET,
                                   $TWITTER_OAUTHTOKEN,
                                   $TWITTER_ACCESSTOKEN);

    $connection->useragent = "RoutingPackets Dead Hand";
    $TweetUpdate = "DeadHand Activated.\nhttp://$domain is coming online.\n#PacketFlagon";
    $Result = $connection->post('statuses/update', array('status' => $TweetUpdate));   

    print('_________                       .__          __          
\_   ___ \  ____   _____ ______ |  |   _____/  |_  ____  
/    \  \/ /  _ \ /     \\____ \|  | _/ __ \   __\/ __ \ 
\     \___(  <_> )  Y Y  \  |_> >  |_\  ___/|  | \  ___/ 
 \______  /\____/|__|_|  /   __/|____/\___  >__|  \___  >
        \/             \/|__|             \/          \/ ');
 

    //Functions-----------------------------------------------------

    function createORGSignatureHash($message, $secret) 
    {
        /* Use hmac functions to return signature for message string */
        return hash_hmac('sha512', $message, $secret);
    }

    function ORGsign($secret, $data, $keys) 
    {
        /* creates a list of values from $data, using $keys as the ordered
        list of keys. Signs the resulting list using $secret */
        $items = array();
        foreach($keys as $k) 
        {
            $items[] = $data[$k];
        }
        $signdata = implode(":",$items);
        return createORGSignatureHash($signdata, $secret);
    }
