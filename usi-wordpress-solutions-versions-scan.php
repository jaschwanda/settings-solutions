<?php // ------------------------------------------------------------------------------------------------------------------------ //

final class USI_WordPress_Solutions_Versions_Scan {

   const VERSION = '2.2.0 (2019-12-11)';

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
            $status   = preg_match('/(V|v)(E|e)(R|r)(S|s)(I|i)(O|o)(N|n)\s*(=|:)\s*(\')?([(0-9\.\s\-\)]*)/', $contents, $matches);
            if (!empty($matches[10])) $html .= '<tr><td>' . $file . ' &nbsp; &nbsp; </td><td>' . $matches[10] . '</td></tr>';
         }
      }
      return($html);
   } // scan();

   public static function versions() {
      die('<table>' . self::scan(explode('&', urldecode($_SERVER['QUERY_STRING']))[0]) . '</table>');
   } // versions();

} // Class USI_WordPress_Solutions_Versions_Scan;

USI_WordPress_Solutions_Versions_Scan::versions();

// --------------------------------------------------------------------------------------------------------------------------- // ?>