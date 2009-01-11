#!/usr/bin/php
<?php

if (! $argv[0] || ! $argv[1] || ! $argv[2] || ! $argv[3]) {
    print "./step_two.php <api_key> <app secret> <auth_token>\n";
    exit();
}

$api_key = $argv[1];
$secret = $argv[2];
$auth_token = $argv[3];

include_once 'lib/client/facebook.php';
include_once 'lib/client/facebook_desktop.php';

$client = new FacebookDesktop($api_key, $secret);
$result = $client->do_get_session($auth_token);

print "Be sure to keep the session key and secret in a safe place!!\n\n";
print "Session: " . $result['session_key'] . "\n";
print "User ID: " . $result['uid'] . "\n";
print "Expires: " . $result['expires'] . "\n";
print "Application Secret: " . $secret . "\n";
print "Session Secret: " . $result['secret'] . "\n\n";

print 'Executing query: SELECT concat(name, " is ", relationship_status) FROM user WHERE uid = ' . $result['uid'] ."\n\n";

$resp = $client->api_client->fql_query('SELECT concat(name, " is ", relationship_status) FROM user WHERE uid = ' . $result['uid']);

var_dump($resp);

?>