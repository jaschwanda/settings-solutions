<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

/*
Plugin Name: WordPress-Solutions
Plugin URI: https://github.com/jaschwanda/wordpress-solutions
Description: The WordPress-Solutions plugin provides WordPress settings functionality for themes and plugins. The WordPress-Solutions plugin is developed and maintained by Universal Solutions.
Version: 2.0.0 (2019-04-13)
Author: Jim Schwanda
Author URI: http://www.usi2solve.com/leader
Text Domain: usi-wordpress-solutions
*/

final class USI_WordPress_Solutions {

   const VERSION = '2.1.0 (2019-06-08)';

   const NAME       = 'WordPress-Solutions';
   const PREFIX     = 'usi-wordpress';
   const TEXTDOMAIN = 'usi-wordpress-solutions';

   public static $options = array();

   function __construct() {
      if (empty(USI_WordPress_Solutions::$options)) {
         $defaults['preferences']['menu-sort'] = 'no';
         $defaults['preferences']['regexp'] = 'prefix';
         USI_WordPress_Solutions::$options = get_option(self::PREFIX . '-options', $defaults);
      }
   } // __construct();

} // Class USI_WordPress_Solutions;

new USI_WordPress_Solutions();

if (is_admin() && !defined('WP_UNINSTALL_PLUGIN')) {
   require_once('usi-wordpress-solutions-install.php');
   require_once('usi-wordpress-solutions-settings-settings.php');
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