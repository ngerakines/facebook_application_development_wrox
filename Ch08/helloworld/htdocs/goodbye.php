<?php

include_once '../lib/client/facebook.php';
include_once '../lib/AppConfig.class.php';
include_once '../lib/HelloWorld.class.php';

$facebook = new Facebook(AppConfig::$api_key, AppConfig::$secret);

$app = new HelloWorld($facebook);

$user = $facebook->require_login();

$app->goodbye($user);

?>