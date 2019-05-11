<?php // ------------------------------------------------------------------------------------------------------------------------ //

class USI_Settings_Versions_Scan {

   const VERSION = '1.2.0 (2018-01-13)';

   private function __construct() {
   } // __construct();

   private static function scan($path) {
      $files = scandir($path);
      $html  = '';
      foreach ($files as $file) {
         $full_path = $path . DIRECTORY_SEPARATOR . $file;
         if (('.' == $file) || ('..' == $file)) {
         } else if (is_dir($full_path)) {
            $html .= self::scan($full_path);
         } else {
            $contents = file_get_contents($full_path);
            $status   = preg_match('/VERSION\s*=\s*\'([(0-9\.\s\-\)]*)/', $contents, $matches);
            if (!empty($matches[1])) $html .= '<tr><td>' . $file . ' &nbsp; &nbsp; </td><td>' . $matches[1] . '</td></tr>';
         }
      }
      return($html);
   } // scan();

   public static function versions() {
      $files  = scandir(dirname(dirname(__FILE__)));
      die('<table>' . self::scan(dirname(dirname(__FILE__))) . '</table>');
   } // versions();

} // Class USI_Settings_Versions_Scan;

USI_Settings_Versions_Scan::versions();

// --------------------------------------------------------------------------------------------------------------------------- // ?>