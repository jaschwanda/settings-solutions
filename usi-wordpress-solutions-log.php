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

// clean up subscripts;
// add log file;
// use try/catch;

final class USI {

   const VERSION = '2.9.0 (2020-07-30)';

   private static $info   = null;
   private static $mysqli = null;
   private static $mysqli_stmt = null;
   private static $offset = 0;
   private static $user   = 0;

   private function __construct() {
   } // __construct();

   public static function log() {
      $info = null;
      try {
         $trace = debug_backtrace();
         if (!empty($trace[self::$offset+0])) {
            if (empty($trace[self::$offset+1])) {
               $info .= $trace[self::$offset+0]['file'];
            } else {
            // $info .= !empty($trace[self::$offset+1]['class']) ? $trace[self::$offset+1]['class'] . ':' : $trace[self::$offset+1]['file'];
               $info .= !empty($trace[self::$offset+1]['class']) ? $trace[self::$offset+1]['class'] . ':' : $trace[self::$offset+0]['file'];
               if (!empty($trace[self::$offset+1]['function'])) {
                  switch ($trace[self::$offset+1]['function']) {
                  case 'include':
                  case 'include_once':
                  case 'require':
                  case 'require_once':
                     break;
                  default:
                     $info .= ':' . $trace[self::$offset+1]['function'] . '()';
                  }
               }
            }
            if (!empty($trace[self::$offset+0]['line'])) $info .= '~' . $trace[self::$offset+0]['line'] . ':';
         }
         if (isset($trace[self::$offset/2+0]['args'])) {
            $args = $trace[self::$offset/2+0]['args'];
            foreach ($args as $arg) {
               if (is_array($arg) || is_object($arg)) {
                  $info .= print_r($arg, true);
               } else if (is_string($arg)) {
                  $first = substr($arg, 0, 1);
                  if ('\\' == $first) {
                     $second = substr($arg, 1, 1);
                     if ('!' == $second) {
                        $info = substr($arg, 1);
                     } else if ('n' == $second) {
                        $info .= PHP_EOL . substr($arg, 2);
                     } else if ('%' == $second) {
                        $info .= PHP_EOL . 'backtrace=' . print_r($trace, true) . PHP_EOL;
                     } else if ('2n' == substr($arg, 1, 2)) {
                        $info .= PHP_EOL . PHP_EOL . substr($arg, 3);
                     }
                  } else {
                     $info .= $arg;
                  }
               } else {
                  $info .= $arg;
               }
            }
         }
      } catch (Exception $e) {
         $info .= PHP_EOL . 'exception=' . $e->GetMessage();
      }

      if (!self::$mysqli) {
         self::$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
         self::$mysqli_stmt = new mysqli_stmt(self::$mysqli);
         self::$mysqli_stmt->prepare('INSERT INTO `' . DB_WP_PREFIX . 'USI_log` (`user_id`, `action`) VALUES (?, ?)');     
         self::$mysqli_stmt->bind_param('is', self::$user, self::$info);
      }
      self::$info = substr($info, 0, 65535);
      self::$user = get_current_user_id();
      self::$mysqli_stmt->execute();

   } // log();

   public static function log2() {
      self::$offset = 2;
      self::log();
      self::$offset = 0;
   } // log2();

} // Class USI;

// --------------------------------------------------------------------------------------------------------------------------- // ?>