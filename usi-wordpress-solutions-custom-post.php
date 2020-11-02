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

class USI_WordPress_Solutions_Custom_Post {

   const VERSION    = '2.10.1 (2020-11-02)';

   const DEBUG_OFF  = 0x0000; // USI_WordPress_Solutions_Custom_Post;
   const DEBUG_INIT = 0x0001; // USI_WordPress_Solutions_Custom_Post;

   protected $autosave_disable = true;
   protected $defaults = array();
   protected $log      = 0;
   protected $prefix   = 'usi-post';
   protected $type     = 'usi-post';

   function __construct() {

      // Fires after WordPress has finished loading but before any headers are sent;
      add_action('init', array($this, '_init'), 1);

   } // __construct();

   function _init() {

      $options = & static::$options;

      if (empty($options)) {
         $options = get_option($this->prefix . '-options', $this->defaults);
      }

      $this->log = USI_WordPress_Solutions_Diagnostics::get_log($options);

      if (self::DEBUG_INIT & $this->log) usi::log('$options=', $options);

      if ($this->autosave_disable) add_action('admin_enqueue_scripts', array($this, 'action_admin_enqueue_scripts'));

      add_action('do_meta_boxes', array($this, 'action_do_meta_boxes'));

   } // _init();

   public function action_admin_enqueue_scripts() {
      if (get_post_type() == $this->type) {
         wp_deregister_script('autosave');
      }
   } // action_admin_enqueue_scripts();

   public function action_do_meta_boxes() {
   } // action_do_meta_boxes();

   public static function custom_capabilities($single, $plural) {
      return(
         array(
            'delete_post'            => $single . '_delete',
            'delete_posts'           => $plural . '_delete',
            'delete_others_posts'    => $plural . '_delete_others',
            'delete_private_posts'   => $plural . '_delete_private',
            'delete_published_posts' => $plural . '_delete_published',
            'edit_post'              => $single . '_edit',
            'edit_posts'             => $plural . '_edit',
            'edit_others_posts'      => $plural . '_edit_others',
            'edit_private_posts'     => $plural . '_edit_private',
            'edit_published_posts'   => $plural . '_edit_published',
            'publish_posts'          => $plural . '_publish',
            'read_private_posts'     => $plural . '_read_private',
         )
      );
   } // custom_capabilities();

   public static function get_post_by(string $type, string $field, string $key, $output = OBJECT, $log = false) {

      // $output = (ARRAY_A | ARRAY_N | OBJECT | OBJECT_K);

      global $wpdb;

      $SAFE_post  = $wpdb->prefix . 'posts';

      $post = $wpdb->get_row(
         $sql = $wpdb->prepare("SELECT * FROM `$SAFE_post` WHERE (`post_type` = %s) AND (`$field` = %s)", $type, $key),
         $output
      );

      if ($log) usi::log2('$sql=', $sql, '\n$post=', $post);

      return($post);

   } // get_post_by();

} // Class USI_WordPress_Solutions_Custom_Post;

// --------------------------------------------------------------------------------------------------------------------------- // ?>