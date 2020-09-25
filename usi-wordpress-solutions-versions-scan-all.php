<?php // ------------------------------------------------------------------------------------------------------------------------ //

/*
WordPress-Solutions is free software: you can redistribute it and/or modify it under the terms of the GNU General Public 
License as published by the Free Software Foundation, either version 3 of the License, or any later version.
 
WordPress-Solutions is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied 
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License along with WordPress-Solutions. If not, see 
https://github.com/jaschwanda/wordpress-solutions/blob/master/LICENSE.md

Copyright (c) 2020 by Jim Schwanda.
*/

final class USI_WordPress_Solutions_Versions_Scan_All {

   const VERSION = '2.9.9 (2020-09-25)';

   private function __construct() {
   } // __construct();

   private static function scan($level, $path, $parent, $out) {
      static $first = true;
      $offset  = strrpos($path, DIRECTORY_SEPARATOR) + 1;
      $package = substr($path, $offset);
      $files   = scandir($path);
      $html    = '';
      $json    = '';
      if (1 == $level) {
         $html    = '  <tr><td colspan="2">' . gethostname() . '</td></tr>' . PHP_EOL;
         $json    = '{' . PHP_EOL . '   "name":"' . gethostname() . '",' . PHP_EOL . '   "packages":[' . PHP_EOL;;
      }
      if (strpos($path, '.git')) return($out ? $html : $json);
      if (3 == $level) {
         $prefix  = explode('-', $package);
         if (('ru' != $prefix[0]) && ('theme' != $prefix[0]) && ('usi' != $prefix[0])) return($out ? $html : $json);
         $html    = '  <tr><td colspan="2">' . $package . '</td></tr>' . PHP_EOL;
         if ($first) {
            $first = false;
         } else {
            $json .= '         ],' . PHP_EOL . '      },' . PHP_EOL;
         }
         $json   .= '      {' . PHP_EOL . '         "name":"' . $package . '",' . PHP_EOL . '         "files":[' . PHP_EOL;
      }
      foreach ($files as $file) {
         $full_path = $path . DIRECTORY_SEPARATOR . $file;
         if (('.' == $file) || ('..' == $file)) {
         } else if (is_dir($full_path)) {
            if ($out) {
               $html .= self::scan($level + 1, $full_path, $package, $out);
            } else {
               $json .= self::scan($level + 1, $full_path, $package, $out);
            }
         } else {
            $contents = file_get_contents($full_path);
            $status   = preg_match('/(V|v)(E|e)(R|r)(S|s)(I|i)(O|o)(N|n)\s*(=|:)\s*(\')?([(0-9\.\s\-\)]*)/', $contents, $matches);
            if (!empty($matches[10])) {
               $version = str_replace(array(PHP_EOL, "\n", "\n"), '', $matches[10]);
               if ('0	' == $version) $version = '';
               if ('config' == substr($file, 0, 6)) $file = $path;
               $html .= '  <tr><td>' . $file . ' &nbsp; &nbsp; </td><td>' . $version . '</td></tr>' . PHP_EOL;
               $json .= '            {"file":"' . $file . '", "version":"' . $version . '"},' . PHP_EOL;
            }
         }
      }
      return($out ? $html : $json);
   } // scan();

   public static function versions() {
      $style = '<style>td{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,' .
         '"Helvetica Neue",sans-serif; font-size: 13px;}</style>' . PHP_EOL;
      foreach ($_COOKIE as $key => $value) {
         if (substr($key, 0, 20) == 'wordpress_logged_in_') {
            $fmt = false;
            $out = '';
            if ($fmt) {
               $out = $style . '<table>' . PHP_EOL;
            } else {
            }
//          $out .= self::scan(1, 'C:\Users\jas473\Documents\Applications\WordPress', '', $fmt);
            $out .= self::scan(1, explode('?', urldecode($_SERVER['QUERY_STRING']))[0], '', $fmt);
            if ($fmt) {
               $out .= '</table>' . PHP_EOL;
            } else {
               $out .= '         ],' . PHP_EOL . '      },' . PHP_EOL . '   ],' . PHP_EOL . '},' . PHP_EOL;
            }
            die($out);
         }
      }
      die('Accesss not allowed.');
   } // versions();

} // Class USI_WordPress_Solutions_Versions_Scan_All;

USI_WordPress_Solutions_Versions_Scan_All::versions();

// --------------------------------------------------------------------------------------------------------------------------- // ?>