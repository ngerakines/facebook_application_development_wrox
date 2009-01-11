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
    } catch (Exception $e) {
      error_log($e->getMessage());
      return 0;
    }
    // Update the user profile for both users
    try {
        $this->update_profile($user_from);
        $this->update_profile($user_to);
    } catch (Exception $e) {
      error_log($e->getMessage());
      return 0;
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
    $greetings_from = $this->get_greetings('user_from', $user);
    $gfromcount = count($greetings_from);
    $greetings_to = $this->get_greetings('user_to', $user);
    $gtocount = count($greetings_to);
    // Set some of the variables we will be using
    $fbml = '';
    $tomessage = '';
    $frommessage = '';
    $gf_str = '';
    $gt_str = '';
    // Set different messages based on the number of greetings to and from
    // the user.
    if ($gtocount) {
      $tomessage = "<p><fb:name uid=\"$user\" firstnameonly=\"true\" useyou=\"false\" /> has been waved to $gtocount times.</p>";
    } else {
      $tomessage = "<p>No one has waved hello to <fb:name uid=\"$user\" firstnameonly=\"true\" useyou=\"false\" />!</p>";
    }
    if ($gfromcount) {
      $frommessage = "<p><fb:name uid=\"$user\" firstnameonly=\"true\" useyou=\"false\" /> has waved hello to $gfromcount people.</p>";
    } else {
      $frommessage = "<p><fb:name uid=\"$user\" firstnameonly=\"true\" useyou=\"false\" /> has not waved to anyone.</p>";
    }
    // Both the wide and narrow profile blocks will contain the same number
    // of greetings, both to and from.
    foreach ($greetings_from as $greeting) {
      $gf_str .= "<fb:if-can-see uid=\"$user\">";
      $gf_str .= '<li><fb:name uid="' . $greeting['user_to'] . '" useyou="false" /></li>';
      $gf_str .= '</fb:if-can-see>';
    }
    foreach ($greetings_to as $greeting) {
      $gt_str .= "<fb:if-can-see uid=\"$user\">";
      $gt_str .= '<li><fb:name uid="' . $greeting['user_from'] . '" useyou="false" /></li>';
      $gt_str .= '</fb:if-can-see>';
    }
    $fbml .= '<fb:wide>';
    $fbml .= "$frommessage\n<ul>$gf_str</ul>";
    $fbml .= "$tomessage<ul>$gt_str</ul>";
    $fbml .= '</fb:wide>';
    $fbml .= '<fb:narrow>';
    $fbml .= "$frommessage\n<ul>$gf_str</ul>";
    $fbml .= "$tomessage<ul>$gt_str</ul>";
    $fbml .= '</fb:narrow>';
    // Using the fbclient object set on object contstruction, submit a
    // facebook.profile.setFBML request
    //! \todo Throw an exception if the profile_setFBML method call fails
    $this->fbclient->api_client->profile_setFBML($fbml, $user);
    return 1;
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