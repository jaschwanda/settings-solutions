<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

/* 
Author:            Jim Schwanda
Author URI:        https://www.usi2solve.com/leader
Description:       The WordPress-Solutions plugin simplifys the implementation of WordPress functionality and is used by many Universal Solutions plugins and themes. The WordPress-Solutions plugin is developed and maintained by Universal Solutions.
Donate link:       https://www.usi2solve.com/donate/wordpress-solutions
License:           GPL-3.0
License URI:       https://github.com/jaschwanda/wordpress-solutions/blob/master/LICENSE.md
Plugin Name:       WordPress-Solutions
Plugin URI:        https://github.com/jaschwanda/wordpress-solutions
Requires at least: 5.0
Requires PHP:      5.6.25
Tested up to:      5.3.2
Text Domain:       usi-wordpress-solutions
Version:           2.4.5
*/

/*
WordPress-Solutions is free software: you can redistribute it and/or modify it under the terms of the GNU General Public 
License as published by the Free Software Foundation, either version 3 of the License, or any later version.
 
WordPress-Solutions is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied 
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License along with WordPress-Solutions. If not, see 
https://github.com/jaschwanda/wordpress-solutions/blob/master/LICENSE.md

Copyright (c) 2020 by Jim Schwanda.
*/

final class USI_WordPress_Solutions {

   const VERSION = '2.4.5 (2020-02-26)';

   const NAME       = 'WordPress-Solutions';
   const PREFIX     = 'usi-wordpress';
   const TEXTDOMAIN = 'usi-wordpress-solutions';

   public static $options = array();

   function __construct() {
      if (empty(USI_WordPress_Solutions::$options)) {
         $defaults['preferences']['menu-sort'] = 'no';
         USI_WordPress_Solutions::$options = get_option(self::PREFIX . '-options', $defaults);
      }
   } // __construct();

} // Class USI_WordPress_Solutions;

new USI_WordPress_Solutions();

if (is_admin() && !defined('WP_UNINSTALL_PLUGIN')) {
   add_action('init', 'add_thickbox');
   require_once('usi-wordpress-solutions-install.php');
   require_once('usi-wordpress-solutions-settings-settings.php');
   if (!empty(USI_WordPress_Solutions::$options['admin-options']['git-update'])) {
      require_once('usi-wordpress-solutions-update.php');
      new USI_WordPress_Solutions_Update_GitHub(__FILE__, 'jaschwanda', 'wordpress-solutions');
   }
}

if (!function_exists('usi_log')) {
   function usi_log($action) {
      global $wpdb;
      $wpdb->insert($wpdb->prefix . 'USI_log', array('action' => $action, 'user_id' => get_current_user_id()));
   } // usi_log();
} // ENDIF function_exists('usi_log');

// --------------------------------------------------------------------------------------------------------------------------- // ?>