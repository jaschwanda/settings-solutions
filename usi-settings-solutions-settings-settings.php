<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

require_once(plugin_dir_path(__DIR__) . 'usi-settings-solutions/usi-settings-solutions-settings.php');
require_once(plugin_dir_path(__DIR__) . 'usi-settings-solutions/usi-settings-solutions-versions.php');

class USI_Settings_Solutions_Settings_Settings extends USI_Settings_Solutions_Settings {

   const VERSION = '1.0.8 (2018-01-10)';

   protected $is_tabbed = true;

   function __construct() {

      $this->sections = array(

         'preferences' => array(
            'header_callback' => array($this, 'config_section_header_preferences'),
            'label' => 'Preferences',
            'settings' => array(
               'variable-prefix' => array(
                  'class' => 'regular-text', 
                  'type' => 'text', 
                  'label' => 'Variable prefix',
                  'notes' => 'Enter lower case text.',
               ),
               'file-location' => array(
                  'type' => 'radio', 
                  'label' => 'Location of variables.php file',
                  'choices' => array(
                     array(
                        'value' => 'plugin', 
                        'label' => true, 
                        'notes' => __('Plugin folder', USI_Settings_Solutions::TEXTDOMAIN), 
                        'suffix' => ' &nbsp; &nbsp; &nbsp; ',
                     ),
                     array(
                        'value' => 'root', 
                        'label' => true, 
                        'notes' => __('WordPress wp-config.php folder', USI_Settings_Solutions::TEXTDOMAIN), 
                     ),
                  ),
                  'notes' => 'Defaults to <b>Plugin folder</b>.',
               ), // file-location;
            ),
         ), // preferences;

         'publish' => array(
         // 'footer_callback' => array($this, 'config_section_footer'), // Only to test no tabbing;
            'label' => 'Publish',
            'settings' => array(
               'explaination' => array(
                  'class' => 'regular-text', 
                  'type' => 'textarea', 
                  'label' => 'Explaination',
                  'notes' => __('Enter up to 255 printable characters.', USI_Settings_Solutions::TEXTDOMAIN), 
               ),
            ),
            'submit' => __('Publish Variables', USI_Settings_Solutions::TEXTDOMAIN),
         ), // publish;

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

   /* This function is here only to test the no tabbing settings option;
   function config_section_footer() {
      submit_button(__('Single Save', USI_Settings_Solutions::TEXTDOMAIN), 'primary', 'submit', true); 
      return(null);
   } // config_section_footer();
   */

   function config_section_header_preferences() {
      echo '<p>' . __('Changing these settings after the system is in use may cause referencing errors. Make sure that you also change the <b>[ID attribute="value"]</b> shortcodes in your content and the <b>defined(variable, "value")</b> statments in your PHP files to match the settings you enter here.', USI_Settings_Solutions::TEXTDOMAIN) . '</p>' . PHP_EOL;
   } // config_section_header_preferences();

   function fields_sanitize($input) {
      usi_log(__METHOD__.':input=' . print_r($input, true));
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