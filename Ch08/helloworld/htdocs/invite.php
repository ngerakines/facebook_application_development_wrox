<?php

include_once '../lib/client/facebook.php';
include_once '../lib/AppConfig.class.php';
include_once '../lib/HelloWorld.class.php';

$facebook = new Facebook(AppConfig::$api_key, AppConfig::$secret);

$facebook->require_frame();

$user = $facebook->require_login();

$facebook->require_add();

$app = new HelloWorld($facebook);

if ($_GET['ids']) {
  foreach($_GET['ids'] as $key) {
    $app->record_invite($user, $key);
  }
}

$exclude_ids = $facebook->api_client->friends_getAppUsers();

$sent_invites = $app->get_invites($user);
$exclude_ids = array_merge($exclude_ids, $sent_invites);

$referralTracker = urlencode("?referralbyuser=" . $user);

$app_name = AppConfig::$app_name;
$app_key = AppConfig::$api_key;

$invite_fbml = <<<FBML
<fb:name uid="$user" firstnameonly="true" shownetwork="false"/> wants you to add {$app_name} to receive greetings from <fb:pronoun objective="true" possessive="false" uid="$user"/>.
<fb:req-choice url="http://www.facebook.com/add.php?api_key={$app_key}&next=$referralTracker" label="Add {$app_name}" />
FBML;

?>
<fb:request-form type="<?= AppConfig::$app_name ?>" action="invite.php" content="<?= htmlentities($invite_fbml) ?>" invite="true">
	<fb:multi-friend-selector max="20" actiontext="Invite up to twenty of your friends." showborder="true" rows="5" exclude_ids="<?= join(',', $exclude_ids); ?>">
</fb:request-form>