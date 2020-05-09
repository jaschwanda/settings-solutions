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

      add_action('delete_user', array(__CLASS__, 'action_delete_user'), 10, 2);
      add_action('edit_user_profile_update', array(__CLASS__, 'action_profile_update'));
      add_action('user_register', array(__CLASS__, 'action_user_register'), 10, 2);
      add_action('wp_login', array(__CLASS__, 'action_wp_login'), 10, 3);

      add_filter('logout_redirect', array(__CLASS__, 'filter_logout_redirect'), 10, 3);

   } // _init();

   public static function action_delete_user($id, $reassign) {
      $user = get_userdata($id);
      self::history(get_current_user_id(), 'user', 
         'Deleted <' . $user->data->user_login . '> from user list', $id, $_REQUEST);
   } // action_delete_user();

   public static function action_profile_update($user_id) {
      $user = get_userdata($user_id);
      self::history(get_current_user_id(), 'user', 
         'Modified <' . $user->data->user_login . '> user profile', $user_id, $_REQUEST);
   } // action_profile_update();

   public static function action_user_register($user_id) {
      $source = self::$source ? self::$source : $_REQUEST;
      $user   = get_userdata($user_id);
      self::history(get_current_user_id(), 'user', 
         'Added <' . $user->data->user_login . '> as new user', $user_id, $source);
      self::$source = null;
   } // action_user_register();

   public static function action_wp_login($user_login = null, $user = null) {
      // https://usersinsights.com/wordpress-user-login-hooks/
      self::history($user->ID, 'user', 'User <' . $user_login . '> logged in from ' . $_SERVER['REMOTE_ADDR'], $user->ID);
   } // action_wp_login();

   public static function filter_logout_redirect($redirect_to, $requested_redirect_to, $user) {
      self::history($user->ID, 'user', 'User <' . $user->data->user_login . '> logged out from ' . $_SERVER['REMOTE_ADDR'], $user->ID);
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