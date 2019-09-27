<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

require_once(plugin_dir_path(__DIR__) . 'usi-wordpress-solutions/usi-wordpress-solutions-settings.php');
require_once(plugin_dir_path(__DIR__) . 'usi-wordpress-solutions/usi-wordpress-solutions-versions.php');

class USI_WordPress_Solutions_Settings_Settings extends USI_WordPress_Solutions_Settings {

   const VERSION = '2.1.4 (2019-09-26)';

   function __construct() {

      parent::__construct(
         USI_WordPress_Solutions::NAME, 
         USI_WordPress_Solutions::PREFIX, 
         USI_WordPress_Solutions::TEXTDOMAIN,
         USI_WordPress_Solutions::$options
      );

      // $this->debug('usi_log');

   } // __construct();

   function config_section_footer() {
      submit_button(__('Save Changes', USI_WordPress_Solutions::TEXTDOMAIN), 'primary', 'submit', true); 
      return(null);
   } // config_section_footer();

   function config_section_header() {
      echo '<p>' . __('The WordPress-Solutions plugin is used by many Universal Solutions plugins and themes to simplify the ' .
         'implementation of WordPress functionality. Additionally, you can place all of the Universal Solutions settings pages ' .
         'at the end of the Settings sub-menu, or you can sort the Settings sub-menu alphabetically or not at all.', 
          USI_WordPress_Solutions::TEXTDOMAIN) . '</p>' . PHP_EOL;
   } // config_section_header();

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

      $sections = array(

         'preferences' => array(
            'header_callback' => array($this, 'config_section_header'),
            'footer_callback' => array($this, 'config_section_footer'),
            'label' => __('Sidebar Menu Sorting', USI_WordPress_Solutions::TEXTDOMAIN), 
            'settings' => array(
               'menu-sort' => array(
                  'type' => 'radio', 
                  'label' => 'Settings menu sort option',
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
               'impersonate' => array(
                  'type' => 'checkbox', 
                  'label' => 'Enable user switching',
                  'notes' => 'Enables administrators to impersonate another WordPress user.',
               ),
            ),
         ), // admin-options;

      );

      foreach ($sections as $name => & $section) {
         foreach ($section['settings'] as $name => & $setting) {
            if (!empty($setting['label'])) $setting['label'] = __($setting['label'], USI_WordPress_Solutions::TEXTDOMAIN);
            if (!empty($setting['notes'])) $setting['notes'] = '<p class="description">' . 
               __($setting['notes'], USI_WordPress_Solutions::TEXTDOMAIN) . '</p>';
         }
      }
      unset($setting);

      return($sections);

   } // sections();

} // Class USI_WordPress_Solutions_Settings_Settings;

new USI_WordPress_Solutions_Settings_Settings();

// --------------------------------------------------------------------------------------------------------------------------- // ?>