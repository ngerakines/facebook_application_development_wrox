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

// Get the greetings to and from this user
$greetings_from = $app->get_greetings('user_from', $user, 5);
$greetings_to = $app->get_greetings('user_to', $user, 5);

$greetings_from_count = $app->get_greeting_count('user_from', $user);
$greetings_to_count = $app->get_greeting_count('user_to', $user);

?>
<fb:dashboard>
<fb:action href="/nghelloworld/">View My Greetings</fb:action>
<fb:action href="/nghelloworld/wave.php">Send a greeting</fb:action>
</fb:dashboard>

<div style="padding: 10px;">
  <h2>Hello <fb:name firstnameonly="true" uid="<?= $user ?>" useyou="false"/>!</h2>
  <fb:if-is-app-user uid="<?= $user ?>">
<? if ($greetings_to_count) { ?>
<p>You have received <?= $greetings_from_count ?> greetings from your friends.</p>
<ul>
<? foreach ($greetings_to as $greeting) { ?>
  <li><fb:name uid="<?= $greeting['user_from'] ?>" useyou="false" /></li>
<? } ?>
</ul>
<? } else { ?>
<p>You have not received any greetings</p>
<? } ?>
<? if ($greetings_from_count) { ?>
<p>You have sent <?= $greetings_from_count ?> greetings.</p>
<ul>
<? foreach ($greetings_from as $greeting) { ?>
  <li><fb:name uid="<?= $greeting['user_to'] ?>" useyou="false" /></li>
<? } ?>
</ul>
<? } else { ?>
<p>You have not sent any greetings</p>
<? } ?>
  <fb:else>
    <p>You need to <a href="<?= $facebook->get_add_url() ?>">add <?= AppConfig::$app_name ?></a> to use it!</p>
  </fb:else>
</fb:if-is-app-user>
</div>