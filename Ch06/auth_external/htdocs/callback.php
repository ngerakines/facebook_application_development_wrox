<?php

include_once '../lib/client/facebook.php';
include_once '../lib/AppConfig.class.php';
include_once '../lib/SuperStore.class.php';

// Create a new Facebook client object
$facebook = new Facebook(AppConfig::$api_key, AppConfig::$secret);

$success = false;

$this_app = new SuperStore($facebook);

$has_account = $this_app->has_account();

if (! $current_user) {
  $success = $this_app->tie_account();
}

$current_user = $this_app->account_id();
$expiration = $this_app->account_expires();

?>
<html>
  <head>
    <title>SuperStore 2000</title>
  </head>
  <body>
    <div style="padding: 10px; margin: 10px;">
      <h1>Welcome to Super Store 2000</h1>
<?php if ($success) { ?>
  <p>You have associated your Facebook account with Super Store 2000. <a href="https://login.facebook.com/login.php?v=1.0&api_key=<?= AppConfig::$api_key ?>">Go to your Facebook profille</a>.</p>
<?php
} else {
  if ($has_account) { // Success is false, there was already an account
?>
  <p>You have already tied your Super Store 2000 and Facebook accounts. <a href="#">Go to your Facebook profille</a>.</p>
<?php } else { ?>
  <p>There was an error. Your Super Store 2000 and Facebook accounts were not tied.</p>
<?php } } ?>
      <p>
    </div>
  </body>
</html>