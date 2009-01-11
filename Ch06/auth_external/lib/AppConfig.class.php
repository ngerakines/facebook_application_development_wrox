<?php
//! A simple project configuration class
/*! Abstracting the application configuration into a class like this allows
    us to make the application easier to port and more secure.
*/
class AppConfig {
  // Facebook specific configuration variables
  public static $app_name = 'ngExternal';
  public static $app_id = '8343607983';
  public static $api_key = 'b40f8f67e43ef4a8498dfb42719ab905';
  public static $secret  = '00cdf71bea62ec5a49fef8ac2b023b8f';
  // Application specific configuration variables
  public static $db_ip = '127.0.0.1';
  public static $db_user = 'root';
  public static $db_pass = 'asd123';
  public static $db_name = 'ngexternal';
}

?>