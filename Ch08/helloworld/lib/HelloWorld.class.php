<?php

/*!
\class HelloWorld

\brief The main Hello World project class

\details

This class contains most of the core logic used throughout the Hello World
project.

\par SQL Schema

\verbatim
CREATE TABLE greetings (
    user_from INT(11) NOT NULL,
    user_to INT(11) NOT NULL,
    row_created INT(11) NOT NULL
);
\endverbatim

\verbatim
CREATE TABLE users (
    fb_id INT(14) NOT NULL,
    ts_in INT(11) NOT NULL DEFAULT '0',
    ts_out INT(11) NOT NULL DEFAULT '0',
    total_actions INT(11) NOT NULL DEFAULT '0',
    deleted INT(1) NOT NULL DEFAULT '0'
);
\endverbatim

\verbatim
CREATE TABLE invites (
    user_from INT(11) NOT NULL,
    user_to INT(11) NOT NULL,
    ts_sent INT(11) NOT NULL DEFAULT '0',
    accepted INT(1) NOT NULL DEFAULT '0'
);
\endverbatim

\author Nick Gerakines

\version 1.0

\date  2007

 */

class HelloWorld {

  public $fbclient;

  //! Create a new HelloWorld object
  /*! \param fbclient The facebook client object
   */
  public function __construct($fbclient) {
    // On creation, set the facebook client
    $this->fbclient = $fbclient;
  }

  //! Send a hello greeting from one user to another
  /*! \param user_from The user sending the greeting
   *  \param user_to The user receiving the greeting
  */
  function wave_hello($user_from, $user_to) {
    // Record the greeting
    try {
      $conn = $this->get_db_conn();
      $sql = "INSERT INTO greetings SET user_from = $user_from, user_to = $user_to, row_created = UNIX_TIMESTAMP(NOW())";
      error_log($sql);
      mysql_query($sql, $conn);
      $sql = "UPDATE users SET total_actions = total_actions + 1 WHERE fb_id = $user_from";
      error_log($sql);
      mysql_query($sql, $conn);
    } catch (Exception $e) {
      error_log($e->getMessage());
    }
    // Update the user profile for both users
    try {
        $this->update_profile($user_from);
        $this->update_profile($user_to);
    } catch (Exception $e) {
      error_log($e->getMessage());
    }
    return 1;
  }

  //! Returns the greetings to or from a user
  /*! \param type The type of request, valid options are user_from and user_to
   *  \param user The user who's greetings are returned
      \param limit Limit the query to this many results
   */
  function get_greetings($type = 'user_from', $user, $limit = 5) {
    $conn = $this->get_db_conn();
    $sql = "SELECT user_to, user_from, row_created FROM greetings WHERE $type = $user ORDER BY row_created DESC LIMIT $limit";
    error_log($sql);
    $res = mysql_query($sql, $conn);
    $greetings = array();
    if (! $res) { return $greetings; }
    while ($row = mysql_fetch_assoc($res)) {
      $greetings[] = $row;
    }
    return $greetings;
  }

  //! Returns the number of greetings to or from a user
  /*! \param type The type of request, valid options are user_from and user_to
   *  \param user The user who's greetings are returned
   */
  function get_greeting_count($type = 'user_from', $user) {
    $conn = $this->get_db_conn();
    $sql = "SELECT count(*) as cnt FROM greetings WHERE $type = $user";
    error_log($sql);
    $res = mysql_query($sql, $conn);
    if (! $res) { return 0; }
    $count = 0;
    while ($row = mysql_fetch_assoc($res)) {
      $count = $row["cnt"];
    }
    return $count;
  }

  //! Render and submit an updated profile block for a user
  /*! \param user The user who's profile to update
   */
  function update_profile($user) {
    // The url of the profile page that will display the profile content.
    $url = AppConfig::$app_home . 'profile.php?user=' . $user;
    // Instruct the Facebook Platform to refresh the content from the
    // profile url.
    $this->fbclient->api_client->fbml_refreshRefUrl($url);
    return 1;
  }

  //! Set the default profile content with the fb:ref FBML entity.
  /*! \param user The user who's profile to update
   */
  function first_time($user) {
    try {
      $conn = $this->get_db_conn();
      $sql = "INSERT INTO users SET fb_id = $user, ts_in = UNIX_TIMESTAMP(NOW())";
      error_log($sql);
      mysql_query($sql, $conn);
    } catch (Exception $e) {
      error_log($e->getMessage());
    }
    // The url of the profile page that will display the profile content.
    $url = AppConfig::$app_home . 'profile.php?user=' . $user;
    $fbml = "<fb:ref url=\"$url\" />";
    //! \todo Throw an exception if the profile_setFBML method call fails
    $this->fbclient->api_client->profile_setFBML($fbml, $user);
    // Instruct the Facebook Platform to refresh the content from the
    // profile url.
    $this->fbclient->api_client->fbml_refreshRefUrl($url);
    return 1;
  }

  function goodbye($user) {
    try {
      $conn = $this->get_db_conn();
      $sql = "UPDATE users SET fb_id = $user, ts_out = UNIX_TIMESTAMP(NOW()), deleted = 1";
      error_log($sql);
      mysql_query($sql, $conn);
    } catch (Exception $e) {
      error_log($e->getMessage());
    }
    return 1;
  }

  function record_invite($user_from, $user_to) {
    try {
      $conn = $this->get_db_conn();
      $sql = "INSERT INTO invites SET user_from = $user_from, user_to = $user_to, ts_sent = UNIX_TIMESTAMP(NOW()), accepted = 0";
      error_log($sql);
      mysql_query($sql, $conn);
    } catch (Exception $e) {
      error_log($e->getMessage());
    }
    return 1;
  }

  function save_referral($user, $user_from) {
    try {
      $conn = $this->get_db_conn();
      $sql = "UPDATE invites SET accepted = 1 WHERE user_from = $user_from AND user_to = $user";
      error_log($sql);
      mysql_query($sql, $conn);
    } catch (Exception $e) {
      error_log($e->getMessage());
    }
    return 1;
  }

  function get_invites($user) {
    $conn = $this->get_db_conn();
    $sql = "SELECT user_to FROM invites WHERE user_from = $user";
    error_log($sql);
    $res = mysql_query($sql, $conn);
    $invites = array();
    if (! $res) { return $invites; }
    while ($row = mysql_fetch_assoc($res)) {
      $invites[] = $row['user_to'];
    }
    return $invites;
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