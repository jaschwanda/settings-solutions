<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

require_once('usi-wordpress-solutions-versions-all.php');

class USI_WordPress_Solutions_Versions_Show {

   const VERSION = '2.9.9 (2020-09-27)';

   public static function parse($expression) {

      $lines   = explode(PHP_EOL, $expression);

      $package = null;

      $site    = array();

      foreach ($lines as $line) {

         $tokens = explode(':', $line);

         if (empty($site['title'])) {

            $site['title'] = $tokens[0];

         } else {

            if (3 != count($tokens)) continue;

            $site['packages'][$tokens[0]][$tokens[1]] = $tokens[2];

         }

      }

      return($site);

   } // parse();

   public static function show($import_expression = null) {

      $title = sanitize_title(get_bloginfo('title'));

      $site  = self::parse(USI_WordPress_Solutions_Versions_All::versions($title, WP_CONTENT_DIR));

      if ($import_expression) {
         $import_site = self::parse($import_expression);
         $first_title = $import_site['title'];
         $sites       = array($first_title => $import_site, $site['title'] => $site);
      } else {
         $first_title = $site['title'];
         $sites       = array($first_title => $site);
      }

      $html  = '<table border="1" cellpadding="2" style="margin:20px 0 0 -220px;">' . PHP_EOL . 
               '  <tr><td>Function</td>';
      foreach ($sites as $site) {
         $html .= '<td>' . $site['title'] . '</td>';
      }
      $html .= '</tr>' . PHP_EOL;

      foreach ($sites[$first_title]['packages'] as $package_name => $package) {

         $html .= '  <tr><td>' . $package_name . '</td><td colspan="' . count($sites) . '"></td></tr>' . PHP_EOL;

         foreach ($package as $file => $version) {
            $html .= '  <tr><td>' . $file . '</td><td>' . $version . '</td>';

            foreach ($sites as $site_title => $site) {
               if ($site_title == $first_title) continue;
               $other_version = !empty($site['packages'][$package_name][$file]) ? $site['packages'][$package_name][$file] : null;
               $html         .= '<td' . ($other_version != $version ? ' style="color:red;"' : '') . '>' . ($other_version ? $other_version : 'missing') . '</td>';
               unset($site['packages'][$package_name][$file]);
            }

            $html .= '</tr>' . PHP_EOL;
         }

         $html .= '  <tr><td>&nbsp;</td><td></td>';

         foreach ($sites as $site_title => $site) {
            if ($site_title == $first_title) continue;
            $html .= '<td style="color:red;">';
            if (!empty($sites[$site_title][$package_name])) {
               foreach ($sites[$site_title][$package_name] as $file => $version) {
                  $html .= $file . '<br/>';
               }
            }
            $html .= '</td>';
         }

         $html .= '</tr>' . PHP_EOL;

      }

      return($html . '</table>' . PHP_EOL);

   } // show();

} // USI_WordPress_Solutions_Versions_Show();

// --------------------------------------------------------------------------------------------------------------------------- // ?>