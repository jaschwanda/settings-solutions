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

   function __construct() {
      add_filter('plugin_row_meta', array($this, 'filter_plugin_row_meta'), 10, 2);
   } // __construct();

   function filter_plugin_row_meta($links, $file) {
      if (false !== strpos($file, USI_Settings_Solutions::TEXTDOMAIN)) {
         $links[0] = USI_Settings_Solutions_Versions::link(
            $links[0], // Original link text;
            USI_Settings_Solutions::NAME, // Title;
            USI_Settings_Solutions::VERSION, // Version;
            USI_Settings_Solutions::TEXTDOMAIN, // Text domain;
            dirname(__FILE__) // Folder containing plugin or theme;
         );
         $links[] = '<a href="https://www.usi2solve.com/donate/settings-solutions" target="_blank">' . 
            __('Donate', USI_Settings_Solutions::TEXTDOMAIN) . '</a>';
      }
      return($links);
   } // filter_plugin_row_meta();

} // Class USI_Settings_Solutions;

new USI_Settings_Solutions();

if (is_admin() && !defined('WP_UNINSTALL_PLUGIN')) {
   require_once('usi-settings-solutions-settings.php');
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