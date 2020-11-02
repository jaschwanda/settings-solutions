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

class USI_WordPress_Solutions_Diagnostics {

   const VERSION = '2.10.1 (2020-11-02)';

   private $options     = null;
   private $text_domain = null;

   function __construct($parent, $options) {

      $this->options     = $options;

      $this->text_domain = $parent->text_domain();

      $this->section     = array(
         'fields_sanitize' => array($this, 'fields_sanitize'),
         'header_callback' => array($this, 'section_header'),
         'label' => 'Diagnostics',
         'localize_labels' => 'yes',
         'localize_notes' => 3, // <p class="description">__()</p>;
         'settings' => array(
            'session' => array(
               'f-class' => 'regular-text', 
               'label' => 'Diagnostic Session',
               'notes' => 'Enter the diagnostic session from the user you wish to analyze.',
               'type' => 'text', 
            ),
            'code' => array(
               'f-class' => 'regular-text', 
               'label' => 'Diagnostic Code',
               'notes' => 'Code used to select diagnostic operations.',
               'readonly' => true, 
               'type' => 'text', 
            ),
         ),
      );

      foreach ($options as $key => $values) {
         $this->section['settings'][$key]['label'] = $key;
         $this->section['settings'][$key]['notes'] = $values['notes'];
         $this->section['settings'][$key]['type'] = 'checkbox';
         $this->section['settings'][$key]['usi-code'] = $values['value'];
      }

   } // __construct();

   function fields_sanitize($input) {
      $code = 0;
      if (!empty($input['diagnostics']['session'])) {
         foreach ($input['diagnostics'] as $key => $value) {
            if ('checkbox' == $this->section['settings'][$key]['type']) {
               if (!empty($this->options[$key]['value'])) $code |= $this->section['settings'][$key]['usi-code'];
            }
         }
      }
      $input['diagnostics']['code'] = $code;
      return($input);
   } // fields_sanitize();

   public static function get_log($options, $log_log = false) {
      $log = 0;
      if (!empty($options['diagnostics']['session'])) {
         if (!($session_id = session_id())) {
            session_start(); 
            $session_id = session_id();
         }
         if (($session_id == $options['diagnostics']['session']) || ('all' == $options['diagnostics']['session'])) {
            if (!empty($options['diagnostics']['code'])) $log = (int)$options['diagnostics']['code'];
         }
      }
      if ($log_log) usi::log2('$log=', $log);
      return($log);
   } // get_log();

   function section_header() {
      echo '<p>' . sprintf(__(' Send this link: <b>%s</b> to the user to get the user\'s diagnostic session.',
         $this->text_domain), plugin_dir_url(__FILE__) . 'diagnostics.php') . '</p>' . PHP_EOL;
   } // section_header();

} // Class USI_WordPress_Solutions_Diagnostics;

// --------------------------------------------------------------------------------------------------------------------------- // ?>