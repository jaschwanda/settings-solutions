<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

final class USI_WordPress_Solutions_Install {

   const VERSION = '2.1.0 (2019-06-08)';
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
