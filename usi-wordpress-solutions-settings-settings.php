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

require_once(plugin_dir_path(__DIR__) . 'usi-wordpress-solutions/usi-wordpress-solutions-popup.php');
require_once(plugin_dir_path(__DIR__) . 'usi-wordpress-solutions/usi-wordpress-solutions-settings.php');
require_once(plugin_dir_path(__DIR__) . 'usi-wordpress-solutions/usi-wordpress-solutions-versions.php');

class USI_WordPress_Solutions_Settings_Settings extends USI_WordPress_Solutions_Settings {

   const VERSION = '2.5.1 (2020-05-07)';

   private $popup = null;

   function __construct() {

      parent::__construct(
         array(
            'name' => USI_WordPress_Solutions::NAME, 
            'prefix' => USI_WordPress_Solutions::PREFIX, 
            'text_domain' => USI_WordPress_Solutions::TEXTDOMAIN,
            'options' => USI_WordPress_Solutions::$options,
         )
      );

   } // __construct();

   function filter_plugin_row_meta($links, $file) {
      if (false !== strpos($file, USI_WordPress_Solutions::TEXTDOMAIN)) {
         $links[0] = USI_WordPress_Solutions_Versions::link(
            $links[0], // Original link text;
            USI_WordPress_Solutions::NAME, // Title;
            USI_WordPress_Solutions::VERSION, // Version;
            USI_WordPress_Solutions::TEXTDOMAIN, // Text domain;
            __DIR__ // Folder containing plugin or theme;
         );
         $links[] = '<a href="https://www.usi2solve.com/donate/wordpress-solutions" target="_blank">' . 
            __('Donate', USI_WordPress_Solutions::TEXTDOMAIN) . '</a>';
      }
      return($links);
   } // filter_plugin_row_meta();

   function sections() {

      $this->popup = USI_WordPress_Solutions_Popup::build(
         array(
            'class'  => 'usi-wordpress-popup-phpinfo', // class for anchor;;
            'close'  => __('Close', USI_WordPress_Solutions::TEXTDOMAIN),
            'direct' => '.usi-wordpress-popup-phpinfo', // Elements with this class invoke popup;
            'height' => 600,
            'link'   => 'phpinfo()',
            'tip'    => __('Display PHP information', USI_WordPress_Solutions::TEXTDOMAIN),
            'title'  => 'phpinfo()',
            'type'   => 'iframe',
            'url'    => plugins_url(null, __FILE__) . '/usi-wordpress-solutions-phpinfo-scan.php',
            'width'  => 950,
         )
      );

      $sections = array(

         'preferences' => array(
            'header_callback' => array($this, 'sections_header'),
            'footer_callback' => array($this, 'sections_footer'),
            'label' => __('Sidebar Menu Sorting', USI_WordPress_Solutions::TEXTDOMAIN), 
            'localize_labels' => 'yes',
            'localize_notes' => 3, // <p class="description">__()</p>;
            'settings' => array(
               'menu-sort' => array(
                  'type' => 'radio', 
                  'label' => 'Settings Menu Sort Option',
                  'choices' => array(
                     array(
                        'value' => 'none', 
                        'label' => true, 
                        'notes' => __('No sorting', USI_WordPress_Solutions::TEXTDOMAIN), 
                        'suffix' => '<br/>',
                     ),
                     array(
                        'value' => 'alpha', 
                        'label' => true, 
                        'notes' => __('Alphabetical sorting selection', USI_WordPress_Solutions::TEXTDOMAIN), 
                        'suffix' => '<br/>',
                     ),
                     array(
                        'value' => 'usi', 
                        'label' => true, 
                        'notes' => __('Sort Universal Solutions settings and move to end of menu', USI_WordPress_Solutions::TEXTDOMAIN), 
                     ),
                  ),
                  'notes' => 'Defaults to <b>No sorting</b>.',
               ), // menu-sort;
            ),
         ), // preferences;

         'admin-options' => array(
            'label' => __('Administrator Options', USI_WordPress_Solutions::TEXTDOMAIN),
            'settings' => array(
               'git-update' => array(
                  'type' => 'checkbox', 
                  'label' => 'Enable Git Updates',
                  'notes' => 'Checks GitHub/GitLab for updates and notifies the administrator when updates are avaiable for download and installation.',
               ),
               'history' => array(
                  'type' => 'checkbox', 
                  'label' => 'Enable Historian',
                  'notes' => 'The system historian records user, configuration and update events in the system database.',
               ),
               'impersonate' => array(
                  'type' => 'checkbox', 
                  'label' => 'Enable User Switching',
                  'notes' => 'Enables administrators to impersonate another WordPress user.',
               ),
               'options_php' => array(
                  'type' => 'html', 
                  'html' => '<a href="options.php" title="Semi-secret settings on options.php page">options.php</a>',
                  'label' => 'Semi-Secret Settings',
               ),
            ),
         ), // admin-options;

         'diagnostics' => array(
            'label' => __('Diagnostics', USI_WordPress_Solutions::TEXTDOMAIN),
            'settings' => array(
               'phpinfo' => array(
                  'type' => 'html', 
                  'html' => $this->popup['anchor'],
                  'label' => 'Information',
               ),
               'visible-grid' => array(
                  'type' => 'checkbox', 
                  'label' => 'Visable Grid Borders',
               ),
            ),
         ), // diagnostics;

      );

      return($sections);

   } // sections();

   function sections_footer() {
      echo '    ';
      submit_button(__('Save Changes', USI_WordPress_Solutions::TEXTDOMAIN), 'primary', 'submit', true); 
      echo $this->popup['script'];
      return(null);
   } // sections_footer();

   function sections_header() {
      echo '    <p>' . __('The WordPress-Solutions plugin is used by many Universal Solutions plugins and themes to simplify the ' .
         'implementation of WordPress functionality. Additionally, you can place all of the Universal Solutions settings pages ' .
         'at the end of the Settings sub-menu, or you can sort the Settings sub-menu alphabetically or not at all.', 
          USI_WordPress_Solutions::TEXTDOMAIN) . '</p>' . PHP_EOL;
   } // sections_header();

} // Class USI_WordPress_Solutions_Settings_Settings;

new USI_WordPress_Solutions_Settings_Settings();

// --------------------------------------------------------------------------------------------------------------------------- // ?>