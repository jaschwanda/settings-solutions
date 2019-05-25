<?php // ------------------------------------------------------------------------------------------------------------------------ //

final class USI_Settings_Solutions_Uninstall {

   const VERSION = '2.0.0 (2019-04-13)';

   private function __construct() {
   } // __construct();

   static function uninstall($prefix) {

      if (!defined('WP_UNINSTALL_PLUGIN')) exit;

      delete_option($prefix . '-options');

   } // uninstall();

} // Class USI_Settings_Solutions_Uninstall;

// --------------------------------------------------------------------------------------------------------------------------- // ?>
