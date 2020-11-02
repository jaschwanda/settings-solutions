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

require_once('usi-wordpress-solutions-popup-iframe.php');

class USI_WordPress_Solutions_Versions {

   const VERSION = '2.10.1 (2020-11-02)';

   private static $built = false;

   private function __construct() {
   } // __construct();

   public static function link($link, $title, $version, $text_domain, $file) {

      if (!self::$built) {

         USI_WordPress_Solutions_Popup_Iframe::build(
            array(
               'close'   => __('Close', $text_domain),
               'height' => '500px',
               'id'     => 'usi-popup-version',
               'width'  => '500px',
            )
         );

         self::$built = true;

      }

      return(
         USI_WordPress_Solutions_Popup_Iframe::link(
            array(
               'id'     => 'usi-popup-version',
               'iframe' => plugins_url(null, __FILE__) . '/usi-wordpress-solutions-versions-scan.php?' . urlencode($file),
               'link'   => array('text' => $link),
               'tip'    => __('Display detailed version information', $text_domain),
               'title'  => $title . ' &nbsp; &nbsp; ' . __('Version', $text_domain) . ' ' . $version,
            )
         )
      );

   } //link();

} // Class USI_WordPress_Solutions_Versions;

// --------------------------------------------------------------------------------------------------------------------------- // ?>