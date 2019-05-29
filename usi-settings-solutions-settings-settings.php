<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

require_once(plugin_dir_path(__DIR__) . 'usi-settings-solutions/usi-settings-solutions-settings.php');
require_once(plugin_dir_path(__DIR__) . 'usi-settings-solutions/usi-settings-solutions-versions.php');

class USI_Settings_Solutions_Settings_Settings extends USI_Settings_Solutions_Settings {

   const VERSION = '1.0.8 (2018-01-10)';

   function __construct() {

      $this->sections = array(

         'preferences' => array(
            'header_callback' => array($this, 'config_section_header'),
            'footer_callback' => array($this, 'config_section_footer'),
            //'label' => 'Preferences',
            'settings' => array(
               'menu-sort' => array(
                  'type' => 'radio', 
                  'label' => 'Settings menu sort option',
                  'choices' => array(
                     array(
                        'value' => 'none', 
                        'label' => true, 
                        'notes' => __('No sorting', USI_Settings_Solutions::TEXTDOMAIN), 
                        'suffix' => ' &nbsp; &nbsp; &nbsp; ',
                     ),
                     array(
                        'value' => 'custom', 
                        'label' => true, 
                        'notes' => __('Custom sorting selection', USI_Settings_Solutions::TEXTDOMAIN), 
                        'suffix' => ' &nbsp; &nbsp; &nbsp; ',
                     ),
                     array(
                        'value' => 'usi', 
                        'label' => true, 
                        'notes' => __('Sort Universal Solutions settings and move to end of menu', USI_Settings_Solutions::TEXTDOMAIN), 
                     ),
                  ),
                  'notes' => 'Defaults to <b>No sorting</b>.',
               ), // menu-sort;
               'regexp' => array(
                  'class' => 'regular-text', 
                  'type' => 'text', 
                  'label' => 'Selection regular expression',
                  'notes' => 'Enter regular expression to select and sort settings menu items.',
               ),
            ),
         ), // preferences;

      );

      foreach ($this->sections as $name => & $section) {
         foreach ($section['settings'] as $name => & $setting) {
            if (!empty($setting['notes']))
               $setting['notes'] = '<p class="description">' . __($setting['notes'], USI_Settings_Solutions::TEXTDOMAIN) . '</p>';
         }
      }
      unset($setting);

      parent::__construct(
         USI_Settings_Solutions::NAME, 
         USI_Settings_Solutions::PREFIX, 
         USI_Settings_Solutions::TEXTDOMAIN,
         USI_Settings_Solutions::$options
      );

      // $this->debug('usi_log');

      add_filter('plugin_row_meta', array($this, 'filter_plugin_row_meta'), 10, 2);

   } // __construct();

   function config_section_footer() {
      submit_button(__('Save Changes', USI_Settings_Solutions::TEXTDOMAIN), 'primary', 'submit', true); 
      return(null);
   } // config_section_footer();

   function config_section_header() {
      echo '<p>' . __('The Settings-Solutions plugin is used by many Universal Solutions plugins and themes to simplify the ' .
         'implementation and use of WordPress settings. It can sort the settings pages of all Universal Solutions plugins ' .
         'and place them at the end of the Settings menu, or you can create a custom subset of settings pages that are placed ' .
         'at the end of the Settings menu or you can disable sorting completely.', USI_Settings_Solutions::TEXTDOMAIN) . '</p>' . PHP_EOL;
   } // config_section_header();

   function fields_sanitize($input) {
      if ('usi' == $input['preferences']['menu-sort']) {
         $input['preferences']['regexp'] = '/^usi\-\w+-settings/';
      } else if ('none' == $input['preferences']['menu-sort']) {
         $input['preferences']['regexp'] = '';
      }
      return($input);
   } // fields_sanitize();

   function filter_plugin_row_meta($links, $file) {
      if (false !== strpos($file, USI_Settings_Solutions::TEXTDOMAIN)) {
         $links[0] = USI_Settings_Solutions_Versions::link(
            $links[0], // Original link text;
            USI_Settings_Solutions::NAME, // Title;
            USI_Settings_Solutions::VERSION, // Version;
            USI_Settings_Solutions::TEXTDOMAIN, // Text domain;
            __DIR__ // Folder containing plugin or theme;
         );
         $links[] = '<a href="https://www.usi2solve.com/donate/settings-solutions" target="_blank">' . 
            __('Donate', USI_Settings_Solutions::TEXTDOMAIN) . '</a>';
      }
      return($links);
   } // filter_plugin_row_meta();

} // Class USI_Settings_Solutions_Settings_Settings;

new USI_Settings_Solutions_Settings_Settings();

// --------------------------------------------------------------------------------------------------------------------------- // ?>