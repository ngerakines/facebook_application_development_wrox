<?php
//! A simple project configuration class
/*! Abstracting the application configuration into a class like this allows
    us to make the application easier to port and more secure.
*/
class AppConfig {
  // Facebook specific configuration variables
  public static $app_name = 'HelloWorld';
  public static $app_url = 'http://apps.facebook.com/nghelloworld/';
  public static $app_id = '9937165273';
  public static $api_key = '2c6f440ba45414084993df8f9850d8fd';
  public static $secret  = 'bce49c13bea24f31a9b4962c8ec2c054';
  // Application specific configuration variables
  public static $db_ip = '127.0.0.1';
  public static $db_user = 'root';
  public static $db_pass = 'asd123';
  public static $db_name = 'nghelloworld';
}

?>