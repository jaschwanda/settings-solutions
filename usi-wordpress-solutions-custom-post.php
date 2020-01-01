<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

class USI_WordPress_Solutions_Custom_Post {

   const VERSION = '2.3.1 (2020-01-01)';

   const POST    = 'custom-post';

   private $autosave_disable = true;

   function __construct() {

      if ($this->autosave_disable) add_action('admin_enqueue_scripts', array($this, 'action_admin_enqueue_scripts'));

      add_action('do_meta_boxes', array($this, 'action_do_meta_boxes'));

   } // __construct();

   function action_admin_enqueue_scripts() {
      if (self::POST == get_post_type()) {
         wp_deregister_script('autosave');
      }
   } // action_admin_enqueue_scripts();

   function action_do_meta_boxes() {
   } // action_do_meta_boxes();

} // Class USI_WordPress_Solutions_Custom_Post;

// --------------------------------------------------------------------------------------------------------------------------- // ?>