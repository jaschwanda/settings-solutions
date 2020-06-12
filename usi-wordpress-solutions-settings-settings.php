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

require_once('usi-wordpress-solutions-capabilities.php');
require_once('usi-wordpress-solutions-diagnostics.php');
require_once('usi-wordpress-solutions-popup.php');
require_once('usi-wordpress-solutions-settings.php');
require_once('usi-wordpress-solutions-updates.php');
require_once('usi-wordpress-solutions-versions.php');

class USI_WordPress_Solutions_Settings_Settings extends USI_WordPress_Solutions_Settings {

   const VERSION = '2.7.0 (2020-06-08)';

   protected $is_tabbed = true;

   private $popup = array();

   function __construct() {

      parent::__construct(
         array(
            'name' => USI_WordPress_Solutions::NAME, 
            'prefix' => USI_WordPress_Solutions::PREFIX, 
            'text_domain' => USI_WordPress_Solutions::TEXTDOMAIN,
            'options' => USI_WordPress_Solutions::$options,
            'capabilities' => USI_WordPress_Solutions::$capabilities,
            'file' => str_replace('-settings', '', __FILE__), // Plugin main file, this initializes capabilities on plugin activation;
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

      $this->popup['php-info'] = USI_WordPress_Solutions_Popup::build(
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

      $diagnostics = new USI_WordPress_Solutions_Diagnostics($this, 
         array(
            'DEBUG_INIT' => array(
               'value' => USI_WordPress_Solutions::DEBUG_INIT,
               'notes' => 'Log USI_WordPress_Solutions_Settings::action_admin_init() method.',
            ),
            'DEBUG_RENDER' => array(
               'value' => USI_WordPress_Solutions::DEBUG_RENDER,
               'notes' => 'Log USI_WordPress_Solutions_Settings::fields_render() method.',
            ),
         )
      );

      $diagnostics->section['footer_callback'] = array($this, 'sections_diagnostics_footer');

      $sections = array(

         'preferences' => array(
            'header_callback' => array($this, 'sections_header'),
            'label' => __('Preferences', USI_WordPress_Solutions::TEXTDOMAIN), 
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
               'admin-notice' => array(
                  'f-class' => 'large-text', 
                  'rows' => 2,
                  'type' => 'textarea', 
                  'label' => 'Admin Notice',
               ),
            ),
         ), // preferences;

         'admin-options' => array(
            'title' => __('Administrator Options', USI_WordPress_Solutions::TEXTDOMAIN),
            'not_tabbed' => 'preferences',
            'settings' => array(
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

         'capabilities' => new USI_WordPress_Solutions_Capabilities($this),

         'diagnostics' => $diagnostics,

         'illumination' => array(
            'title' => 'Illumination',
            'not_tabbed' => 'diagnostics',
            'settings' => array(
               'php-info' => array(
                  'type' => 'html', 
                  'html' => $this->popup['php-info']['anchor'],
                  'label' => 'Information',
               ),
               'active-users' => array(
                  'type' => 'html', 
                  'html' => '<a href="admin.php?page=usi-wordpress-solutions-user-sessions">Users Logged In</a>',
                  'label' => 'Users Currently Logged In',
               ),
               'visible-grid' => array(
                  'type' => 'checkbox', 
                  'label' => 'Visable Grid Borders',
               )
            ),
         ), // illumination;

         'updates' => new USI_WordPress_Solutions_Updates($this),

      );

      return($sections);

   } // sections();

   function sections_diagnostics_footer() {
      echo '    ';
      submit_button(__('Save Diagnostics', USI_WordPress_Solutions::TEXTDOMAIN), 'primary', 'submit', true); 
      echo $this->popup['php-info']['script'];
      return(null);
   } // sections_diagnostics_footer();

   function sections_header() {
      echo '    <p>' . __('The WordPress-Solutions plugin is used by many Universal Solutions plugins and themes to simplify the ' .
         'implementation of WordPress functionality. Additionally, you can place all of the Universal Solutions settings pages ' .
         'at the end of the Settings sub-menu, or you can sort the Settings sub-menu alphabetically or not at all.', 
          USI_WordPress_Solutions::TEXTDOMAIN) . '</p>' . PHP_EOL;
   } // sections_header();

} // Class USI_WordPress_Solutions_Settings_Settings;

new USI_WordPress_Solutions_Settings_Settings();

// --------------------------------------------------------------------------------------------------------------------------- // ?>