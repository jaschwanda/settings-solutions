<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

/*
Plugin Name: Settings-Solutions
Plugin URI: https://github.com/jaschwanda/settings-solutions
Description: The Settings-Solutions plugin provides WordPress settings functionality for themes and plugins. The Settings-Solutions plugin is developed and maintained by Universal Solutions.
Version: 2.0.0 (2019-04-13)
Author: Jim Schwanda
Author URI: http://www.usi2solve.com/leader
Text Domain: usi-settings-solutions
*/

final class USI_Settings_Solutions {

   const VERSION = '2.0.0 (2019-04-13)';

   const NAME       = 'Settings-Solutions';
   const PREFIX     = 'usi-settings';
   const TEXTDOMAIN = 'usi-settings-solutions';

   public static $options = array();

   function __construct() {
      if (empty(USI_Settings_Solutions::$options)) {
         $defaults['preferences']['menu-sort'] = 'no';
         $defaults['preferences']['regexp'] = 'prefix';
         USI_Settings_Solutions::$options = get_option(self::PREFIX . '-options', $defaults);
      }
   } // __construct();

} // Class USI_Settings_Solutions;

new USI_Settings_Solutions();

if (is_admin() && !defined('WP_UNINSTALL_PLUGIN')) {
   require_once('usi-settings-solutions-install.php');
   require_once('usi-settings-solutions-settings-settings.php');
}

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

// --------------------------------------------------------------------------------------------------------------------------- // ?>