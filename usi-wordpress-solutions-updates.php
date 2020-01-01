<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

class USI_WordPress_Solutions_Updates {

   const VERSION = '2.3.1 (2020-01-01)';

   private $text_domain = null;

   private function __construct($text_domain) {
      $this->text_domain = $text_domain;
   } // __construct();

   public function section_header() {
      echo '<p>' . __('GitHub is a code hosting platform for version control and collaboration. It is used to publish updates for this WordPress plugin.', $this->text_domain) . '</p>' . PHP_EOL;
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
                  'label' => 'Enable GitHub updates',
                  'notes' => 'Checks GitHub for updates and notifies the user when updates are avaiable for download and installation.',
               ),
            ),
         )
      );

   } // section();

} // Class USI_WordPress_Solutions_Updates;

// --------------------------------------------------------------------------------------------------------------------------- // ?>