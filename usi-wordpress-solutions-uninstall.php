<?php // ------------------------------------------------------------------------------------------------------------------------ //

/*
WordPress-Solutions is free software: you can redistribute it and/or modify it under the terms of the GNU General Public 
License as published by the Free Software Foundation, either version 3 of the License, or any later version.
 
WordPress-Solutions is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied 
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License along with WordPress-Solutions. If not, see 
https://github.com/jaschwanda/wordpress-solutions/blob/master/LICENSE.md

Copyright (c) 2020 by Jim Schwanda.
*/

final class USI_WordPress_Solutions_Uninstall {

   const VERSION = '2.4.12 (2020-04-19)';

   private function __construct() {
   } // __construct();

   static function uninstall($config) {

      global $wpdb;

      if (!defined('WP_UNINSTALL_PLUGIN')) exit;

      $capabilities = !empty($config['capabilities']) ? $config['capabilities'] : null;
      $post_type    = !empty($config['post_type'])    ? $config['post_type']    : null;
      $prefix       = !empty($config['prefix'])       ? $config['prefix']       : null;

      if ($capabilities) {

      }

      if ($post_type) {

         $posts = get_posts(array('post_type' => $post_type, 'numberposts' => -1));
         foreach ($posts as $post) {
            wp_delete_post($post->ID, true);
         }

      }

      if ($prefix) {

         $results = $wpdb->get_results('SELECT option_name FROM ' . $wpdb->prefix . 
            'options WHERE (option_name LIKE "' . $prefix . '-options%")');
         foreach ($results as $result) {
            delete_option($result->option_name);
         }

         $results = $wpdb->get_results('SELECT DISTINCT meta_key FROM ' . $wpdb->prefix . 
            'usermeta WHERE (meta_key LIKE "' . $wpdb->prefix . $prefix . '-options%")');
         foreach ($results as $result) {
            delete_metadata('user', null, $result->meta_key, null, true);
         }

      }

   } // uninstall();

} // Class USI_WordPress_Solutions_Uninstall;

// --------------------------------------------------------------------------------------------------------------------------- // ?>