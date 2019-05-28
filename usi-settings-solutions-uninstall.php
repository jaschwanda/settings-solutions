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

usi_log(__FILE__);

final class USI_Settings_Solutions_Uninstall {

   const VERSION = '2.0.0 (2019-04-13)';

   private function __construct() {
   } // __construct();

   static function uninstall($prefix) {

      if (!defined('WP_UNINSTALL_PLUGIN')) exit;
      $status = delete_option($prefix . '-options');
usi_log(__METHOD__.':'.__LINE__.':delete_option=' . ($status ? 'yes' : 'no') . '(' . $prefix . '-options)');
      $status = delete_metadata('user', null, $wpdb->prefix . $prefix . '-options%', null, true);
usi_log(__METHOD__.':'.__LINE__.':delete_metadata=' . ($status ? 'yes' : 'no') . '(' . $wpdb->prefix . $prefix . '-options%)');
      //update_user_option($current_user_id, $prefix . '-options-role-id', $this->role_id);
      //update_user_option($current_user_id, $prefix . '-options-user-id', $this->user_id);

   } // uninstall();

} // Class USI_Settings_Solutions_Uninstall;

// --------------------------------------------------------------------------------------------------------------------------- // ?>
