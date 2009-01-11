<?php

class AuthFacebook extends SpecialPage {
  function AuthFacebook() {
    SpecialPage::SpecialPage("AuthFacebook");
  }

  function execute($par) {
    global $wgRequest, $wgUser, $wgOut, $wgSessionStarted;
    if (! $wgSessionStarted) {
      $wgUser->SetupSession();
    }
    
    $this->setHeaders();
    
    if ($wgUser->getID() != 0) {
      $this->templateAlreadyLoggedIn();
      return;
    }
    
    $facebook = new Facebook(MEDIAWIKI_AUTHFACEBOOK_APIKEY, MEDIAWIKI_AUTHFACEBOOK_SECRET);
    if (! $facebook) {
      $wgOut->errorpage('authfacebookrror', 'authfacebookerrortext');
      return;
    }
    
    $auth_token = $wgRequest->getText('auth_token');
    
    if (! $auth_token) {
      $this->templateDefaultPage();
      return;
    }
    
    try {
      $session_response = $facebook->api_client->auth_getSession($auth_token);
    } catch (Exception $e) {
      $this->templateDefaultPage('There was an error, please refresh the page and try again.');
      error_log($e);
      return;
    }
    $session_key = $session_response['session_key'];
    $session_expires = $session_response['expires'];
    $fb_userid = $session_response['uid'];
    
    try {
      $userinfo_response = $facebook->api_client->users_getInfo(array($fb_userid), array('name'));
    } catch (Exception $e) {
      $this->templateDefaultPage('There was an error, please refresh the page and try again.');
      error_log($e);
      return;
    }
    
    $fb_username = $userinfo_response[0]['name'];
    
    $user = $this->getUserFromFB($fb_userid);
    
    if (isset($user)) {
      $this->updateUser($fb_userid, $session_key, $session_expires, $fb_username);
    } else {
      # For easy names
      $name = $this->createName($fb_username);
      if ($name) {
        $user = $this->createUser(
          $fb_userid,
          $fb_username,
          $session_key,
          $session_expires
        );
      }
    }
    
    if (! isset($user)) {
      $wgOut->errorpage('authfacebookrror', 'authfacebookerrortext');
    } else {
      $wgUser = $user;
      $wgUser->SetupSession();
      $wgUser->SetCookies();
      wfRunHooks('UserLoginComplete', array(&$wgUser));
      $this->setLoginCookie($fb_userid, $session_expires);
      $this->templateLoggedInPage();
    }
  }

  private function templateAlreadyLoggedIn() {
    global $wgUser, $wgOut;
    $wgOut->setPageTitle( wfMsg( 'authfacebooktitle' ) );
    $wgOut->setRobotpolicy( 'noindex,nofollow' );
    $wgOut->setArticleRelated( false );
    $wgOut->addWikiText( wfMsg( 'authfacebookalreadyloggedin', $wgUser->getName() ) );
  }

  private function templateDefaultPage($error = null) {
    global $wgUser, $wgOut;
    $wgOut->setPageTitle( wfMsg( 'authfacebooktitle' ) );
    if ($error) {
      $wgOut->addHTML('<table class="messagebox standard-talk" style="border: 2px solid #000000; background-color: #FFCCCC;"><tr><td align="left" width="100%">' . $error . '</td></tr></table>');
    }
    $wgOut->addHTML('<p>This wiki uses the Facebook Platform to allow wiki views to login using their Facebook username and password.</p>');
    $wgOut->addHTML('<p>The login page for local users can be found <a href="/index.php?title=Special:Userlogin">here</a>, although account creation has been disabled by the wiki adminstrators.</p>');
    $wgOut->addHTML('<p>To start the authentication process follow the login link below.</p>');
    $wgOut->addHTML('<p><a href="http://www.facebook.com/login.php?api_key=' . MEDIAWIKI_AUTHFACEBOOK_APIKEY . '&v=1.0"><img src="http://static.ak.facebook.com/images/devsite/facebook_login.gif"></a></p>');
  }

  private function templateLoggedInPage() {
    global $wgUser, $wgOut;
    $wgOut->setPageTitle( wfMsg( 'authfacebooktitle' ) );
    $wgOut->addHTML(wfMsg( 'authfacebookloggedin', $wgUser->getName() ));
  }

  private function setLoginCookie($fb_userid, $session_expires) {
    global $wgCookiePath, $wgCookieDomain, $wgCookieSecure, $wgCookiePrefix;
    global $wgOpenIDCookieExpiration;
    setcookie(
      $wgCookiePrefix . 'AuthFacebook',
      $fb_userid,
      $session_expires,
      $wgCookiePath,
      $wgCookieDomain,
      $wgCookieSecure
    );
  }

  private function createName($name) {
    if ($this->isUsernameOK($name)) {
      return $name;
    }
  }

  private function isUsernameOK($name) {
    global $wgReservedUsernames;
    return (0 == User::idFromName($name) && ! in_array($name, $wgReservedUsernames));
  }

  private function getUserFromFB($fb_userid) {
    global $wgSharedDB, $wgDBprefix;
    if (isset($wgSharedDB)) {
      $tableName = "`$wgSharedDB`.${wgDBprefix}user_facebook";
    } else {
      $tableName = 'user_facebook';
    }
    $dbr =& wfGetDB( DB_SLAVE );
    $id = $dbr->selectField($tableName, 'user', array(
      'fb_userid' => $fb_userid
    ));
    if ($id) {
      $name = User::whoIs($id);
      return User::newFromName($name);
    } else {
      return NULL;
    }
  }

  private function createUser($fb_userid, $name, $session_key, $session_expires) {
    global $wgAuth, $wgAllowRealName;
    $user = User::newFromName($name);
    $user->addToDatabase();
    if (!$user->getId()) {
      wfDebug("AuthFacebook: Error adding new user.\n");
    } else {
      $this->insertUser($user, $fb_userid, $session_key, $session_expires);
      $user->setOption('nickname', $name);
      $user->setEmail($fbuid . '@facebook.com');
      if ($wgAllowRealName) {
        $user->setRealName($name);
      }
      $user->saveSettings();
      return $user;
    }
  }

  private function insertUser($user, $fb_userid, $session_key, $session_expires) {
    global $wgSharedDB, $wgDBname;
    $dbw =& wfGetDB( DB_MASTER );
    if (isset($wgSharedDB)) {
      $dbw->selectDB($wgSharedDB);
    }
    $dbw->insert('user_facebook', array(
      'user' => $user->getId(),
      'fb_userid' => $fb_userid,
      'session_key' => $session_key,
      'session_expires' => $session_expires,
    ));
    if (isset($wgSharedDB)) {
      $dbw->selectDB($wgDBname);
    }
  }

  private function updateUser($fb_userid, $session_key, $session_expires, $fb_username) {
    global $wgSharedDB, $wgDBname;
    $dbw =& wfGetDB( DB_MASTER );
    if (isset($wgSharedDB)) {
      $dbw->selectDB($wgSharedDB);
    }
    $dbw->update(
      'user_facebook',
      array(
        'session_key' => $session_key,
        'session_expires' => $session_expires,
      ),
      array(
        'fb_userid' => $fb_userid
      ),
      'updateUser'
    );
    if (isset($wgSharedDB)) {
      $dbw->selectDB($wgDBname);
    }
  }

}
