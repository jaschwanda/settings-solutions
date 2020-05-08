<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

/*
WordPress-Solutions is free software: you can redistribute it and/or modify it under the terms of the GNU General Public 
License as published by the Free Software Foundation, either version 3 of the License, or any later version.
 
WordPress-Solutions is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied 
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License along with WordPress-Solutions. If not, see 
https://github.com/jaschwanda/wordpress-solutions/blob/master/LICENSE.md

Copyright (c) 2020 by Jim Schwanda.
*/

final class USI_WordPress_Solutions_Install {

   const VERSION = '2.5.1 (2020-05-07)';

   const VERSION_DATA = '1.0';

   private function __construct() {
   } // __construct();

   static function init() {
      $file = str_replace('-install', '', __FILE__);
      register_activation_hook($file, array(__CLASS__, 'hook_activation'));
      register_deactivation_hook($file, array(__CLASS__, 'hook_deactivation'));
   } // init();

   static function hook_activation() {

      if (!current_user_can('activate_plugins')) return;

      global $wpdb;

      check_admin_referer('activate-plugin_' . (isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : ''));

      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

      $user_id = get_current_user_id();

      $SAFE_history_table = $wpdb->prefix . 'USI_history';

      // The new-lines and double space after PRIMARY KEY are required;
      $sql = "CREATE TABLE `$SAFE_history_table` " .
         '(`history_id` int(10) unsigned NOT NULL AUTO_INCREMENT,' . PHP_EOL .
         "`time_stamp` timestamp DEFAULT CURRENT_TIMESTAMP," . PHP_EOL .
         "`user_id` bigint(20) unsigned DEFAULT '0'," . PHP_EOL .
         '`type` char(4) DEFAULT NULL,' . PHP_EOL .
         '`action` text DEFAULT NULL,' . PHP_EOL .
         "`target_id` bigint(20) unsigned DEFAULT '0'," . PHP_EOL .
         '`data` text DEFAULT NULL,' . PHP_EOL .
         'PRIMARY KEY  (`history_id`),' . PHP_EOL .
         'KEY `TARGET` (`target_id`))';

      $result = dbDelta($sql);

      $SAFE_log_table = $wpdb->prefix . 'USI_log';

      // The new-lines and double space after PRIMARY KEY are required;
      $sql = "CREATE TABLE `$SAFE_log_table` " .
         '(`log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,' . PHP_EOL .
         "`time_stamp` timestamp DEFAULT CURRENT_TIMESTAMP," . PHP_EOL .
         "`user_id` bigint(20) unsigned DEFAULT '0'," . PHP_EOL .
         '`action` text DEFAULT NULL,' . PHP_EOL .
         'PRIMARY KEY  (`log_id`))';

      $result = dbDelta($sql);

   } // hook_activation();

   static function hook_deactivation() {

      if (!current_user_can('activate_plugins')) return;

      check_admin_referer('deactivate-plugin_' . (isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : ''));

   } // hook_deactivation();

} // Class USI_WordPress_Solutions_Install;

USI_WordPress_Solutions_Install::init();

// --------------------------------------------------------------------------------------------------------------------------- // ?>
