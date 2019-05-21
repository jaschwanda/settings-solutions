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

   const VERSION = '1.1.0 (2019-05-14)';

   private function __construct() {
   } // __construct();

   static function uninstall() {
usi_log(__METHOD__.':'.__LINE__.':');

      if (!defined('WP_UNINSTALL_PLUGIN')) exit;

      global $wpdb;

//      $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}USI_variables");

//      delete_metadata('user', null, $wpdb->prefix . USI_Variable_Solutions::PREFIX . '-options-category', null, true);

   } // uninstall();

} // Class USI_Settings_Solutions_Uninstall;

USI_Settings_Solutions_Uninstall::uninstall();

// --------------------------------------------------------------------------------------------------------------------------- // ?>
