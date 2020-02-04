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

   const VERSION = '2.4.0 (2020-02-04)';

   private $text_domain = null;

   private function __construct($text_domain) {
      $this->text_domain = $text_domain;
   } // __construct();

   public function section_header() {
      echo '<p>' . __('GitHub and GitLab are code hosting platforms for version control and collaboration. Thay are used to publish updates for this WordPress plugin.', $this->text_domain) . '</p>' . PHP_EOL;
   } // section_header();

   public static function section($text_domain) {

      $that = new USI_WordPress_Solutions_Updates($text_domain);

      return(
         array(
            'header_callback' => array($that, 'section_header'),
            'label' => 'Updates',
            'settings' => array(
               'git-update' => array(
                  'type' => 'checkbox', 
                  'label' => 'Enable Git updates',
                  'notes' => 'Checks GitHub/GitLab for updates and notifies the administrator when updates are avaiable for download and installation.',
               ),
            ),
         )
      );

   } // section();

} // Class USI_WordPress_Solutions_Updates;

// --------------------------------------------------------------------------------------------------------------------------- // ?>