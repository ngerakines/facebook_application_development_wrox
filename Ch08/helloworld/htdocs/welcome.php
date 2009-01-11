<?php

include_once '../lib/client/facebook.php';
include_once '../lib/AppConfig.class.php';
include_once '../lib/HelloWorld.class.php';

// Create a new Facebook client object
$facebook = new Facebook(AppConfig::$api_key, AppConfig::$secret);

// Prevent this page from being viewed outside the context of app.facebook.com/appname/
$facebook->require_frame();

// Prevent this page from being viewed without a valid logged in user
// -- NOTE: This does not mean that the logged in user has added the application
$user = $facebook->require_login();

// Require the viewing user to have added the application.
$facebook->require_add();

$app = new HelloWorld($facebook);

$app->first_time($user);

if ($_GET['referralbyuser']) {
  $app->save_referral($user, $_GET['referralbyuser']);
}

?>
<fb:dashboard>
<fb:action href="/nghelloworld/">View My Greetings</fb:action>
<fb:action href="/nghelloworld/wave.php">Send a greeting</fb:action>
</fb:dashboard>

<div style="padding: 10px;">
  <h2>Hello <fb:name firstnameonly="true" uid="<?= $user ?>" useyou="false"/>!</h2>
  <p>Welcome to this application!</p>
</div>