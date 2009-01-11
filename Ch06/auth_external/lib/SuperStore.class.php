<?php

/*!
\class SuperStore

\brief The main Super Store project class

\par SQL Schema

\verbatim
CREATE TABLE facebook_accounts (
    user_id INT(11) NOT NULL,
    fb_user_id INT(11) NOT NULL,
    session_key VARCHAR(44) NOT NULL,
    session_expires INT(11) NOT NULL,
    row_created INT(11) NOT NULL
);
\endverbatim

\author Nick Gerakines

\version 1.0

\date  2007

 */

class SuperStore {

  public $fbclient;
  public $account;

  //! Create a new HelloWorld object
  /*! \param fbclient The facebook client object
   */
  public function __construct($fbclient) {
    // On creation, set the facebook client
    $this->fbclient = $fbclient;
  }

  // Return a user id representing some made-up Super Store 2000 user.
  function get_user_id() {
    return 3;
  }

  function has_account($user_id = null) {
    if (! $user_id) { $user_id = $this->get_user_id(); }
    if ($this->account['fb_user_id']) { return true; }
    $udata = $this->get_user($user_id);
    if (! $udata) { return false; }
    $this->account = $udata;
    return true;
  }

  function get_user($id) {
    $conn = $this->get_db_conn();
    // nkg: id should be escaped ... raw vars in sql = quick but bad
    $sql = "SELECT user_id, fb_user_id, session_key, session_expires, row_created FROM facebook_accounts WHERE user_id = $id LIMIT 1";
    $res = mysql_query($sql, $conn);
    // nkg: error checking? ... nah
    $data = array();
    while ($row = mysql_fetch_assoc($res)) {
        $data = $row;
    }
    return $data;
  }

  function account_id() {
    $has_account = $this->has_account();
    if (! $has_account) { return 0; }
    return $this->account['fb_user_id'];
  }

  function account_expires() {
    $has_account = $this->has_account();
    if (! $has_account) { return false; }
    return $this->account['session_expires'];
  }

  function account_has_expired() {
    $has_account = $this->has_account();
    if (! $has_account) { return true; } // nkg: makes sense to me
    $now = time;
    $expires = $this->account_expires();
    if ($expires > $now) { return true; }
    return false;
  }

  function tie_account() {
    $auth_token = $_GET['auth_token'];
    if (! $auth_token) { return false; }
    $session_response = $this->fbclient->api_client->auth_getSession($auth_token);
    $skey = $session_response['session_key'];
    $sexp = $session_response['expires'];
    $fbuid = $session_response['uid'];
    $uid = $this->get_user_id();
    $this->account = array('user_id' => $uid, 'fb_user_id' => $fbuid, 'session_key' => $skey, 'session_expires' => $sexp);
    try {
      $conn = $this->get_db_conn();
      $sql = "INSERT INTO facebook_accounts SET user_id = $uid, fb_user_id = $fbuid, session_key = \"$skey\", session_expires = $sexp, row_created = UNIX_TIMESTAMP(NOW())";
      mysql_query($sql, $conn);
    } catch (Exception $e) {
      return false;
    }
    return true;
  }

  //! Return a database connection
  function get_db_conn() {
    $conn = mysql_connect(
      AppConfig::$db_ip,
      AppConfig::$db_user,
      AppConfig::$db_pass
    );
    //! \exception Exception Error connecting to database
    if (! $conn) {
      throw new Exception('Error connecting to database: ' . mysql_error());
    }
    $success = mysql_select_db(AppConfig::$db_name, $conn);
    //! \exception Exception Error connecting to database
    if (! $success) {
      throw new Exception('Error connecting to database: ' . mysql_error());
    }
    return $conn;
  }
}

?>