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

// Create a var to represent the target user, default it to null
$target_user = null;
if (isset($_POST['friend_sel'])) {
  // If the friend_sel var is set in the POST vars, typecast it to an
  // integer and set the target_user
  $target_user = (int) $_POST['friend_sel'];
}

// By default we show the form, and the action_wave and wave_success are
// set to false.
$wave_success = false;
$action_wave = false;
$show_form = true;
if ($target_user) {
  $action_wave = true;
  $show_form = false;
  // The target_user is set, send them a greeting
  $wave_success = $app->wave_hello($user, $target_user);
}

?>

<fb:dashboard>
<fb:action href="/nghelloworld/">View My Greetings</fb:action>
<fb:action href="/nghelloworld/wave.php">Send a greeting</fb:action>
</fb:dashboard>

<div style="padding: 10px;">
  <h2>Hello <fb:name firstnameonly="true" uid="<?= $user ?>" useyou="false"/>!</h2><br/>
  <fb:if-is-app-user uid="<?= $user ?>">
<?php
if ($action_wave) {
  // TODO: Add error handling, display an error or warning message if
  // a greeting was not sent succesfully.
  if ($wave_success) {
?>
	<fb:explanation>
	     <fb:message><fb:name uid="<?= $target_user ?>" /></fb:message>
	     You have sent a greeting to <fb:name uid="<?= $target_user ?>" />.
	</fb:explanation>
<?php
  }
}
?>
<?php
if ($show_form) {
?>
    <p>Give someone a smile, send them a greeting. To send someone a greeting start typing in their name and click on the Send button.</p>
    <fb:editor action="" labelwidth="100">
      <fb:editor-custom label="Tell us who:">
        <fb:friend-selector name="uid" idname="friend_sel" />
      </fb:editor-custom>
      <fb:editor-buttonset>
        <fb:editor-button value="Send"/>
        <fb:editor-cancel />
      </fb:editor-buttonset>
    </fb:editor>
<?php
}
?>
  <fb:else>
    <p>You need to <a href="<?= $facebook->get_add_url() ?>">add <?= AppConfig::$app_name ?></a> to use it!</p>
  </fb:else>
</fb:if-is-app-user>
</div>
