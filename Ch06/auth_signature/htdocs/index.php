<?php

include_once '../lib/client/facebook.php';
include_once '../lib/AppConfig.class.php';

// Create a new Facebook client object
$facebook = new Facebook(AppConfig::$api_key, AppConfig::$secret);

// Prevent this page from being viewed outside the context of http://app.facebook.com/appname/
$facebook->require_frame();

// Prevent this page from being viewed without a valid logged in user
// -- NOTE: This does not mean that the logged in user has added the application
$user = $facebook->require_login();

// Require the viewing user to have added the application.
$facebook->require_add();

// Use the get_valid_fb_params to return an array of the fb_sig_* parameters
$app_params = $facebook->get_valid_fb_params($_POST, 48*3600, 'fb_sig');

// Use the generate_sig method to create a signature from the application parameters and the secret
$request_sig = $facebook->generate_sig($app_params, AppConfig::$secret);

$sig_match = $facebook->verify_signature($app_params, $request_sig);

?>
<div style="padding: 10px;">
  <h2>Hello <fb:name firstnameonly="true" uid="<?= $user ?>" useyou="false"/>!</h2>
<?php if ($sig_match) { ?>
  <p>The signature "<?= $request_sig ?>" does match the request parameters.</p>
<?php } else { ?>
  <p>The signature "<?= $request_sig ?>" does not match the request parameters.</p>
<?php } ?>
  <p>When we add foo => bar we get a mismatch.</p>
<?php
$app_params['foo_sig_foo'] = "bar";
$new_sig = $facebook->generate_sig($app_params, AppConfig::$secret);
?>
  <p>The signature "<?= $new_sig ?>" does not match the request parameters.</p>
  <hr />
  <p>When we add fb_sig_foo => bar we get a mismatch.</p>
<?php
$app_params["fb_sig_foo"] = "bar";
$new_sig = $facebook->generate_sig($app_params, AppConfig::$secret);
$sig_match = $facebook->verify_signature($app_params, $new_sig);
?>
<?php if ($sig_match) { ?>
  <p>The signature "<?= $request_sig ?>" does match the request parameters.</p>
<?php } else { ?>
  <p>The signature "<?= $request_sig ?>" does not match the request parameters.</p>
<?php } ?>
</div>