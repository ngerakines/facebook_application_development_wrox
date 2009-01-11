<?php

/*

CREATE TABLE user_facebook (
  fb_userid varchar(64) NOT NULL,
  user int(5) unsigned NOT NULL,
  session_key varchar(64) DEFAULT '',
  session_expires INT(11) DEFAULT 0,

  PRIMARY KEY aufb_fbid (aufb_fbid),
  UNIQUE INDEX aufb_user (aufb_user)
) TYPE=InnoDB;

*/

if (!defined('MEDIAWIKI')) {
    echo <<<EOT
To install this extension, put the following line in LocalSettings.php:require_once( "$IP/extensions/AuthFacebook/AuthFacebook.php" );
EOT;
    exit(1);
}

require_once("$IP/includes/SpecialPage.php");
require_once("$IP/facebook-platform/client/facebook.php");
require_once("$IP/extensions/AuthFacebook/AuthFacebook.class.php");

define('MEDIAWIKI_AUTHFACEBOOK_VERSION', '0.1');
define('MEDIAWIKI_AUTHFACEBOOK_APIKEY', '385a6b2e60a8249d988663441c92095c');
define('MEDIAWIKI_AUTHFACEBOOK_SECRET', '9904a01fda12369e357453c9906da0a5');

$wgHideAuthFacebookLoginLink = false;

$wgExtensionCredits['other'][] = array(
  'name' => 'AuthFacebook',
  'version' => MEDIAWIKI_AUTHFACEBOOK_VERSION,
  'author' => 'Nick Gerakines',
  'url' => 'http://www.mediawiki.org/wiki/Extension:AuthFacebook',
  'description' => 'Allow wiki users to authenticate using the Facebook Platform authentication process.'
);

$wgExtensionFunctions[] = 'setupAuthFacebook';

$wgSpecialPages['AuthFacebook'] = 'AuthFacebook';

$wgHooks['PersonalUrls'][] = 'AuthFacebookPersonalUrls';

function setupAuthFacebook() {
  global $wgMessageCache, $wgOut, $wgRequest, $wgHooks;
  $wgMessageCache->addMessages(array(
    'authfacebooklogin' => 'Login with Facebook',
    'authfacebookrror' => 'AuthFacebook Error',
    'authfacebookerrortext' => 'An error has occured, the site maintainer has been notified.',
    'authfacebookalreadyloggedin' => 'You are currently logged in as $1.',
    'authfacebooktitle' => 'Facebook Login',
    'authfacebookloggedin' => 'You have logged in as $1.'
  ));
  $action = $wgRequest->getText('action', 'view');
  if ($action == 'view') {
    $title = $wgRequest->getText('title');
    $nt = Title::newFromText($title);
    if ($nt && ($nt->getNamespace() == NS_USER) && strpos($nt->getText(), '/') === false) {
      $user = User::newFromName($nt->getText());
      if ($user && $user->getID() != 0) {
        $fb_userid = AuthFacebookGetFBUserID($user);
        if (isset($fb_userid) && strlen($fb_userid) != 0) {
          $url = 'http://www.facebook.com/profile.php?id=' . $fb_userid;
          $disp = htmlspecialchars($url);
          $wgOut->setSubtitle("<span class='subpages'><a href='$url'>$disp</a></span>");
        }
      }
    }
  }
}

function AuthFacebookGetFBUserID($user) {
  $fb_userid = null;
  if (isset($user) && $user->getId() != 0) {
    global $wgSharedDB, $wgDBprefix;
    if (isset($wgSharedDB)) {
      $tableName = "`${wgSharedDB}`.${wgDBprefix}user_facebook";
    } else {
      $tableName = 'user_facebook';
    }
    $dbr =& wfGetDB( DB_SLAVE );
    $res = $dbr->select(array($tableName),
      array('fb_userid'),
      array('user' => $user->getId()),
      'AuthFacebookGetFBUserID'
    );
    while ($res && $row = $dbr->fetchObject($res)) {
      $fb_userid = $row->fb_userid;
    }
    $dbr->freeResult($res);
  }
  return $fb_userid;
}

function AuthFacebookPersonalUrls(&$personal_urls, &$title) {
  global $wgHideAuthFacebookLoginLink, $wgUser, $wgLang;
  if (!$wgHideAuthFacebookLoginLink && $wgUser->getID() == 0) {
    $sk = $wgUser->getSkin();
    $returnto = ($title->getPrefixedUrl() == $wgLang->specialPage( 'Userlogout' )) ? '' : ('returnto=' . $title->getPrefixedURL());
    $personal_urls['authfacebooklogin'] = array(
      'text' => wfMsg('authfacebooklogin'),
      'href' => $sk->makeSpecialUrl( 'AuthFacebook', $returnto ),
      'active' => $title->isSpecial( 'AuthFacebook' )
    );
  }
  return true;
}

