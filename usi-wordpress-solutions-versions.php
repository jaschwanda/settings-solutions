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

class USI_WordPress_Solutions_Versions {

   const VERSION = '2.3.2 (2020-01-08)';

   private function __construct() {
   } // __construct();

   public static function link($link_text, $title, $version, $text_domain, $file) {

      return('<a class="thickbox" href="' . plugins_url(null, __FILE__) . '/usi-wordpress-solutions-versions-scan.php' .
         '?' . urlencode($file) . '" title="' . 
         $title . ' &nbsp; &nbsp; Version ' . $version . '">' . $link_text . '</a>');

   } //link();

} // Class USI_WordPress_Solutions_Versions;

// --------------------------------------------------------------------------------------------------------------------------- // ?>