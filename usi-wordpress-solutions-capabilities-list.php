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

final class USI_WordPress_Solutions_Capabilities_List {

   const VERSION = '2.9.10 (2020-10-16)';

   private function __construct() {
   } // __construct();

   public static function list() {
      foreach ($_COOKIE as $key => $value) {
         if (substr($key, 0, 20) == 'wordpress_logged_in_') {
            $query = $_SERVER['QUERY_STRING'];
            die('<table>' . '<tr><td>' . $query . '</td></tr>' . '</table>');
         }
      }
      die('Accesss not allowed.');
   } // versions();

} // Class USI_WordPress_Solutions_Capabilities_List;

USI_WordPress_Solutions_Capabilities_List::list();

// --------------------------------------------------------------------------------------------------------------------------- // ?>