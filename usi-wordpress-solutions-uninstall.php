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

   const VERSION = '2.10.1 (2020-11-02)';

   private function __construct() {
   } // __construct();

   static function uninstall($prefix) {

      global $wpdb;

      if (!defined('WP_UNINSTALL_PLUGIN')) exit;

/*
      if ($post_type) {
         $posts = get_posts(array('post_type' => $post_type, 'numberposts' => -1));
         foreach ($posts as $post) {
            wp_delete_post($post->ID, true);
         }
      }
*/
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