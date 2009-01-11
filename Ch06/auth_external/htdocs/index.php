<?php

include_once '../lib/client/facebook.php';
include_once '../lib/AppConfig.class.php';
include_once '../lib/SuperStore.class.php';

// Create a new Facebook client object
$facebook = new Facebook(AppConfig::$api_key, AppConfig::$secret);

$this_app = new SuperStore($facebook);

$has_account = $this_app->has_account();

if ($has_account) {
  $current_user = $this_app->account_id();
  $expiration = $this_app->account_expires();
  $has_expired = $this_app->account_has_expired();
}

?>
<html>
  <head>
    <title>SuperStore 2000</title>
  </head>
  <body>
    <div style="padding: 10px; margin: 10px;">
      <h1>Welcome to Super Store 2000</h1>
<?php if ($has_account) { ?>
<?php if ($has_expired) { ?>
      <p>Oh No! Your Facebook session has expired! Please <a href="https://login.facebook.com/login.php?v=1.0&api_key=<?= AppConfig::$api_key ?>">log into Facebook</a> before continuing.</p>
<?php } ?>
      <p>It looks like you have associated your Facebook account with Super Store 2000.</p>
<?php } else { ?>
      <p>Do you use Facebook? If you do, you can tie Super Store 2000 with your Facebook account to update your Facebook profile with cool Super Store 2000 stuff!</p>
      <p><a href="https://login.facebook.com/login.php?v=1.0&api_key=<?= AppConfig::$api_key ?>">Log into Facebook</a>.</p>
<?php } ?>
      <p>
    </div>
  </body>
</html>