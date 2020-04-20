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

class USI_WordPress_Solutions_Static {

   const VERSION = '2.4.12 (2020-04-19)';

   private function __construct() {
   } // __construct();

   public static function column_style($columns, $style = null) {

      $border = !empty(USI_WordPress_Solutions::$options['diagnostics']['visible-grid']) ? 'border:solid 1px yellow; ' : '';

      $space  = $style ? ' ' : '';

      $hidden = get_hidden_columns(get_current_screen());

      foreach ($hidden as $hide) unset($columns[$hide]);

      $total = 0; foreach ($columns as $width) $total += $width;

      $html  = '<style>' . PHP_EOL;

      foreach ($columns as $name => $width) { 
         $percent = number_format(100 * $width / $total, 1);
         $html   .= ".wp-list-table .column-$name{{$border}width:$percent%;$space$style}" . PHP_EOL;
      }

      return($html . '</style>' . PHP_EOL);

   } // column_style();

   public static function action_admin_head($css = null) {
      echo 
         '<style>' . PHP_EOL .
         '.form-table td{padding-bottom:2px; padding-top:2px;} /* 15px; */' . PHP_EOL .
         '.form-table th{padding-bottom:7px; padding-top:7px;} /* 20px; */' . PHP_EOL .
         'h2{margin-bottom:0.1em; margin-top:2em;} /* 1em; */' . PHP_EOL;
      if ($css) echo $css;
      if (USI_WordPress_Solutions::$options['diagnostics']['visible-grid']) echo
         '.form-table{border:solid 4px yellow;}' . PHP_EOL .
         '.form-table td,.form-table th{border:solid 2px yellow;}' . PHP_EOL .
         '.wrap{border:solid 1px green;}' . PHP_EOL;
      echo 
         '</style>' . PHP_EOL;
   } // action_admin_head();

   public static function divider($indent, $text = null) {
      if ($length = strlen($text)) {
         $text    = ' ' . $text . ' ';
         $length += 2;
      }
      return('<!--' . $text . str_repeat('-', 121 - $length - $indent) . '>' . PHP_EOL);
   } // divider();

} // Class USI_WordPress_Solutions_Static;

// --------------------------------------------------------------------------------------------------------------------------- // ?>