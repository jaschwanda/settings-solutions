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

   const VERSION = '2.4.4 (2020-02-19)';

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