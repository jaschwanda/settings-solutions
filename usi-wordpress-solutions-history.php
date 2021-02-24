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

require_once('usi-wordpress-solutions.php');

final class USI_WordPress_Solutions_History {

   const VERSION = '2.11.0 (2021-02-24)';

   private static $pre_post_update_data = null;
   private static $pre_post_update_id   = 0;
   private static $pre_post_update_meta = null;

   public static $source = null;

   private function __construct() {
   } // __construct();

   public static function _init() {

      if (!empty(USI_WordPress_Solutions::$options['admin-options']['history'])) {

         add_action('delete_post', array(__CLASS__, 'action_delete_post'));
         add_action('delete_user', array(__CLASS__, 'action_delete_user'), 10, 2);
         add_action('edit_user_profile_update', array(__CLASS__, 'action_profile_update'));
         add_action('pre_post_update', array(__CLASS__, 'action_pre_post_update'), 110, 2);
         add_action('user_register', array(__CLASS__, 'action_user_register'), 10, 2);
         add_action('wp_insert_post', array(__CLASS__, 'action_wp_insert_post'), 10, 3);
         add_action('wp_login', array(__CLASS__, 'action_wp_login'), 10, 3);

         add_filter('logout_redirect', array(__CLASS__, 'filter_logout_redirect'), 10, 3);

      }

   } // _init();

   public static function action_delete_post($post_id) {
      if (wp_is_post_revision($post_id)) return; // Don't log revision actions, may confuse users;
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

   public static function action_pre_post_update($post_id, $data) {
      $post = get_post($post_id);
      self::$pre_post_update_id   = $post_id;
      self::$pre_post_update_data = $post->post_content;
      self::$pre_post_update_meta = get_post_meta($post_id);
   } // action_pre_post_update();

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

      if (wp_is_post_revision($post_id)) return; // Don't log revision actions, may confuse users;

      $source = self::$source ? self::$source : $_REQUEST;
      $length = strlen($title = $post->post_title);
      if (36 < $length) $title = substr($title, 0, 33) . '...';

      // IF updating an auto-draft copy with the post title;
      if (!is_object($source) && !empty($source['auto_draft'])) {
         global $wpdb;
         $results = $wpdb->update(
            $wpdb->prefix . 'USI_history', 
            array(
               'action' => 'Added ' . $post->post_type . ' <' . $title . '>',
               'data' => print_r($source, true),
            ), 
            array(
               'target_id' => $post_id,
               'action' => 'Added ' . $post->post_type . ' <Auto Draft>',
            ),
            array('%s', '%s'),
            array('%d', '%s')
         );

      } else { // ELSE not auto-draft update;

         // Only update the history log if the post content or meta data has changed;
         // Assume that it has and then reset assumption if we can verify it has not changed;
         $log_update = true;
         if (isset($_REQUEST['content']) && ($post_id == self::$pre_post_update_id)) {
            $log_update = (self::$pre_post_update_data != $_REQUEST['content']) || (self::$pre_post_update_meta != get_post_meta($post_id));
         }

         if ($log_update) self::history(get_current_user_id(), 'post', 
            ($update ? 'Updated' : 'Added') . ' ' . $post->post_type . ' <' . $title . '>', $post_id, $source);

      } // ENDIF not auto-draft update;

      self::$source = null;

   } // action_wp_insert_post();

   public static function action_wp_login($user_login = null, $user = null) {
      // https://usersinsights.com/wordpress-user-login-hooks/
      self::history($user->ID, 'user', 'User <' . $user->data->display_name . '> logged in from ' . self::from(), $user->ID);
   } // action_wp_login();

   public static function filter_logout_redirect($redirect_to, $requested_redirect_to, $user) {
      self::history($user->ID, 'user', 'User <' . $user->data->display_name . '> logged out from ' . self::from(), $user->ID);
      return($redirect_to);
   } // filter_logout_redirect();

   private static function from() {
      return(!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
   } // from();

   public static function history($user_id, $type, $action, $target_id = 0, $data = null) {

      if (!empty(USI_WordPress_Solutions::$options['admin-options']['history'])) {

         global $wpdb;
         if (is_array($data) || is_object($data)) $data = substr(print_r($data, true), 0, 65535);
         if (false === $wpdb->insert(
            $wpdb->prefix . 'USI_history', 
            array('user_id' => $user_id, 'type' => $type, 'action' => $action, 'target_id' => $target_id, 'data' => $data),
            array('%d', '%s', '%s', '%d', '%s'))) {
            usi::log2('last-error=', $wpdb->last_error);
         }

      }

   } // history();

} // Class USI_WordPress_Solutions_History;

USI_WordPress_Solutions_History::_init();

// --------------------------------------------------------------------------------------------------------------------------- // ?>