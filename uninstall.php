<?php // ------------------------------------------------------------------------------------------------------------------------ //

//defined('ABSPATH') or die('Accesss not allowed.');

/*
WordPress-Solutions is free software: you can redistribute it and/or modify it under the terms of the GNU General Public 
License as published by the Free Software Foundation, either version 3 of the License, or any later version.
 
WordPress-Solutions is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied 
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License along with WordPress-Solutions. If not, see 
https://github.com/jaschwanda/wordpress-solutions/blob/master/LICENSE.md

Copyright (c) 2020 by Jim Schwanda.
*/

if (!defined('WP_UNINSTALL_PLUGIN')) exit;

require_once('usi-wordpress-solutions-uninstall.php');
require_once('usi-wordpress-solutions.php');

final class USI_WordPress_Solutions_Uninstall_Uninstall {

   const VERSION = '2.9.10 (2020-10-16)';

   private function __construct() {
   } // __construct();

   static function uninstall() {

      global $wpdb;

      $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}USI_history");

      $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}USI_log");

   } // uninstall();

} // Class USI_Variable_Solutions_Uninstall_Uninstall;

USI_WordPress_Solutions_Uninstall::uninstall(USI_WordPress_Solutions::PREFIX);

USI_WordPress_Solutions_Uninstall_Uninstall::uninstall();

// --------------------------------------------------------------------------------------------------------------------------- // ?>
