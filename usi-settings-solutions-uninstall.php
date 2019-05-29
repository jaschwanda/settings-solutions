<?php // ------------------------------------------------------------------------------------------------------------------------ //

if (!function_exists('usi_log')) {
   function usi_log($action) {
      global $wpdb;
      $wpdb->insert($wpdb->prefix . 'USI_log', 
         array(
            'action' => $action,
            'user_id' => get_current_user_id(), 
         )
      );
   } // usi_log();
} // ENDIF function_exists('usi_log');

final class USI_Settings_Solutions_Uninstall {

   const VERSION = '2.0.0 (2019-04-13)';

   private function __construct() {
   } // __construct();

   static function uninstall($prefix) {

      if (!defined('WP_UNINSTALL_PLUGIN')) exit;

      global $wpdb;
      $results = $wpdb->get_results('SELECT option_name FROM ' . $wpdb->prefix . 'options WHERE (option_name LIKE "' . $prefix . '-options%")');
      foreach ($results as $result) {
         $status = delete_option($result->option_name);
usi_log(__METHOD__.':'.__LINE__.':delete_option=' . ($status ? 'yes' : 'no') . '(' . $result->option_name . ')');
      }

      $results = $wpdb->get_results('SELECT DISTINCT meta_key FROM ' . $wpdb->prefix . 'usermeta WHERE (meta_key LIKE "' . $wpdb->prefix . $prefix . '-options%")');
usi_log(__METHOD__.':'.__LINE__.':SELECT DISTINCT meta_key FROM ' . $wpdb->prefix . 'usermeta WHERE (meta_key LIKE "' . $wpdb->prefix . $prefix . '-options%")');
      foreach ($results as $result) {
         $status = delete_metadata('user', null, $result->meta_key, null, true);
usi_log(__METHOD__.':'.__LINE__.':delete_metadata=' . ($status ? 'yes' : 'no') . '(' . $result->meta_key . ')');
      }

   } // uninstall();

} // Class USI_Settings_Solutions_Uninstall;

// --------------------------------------------------------------------------------------------------------------------------- // ?>
