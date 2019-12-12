<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

class USI_WordPress_Solutions_Versions {

   const VERSION = '2.2.0 (2019-12-11)';

   private function __construct() {
   } // __construct();

   public static function link($link_text, $title, $version, $text_domain, $file) {

      return('<a class="thickbox" href="' . plugins_url(null, __FILE__) . '/usi-wordpress-solutions-versions-scan.php' .
         '?' . urlencode($file) . '" title="' . 
         $title . ' &nbsp; &nbsp; Version ' . $version . '">' . $link_text . '</a>');

   } //link();

} // Class USI_WordPress_Solutions_Versions;

// --------------------------------------------------------------------------------------------------------------------------- // ?>