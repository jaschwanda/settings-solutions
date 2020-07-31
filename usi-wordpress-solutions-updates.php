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

class USI_WordPress_Solutions_Updates {

   const VERSION = '2.9.0 (2020-07-30)';

   public $section = null;

   private $text_domain = null;

   function __construct($parent) {

      $this->text_domain = $parent->text_domain();

      $this->section      = array(
         'header_callback' => array($this, 'section_header'),
         'label' => 'Updates',
         'settings' => array(
            'git-update' => array(
               'type' => 'checkbox', 
               'label' => 'Enable Git updates',
               'notes' => 'Checks GitHub/GitLab for updates and notifies the administrator when updates are avaiable for download and installation.',
            ),
         ),
      );

   } // __construct();

   function section_header() {
      echo '<p>' . __('GitHub and GitLab are code hosting platforms for version control and collaboration. Thay are used to publish updates for this WordPress plugin.', $this->text_domain) . '</p>' . PHP_EOL;
   } // section_header();

} // Class USI_WordPress_Solutions_Updates;

// --------------------------------------------------------------------------------------------------------------------------- // ?>