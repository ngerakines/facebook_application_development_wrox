#!/usr/bin/php
<?php

if (! $argv[0] || ! $argv[1] || ! $argv[2]) {
    print "./step_one.php <api_key> <secret>\n";
    exit();
}

$api_key = $argv[1];
$secret = $argv[2];

include_once 'lib/client/facebook.php';

$client = new Facebook($api_key, $secret);
$result = $client->api_client->auth_createToken();

print "Go to:\n    http://www.facebook.com/login.php?api_key={$api_key}&v=1.0&auth_token={$result}\n";
print "Then execute:\n    ./step_two.php {$argv[1]} {$argv[2]} {$result}\n";

?>