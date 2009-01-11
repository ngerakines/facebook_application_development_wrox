<?php
//! A simple project configuration class
/*! Abstracting the application configuration into a class like this allows
    us to make the application easier to port and more secure.
*/
class AppConfig {
  // Facebook specific configuration variables
  public static $app_name = 'ngSignature';
  // Don't forget to set the XX in the app_url 
  public static $app_url = 'http://apps.facebook.com/ngsignature/';
  public static $app_id = '20268810596';
  public static $api_key = 'df160a6044ebaf025963bd04493b094b';
  public static $secret  = '1a312f395f3cb7f2e5e41eb07c2fa623';
}

?>