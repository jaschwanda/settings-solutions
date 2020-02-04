<?php // ------------------------------------------------------------------------------------------------------------------------ //

/*
WordPress-Solutions is free software: you can redistribute it and/or modify it under the terms of the GNU General Public 
License as published by the Free Software Foundation, either version 3 of the License, or any later version.
 
WordPress-Solutions is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied 
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License along with WordPress-Solutions. If not, see 
https://github.com/jaschwanda/wordpress-solutions/blob/master/LICENSE.md

Copyright (c) 2020 by Jim Schwanda.
*/

final class USI_WordPress_Solutions_Phpinfo_Scan {

   const VERSION = '2.4.0 (2020-02-04)';

   private function __construct() {
   } // __construct();

   public static function info() {
      foreach ($_COOKIE as $key => $value) {
         if (substr($key, 0, 20) == 'wordpress_logged_in_') {
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
      }
      die('Accesss not allowed.');
   } // info();

} // Class USI_WordPress_Solutions_Phpinfo_Scan;

USI_WordPress_Solutions_Phpinfo_Scan::info();

// --------------------------------------------------------------------------------------------------------------------------- // ?>