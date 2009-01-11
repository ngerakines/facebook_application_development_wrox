<?php

include_once '../lib/client/facebook.php';
include_once '../lib/AppConfig.class.php';
include_once '../lib/HelloWorld.class.php';

$facebook = new Facebook(AppConfig::$api_key, AppConfig::$secret);

$app = new HelloWorld($facebook);

$user = 0;
if (isset($_GET['user'])) {
  $user = (int) $_GET['user'];
}

$greetings_from = $app->get_greetings('user_from', $user);
$gfromcount = count($greetings_from);
$greetings_to = $app->get_greetings('user_to', $user);
$gtocount = count($greetings_to);

$tomessage = '';
$frommessage = '';
if ($gtocount) {
  $tomessage = "<p><fb:name uid=\"profileowner\" firstnameonly=\"true\" useyou=\"false\" /> has been waved to $gtocount times.</p>";
} else {
  $tomessage = "<p>No one has waved hello to <fb:name uid=\"profileowner\" firstnameonly=\"true\" useyou=\"false\" />!</p>";
}
if ($gfromcount) {
  $frommessage = "<p><fb:name uid=\"profileowner\" firstnameonly=\"true\" useyou=\"false\" /> has waved hello to $gfromcount people.</p>";
} else {
  $frommessage = "<p><fb:name uid=\"profileowner\" firstnameonly=\"true\" useyou=\"false\" /> has not waved to anyone.</p>";
}
?>
<fb:wide>
<?php echo $frommessage; ?>
<ul>
<?php foreach ($greetings_from as $greeting) { ?>
<fb:if-can-see uid="<?= $user ?>">
<li><fb:name uid="<?= $greeting['user_to'] ?>" useyou="false" /></li>
</fb:if-can-see>
<?php } ?>
</ul>
<?php echo $tomessage; ?>
<ul>
<?php foreach ($greetings_to as $greeting) { ?>
<fb:if-can-see uid="<?= $user ?>">
<li><fb:name uid="<?= $greeting['user_from'] ?>" useyou="false" /></li>
</fb:if-can-see>
<?php } ?>
</ul>
</fb:wide>
<fb:narrow>
<?php echo $frommessage; ?>
<ul>
<?php foreach ($greetings_from as $greeting) { ?>
<fb:if-can-see uid="<?= $user ?>">
<li><fb:name uid="<?= $greeting['user_to'] ?>" useyou="false" /></li>
</fb:if-can-see>
<?php } ?>
</ul>
<?php echo $tomessage; ?>
<ul>
<?php foreach ($greetings_to as $greeting) { ?>
<fb:if-can-see uid="<?= $user ?>">
<li><fb:name uid="<?= $greeting['user_from'] ?>" useyou="false" /></li>
</fb:if-can-see>
<?php } ?>
</ul>
</fb:narrow>