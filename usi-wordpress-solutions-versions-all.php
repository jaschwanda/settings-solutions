<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

class USI_WordPress_Solutions_Versions_All {

   const VERSION = '2.9.10 (2020-10-16)';

   private static function scan(& $lines, $level, $path, $parent = null) {
      $offset  = strrpos($path, DIRECTORY_SEPARATOR) + 1;
      $package = substr($path, $offset);
      $files   = scandir($path);
      if (strpos($path, '.git')) return($lines);
      if (3 == $level) {
         $prefix  = explode('-', $package);
         if (('ru' != $prefix[0]) && ('theme' != $prefix[0]) && ('usi' != $prefix[0])) return($lines);
      }
      foreach ($files as $file) {
         $full_path = $path . DIRECTORY_SEPARATOR . $file;
         if (('.' == $file) || ('..' == $file)) {
         } else if (is_dir($full_path)) {
            self::scan($lines, $level + 1, $full_path, $package);
         } else {
            $contents = file_get_contents($full_path);
            $status   = preg_match('/(V|v)(E|e)(R|r)(S|s)(I|i)(O|o)(N|n)\s*(=|:)\s*(\')?([(0-9\.\s\-\)]*)/', $contents, $matches);
            if (!empty($matches[10])) {
               $version = trim($matches[10]);
               if ('0	' == $version) $version = '';
               if ('config' == substr($file, 0, 6)) $file = $path;
               $lines  .= $package . ':' . $file . ':' . $version . PHP_EOL;
            }
         }
      }
      return($lines);
   } // scan();

   public static function versions($title, $path) {
      $lines = $title . PHP_EOL;
      return(self::scan($lines, 1, $path) . PHP_EOL);
   } // versions();

} // USI_WordPress_Solutions_Versions_All();

// --------------------------------------------------------------------------------------------------------------------------- // ?>