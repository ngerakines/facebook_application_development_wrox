<?php

if (!defined('MEDIAWIKI')) {
    echo <<<EOT
To install this extension, put the following line in LocalSettings.php:require_once( "$IP/extensions/Facebook/Facebook.php" );
EOT;
    exit(1);
}

require_once("$IP/facebook-platform/client/facebook.php");

define('MEDIAWIKI_FACEBOOK_VERSION', '0.1');
define('MEDIAWIKI_FACEBOOK_APIKEY', '385a6b2e60a8249d988663441c92095c');
define('MEDIAWIKI_FACEBOOK_SECRET', '9904a01fda12369e357453c9906da0a5');

$wgExtensionCredits['other'][] = array(
  'name' => 'Facebook',
  'version' => MEDIAWIKI_FACEBOOK_VERSION,
  'author' => 'Nick Gerakines',
  'url' => 'http://www.mediawiki.org/wiki/Extension:AuthFacebook',
  'description' => 'Allow wiki users to display their wiki edits on their Facebook user profiles.'
);

$wgHooks['ArticleSaveComplete'][] = array('FacebookNotify');

function FacebookNotify(&$article, &$user, &$text, &$summary, &$minoredit, &$watchthis, &$sectionanchor, &$flags, $revision) {
  global $wgRequest;
  $fbdata = FacebookGetFBUserInfo($user);
  error_log(print_r($fbdata, true));
  $fb_userid = $fbdata['fb_userid'];
  if (isset($fb_userid) && strlen($fb_userid) != 0) {
    $facebook = new Facebook(MEDIAWIKI_FACEBOOK_APIKEY, MEDIAWIKI_FACEBOOK_SECRET);
    if (! $facebook) {
      return false;
    }
    $facebook->user = $fb_userid;
    $facebook->api_client->session_key = $fbdata['session_key'];
    $article_title = $article->mTitle;
    $article_url = $wgRequest->getRequestURL();
    $total_edits = User::edits($user->getId());
    $subtitle = '<fb:subtitle>Last updated on ' . date("M jS, Y - g\:i a") . '</fb:subtitle>';
    $profile_content = "<p><fb:name uid=\"$fb_userid\" useyou=\"false\" firstnameonly=\"true\" /> last updated the wiki page $article_title.</p>";
    $profile_content .= "<p><fb:name uid=\"$fb_userid\" useyou=\"false\" firstnameonly=\"true\" /> has made $total_edits edits to the wiki.</p>";
    $profile_body = "<fb:wide>$subtitle $profile_content</fb:wide>";
    $profile_body .= "<fb:narrow>$subtitle $profile_content</fb:narrow>";
    try {
      $facebook->api_client->profile_setFBML($profile_body, $fb_userid);
    } catch (Exception $e) {
      error_log($e);
      return false;
    }
  }
  return true;
}

function FacebookGetFBUserInfo($user) {
  $vars = array();
  if (isset($user) && $user->getId() != 0) {
    global $wgSharedDB, $wgDBprefix;
    if (isset($wgSharedDB)) {
      $tableName = "`${wgSharedDB}`.${wgDBprefix}user_facebook";
    } else {
      $tableName = 'user_facebook';
    }
    $dbr =& wfGetDB( DB_SLAVE );
    $res = $dbr->select(array($tableName),
      array('fb_userid', 'session_key', 'session_expires'),
      array('user' => $user->getId()),
      'FacebookGetFBUserInfo'
    );
    while ($res && $row = $dbr->fetchObject($res)) {
      $vars['fb_userid'] = $row->fb_userid;
      $vars['session_key'] = $row->session_key;
      $vars['session_expires'] = $row->session_expires;
    }
    $dbr->freeResult($res);
  }
  return $vars;
}
