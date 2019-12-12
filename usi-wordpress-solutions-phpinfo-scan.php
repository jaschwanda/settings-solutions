<?php // ------------------------------------------------------------------------------------------------------------------------ //

final class USI_WordPress_Solutions_Phpinfo_Scan {

   const VERSION = '2.2.0 (2019-12-11)';

   private function __construct() {
   } // __construct();

   public static function info() {
      foreach ($_COOKIE as $key => $value) if (substr($key, 0, 20) == 'wordpress_logged_in_') {
         // https://www.securitysift.com/understanding-wordpress-auth-cookies/
         //echo '<pre>';
         //echo "key=$key value=$value" . PHP_EOL;
         //$crumbs = explode('|', $value);
         //print_r($crumbs);
         //require_once('../../../wp-config.php');
         //$hash_key = AUTH_KEY . AUTH_SALT;
         //echo "hash_key=$hash_key" . PHP_EOL;
         //echo '</pre>';
         phpinfo();
         die();
      }
      die('Accesss not allowed.');
   } // info();

} // Class USI_WordPress_Solutions_Phpinfo_Scan;

USI_WordPress_Solutions_Phpinfo_Scan::info();

// --------------------------------------------------------------------------------------------------------------------------- // ?>