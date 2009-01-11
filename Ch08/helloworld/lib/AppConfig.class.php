<?php
//! A simple project configuration class
/*! Abstracting the application configuration into a class like this allows
    us to make the application easier to port and more secure.
*/
class AppConfig {
  // Facebook specific configuration variables
  public static $app_name = 'HelloWorld';
  public static $app_url = 'http://apps.facebook.com/nghelloworld/';
  public static $app_home = 'http://blog.socklabs.com/fbapps/nghello2/htdocs/';
  public static $app_id = '18266139880';
  public static $api_key = '69a09711ee2637755f0091ff5b497733';
  public static $secret  = '9d61409757a5ddae385bba8c2a9d37a3';
  // Application specific configuration variables
  public static $db_ip = '127.0.0.1';
  public static $db_user = 'root';
  public static $db_pass = 'asd123';
  public static $db_name = 'nghelloworld';
}

?>