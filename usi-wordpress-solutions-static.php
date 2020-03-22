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

   const VERSION = '2.4.9 (2020-03-22)';

   private function __construct() {
   } // __construct();

   public static function action_admin_head($columns) {

      $hidden = get_hidden_columns(get_current_screen());

      foreach ($hidden as $hide) {
         unset($columns[$hide]);
      }

      $total = 0;
      foreach ($columns as $width) { 
         $total += $width;
      }

      echo '<style>' . PHP_EOL;

      foreach ($columns as $name => $width) { 
         $percent = number_format(100 * $width / $total, 1);
         echo '.wp-list-table .column-' . $name . '{border:solid red 1px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; width:' . 
            $percent . '%;}' . PHP_EOL;
      }

      echo '</style>' . PHP_EOL;

   } // action_admin_head();

} // Class USI_WordPress_Solutions_Static;

// --------------------------------------------------------------------------------------------------------------------------- // ?>