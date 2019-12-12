<?php // ------------------------------------------------------------------------------------------------------------------------ //

final class USI_WordPress_Solutions_Uninstall {

   const VERSION = '2.2.0 (2019-12-11)';

   private function __construct() {
   } // __construct();

   static function uninstall($prefix, $post_type = null) {

      if (!defined('WP_UNINSTALL_PLUGIN')) exit;

      if ($post_type) {
         $posts = get_posts(array('post_type' => $post_type, 'numberposts' => -1));
         foreach ($posts as $post) {
            wp_delete_post($post->ID, true);
         }
      }

      global $wpdb;
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

   } // uninstall();

} // Class USI_WordPress_Solutions_Uninstall;

// --------------------------------------------------------------------------------------------------------------------------- // ?>