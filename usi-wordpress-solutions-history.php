<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

/*
WordPress-Solutions is free software: you can redistribute it and/or modify it under the terms of the GNU General Public 
License as published by the Free Software Foundation, either version 3 of the License, or any later version.
 
WordPress-Solutions is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied 
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License along with WordPress-Solutions. If not, see 
https://github.com/jaschwanda/wordpress-solutions/blob/master/LICENSE.md

Copyright (c) 2020 by Jim Schwanda.
*/

final class USI_WordPress_Solutions_History {

   const VERSION = '2.5.1 (2020-05-07)';

   public static $source = null;

   private function __construct() {
   } // __construct();

   public static function _init() {

      add_action('delete_post', array(__CLASS__, 'action_delete_post'));
      add_action('delete_user', array(__CLASS__, 'action_delete_user'), 10, 2);
      add_action('edit_user_profile_update', array(__CLASS__, 'action_profile_update'));
      add_action('user_register', array(__CLASS__, 'action_user_register'), 10, 2);
      add_action('wp_insert_post', array(__CLASS__, 'action_wp_insert_post'), 10, 3);
      add_action('wp_login', array(__CLASS__, 'action_wp_login'), 10, 3);

      add_filter('logout_redirect', array(__CLASS__, 'filter_logout_redirect'), 10, 3);

      add_action('wp_dashboard_setup', array(__CLASS__, 'my_custom_dashboard_widgets'));

      add_action( 'wp_dashboard_setup', array(__CLASS__, 'wporg_remove_all_dashboard_metaboxes') );

   } // _init();

   public static function wporg_remove_all_dashboard_metaboxes() {
      remove_action( 'welcome_panel', 'wp_welcome_panel' );
      remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
      remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
      remove_meta_box( 'health_check_status', 'dashboard', 'normal' );
      remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
      remove_meta_box( 'dashboard_activity', 'dashboard', 'normal');
  }
// https://developer.wordpress.org/apis/handbook/dashboard-widgets/  
   public static function my_custom_dashboard_widgets() {
      global $wp_meta_boxes;
//usi::log('$wp_meta_boxes=', $wp_meta_boxes['dashboard']);
      wp_add_dashboard_widget('custom_help_widget', 'History', array(__CLASS__, 'custom_dashboard_help'));
   }

   public static function custom_dashboard_help() {
      echo '<p>Welcome to Custom Blog Theme! Need help? Contact the developer <a href="mailto:yourusername@gmail.com">here</a>. For WordPress Tutorials visit: <a href="https://www.wpbeginner.com" target="_blank">WPBeginner</a></p>';
   }

   public static function action_delete_post($post_id) {
      $post_type = get_post_type($post_id);
      $title     = get_the_title($post_id);
      $length    = strlen($title);
      if (36 < $length) $title = substr($title, 0, 33) . '...';
      self::history(get_current_user_id(), 'post', 
         'Deleted ' . $post_type . ' <' . $title . '> from system', $post_id, $_REQUEST);
   } // action_delete_post();

   public static function action_delete_user($id, $reassign) {
      $user = get_userdata($id);
      self::history(get_current_user_id(), 'user', 
         'Deleted <' . $user->data->display_name . '> from user list', $id, $_REQUEST);
   } // action_delete_user();

   public static function action_profile_update($user_id) {
      $user = get_userdata($user_id);
      self::history(get_current_user_id(), 'user', 
         'Modified <' . $user->data->display_name . '> user profile', $user_id, $_REQUEST);
   } // action_profile_update();

   public static function action_user_register($user_id) {
      $source = self::$source ? self::$source : $_REQUEST;
      $user   = get_userdata($user_id);
      self::history(get_current_user_id(), 'user', 
         'Added <' . $user->data->display_name . '> as new user', $user_id, $source);
      self::$source = null;
   } // action_user_register();

   public static function action_wp_insert_post($post_id, $post, $update) {
      $length = strlen($title = $post->post_title);
      if (36 < $length) $title = substr($title, 0, 33) . '...';
      self::history(get_current_user_id(), 'post', 
         'Added <' . $title . '> as new ' . $post->post_type, $post_id, $_REQUEST);
   } // action_wp_insert_post();

   public static function action_wp_login($user_login = null, $user = null) {
      // https://usersinsights.com/wordpress-user-login-hooks/
      $from = !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
      self::history($user->ID, 'user', 'User <' . $user->data->display_name . '> logged in from ' . $from, $user->ID);
   } // action_wp_login();

   public static function filter_logout_redirect($redirect_to, $requested_redirect_to, $user) {
      self::history($user->ID, 'user', 'User <' . $user->data->display_name . '> logged out from ' . $_SERVER['REMOTE_ADDR'], $user->ID);
      return($redirect_to);
   } // filter_logout_redirect();

   public static function history($user_id, $type, $action, $target_id = 0, $data = null) {
      global $wpdb;
      if (is_array($data) || is_object($data)) $data = print_r($data, true);
      if (false === $wpdb->insert(
         $wpdb->prefix . 'USI_history', 
         array('user_id' => $user_id, 'type' => $type, 'action' => $action, 'target_id' => $target_id, 'data' => $data),
         array('%d', '%s', '%s', '%d', '%s'))) {
         usi::log2('last-error=', $wpdb->last_error);
      }
   } // history();

} // Class USI_WordPress_Solutions_History;

USI_WordPress_Solutions_History::_init();

// --------------------------------------------------------------------------------------------------------------------------- // ?>