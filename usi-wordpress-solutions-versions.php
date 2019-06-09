<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

class USI_WordPress_Solutions_Versions {

   const VERSION = '2.1.0 (2019-06-08)';

   private function __construct() {
      // Enqueues the default ThickBox js and css;
      add_action('init', 'add_thickbox');
   } // __construct();

   public static function link($link_text, $title, $version, $text_domain, $file) {

      $id = 'usi-wordpress-versions-' . $title;

      $ajax = plugins_url(null, __FILE__) . '/usi-wordpress-solutions-versions-scan.php';

      return(
         '<a id="' . $id . '-link" class="thickbox" href="">' . $link_text . '</a>' . 
         '<div id="' . $id . '-popup" style="display:none;">' .  
           '<p id="' . $id . '-list"></p>' . 
           '<hr>' . 
           '<p>' . 
             '<a class="button" href="" onclick="tb_remove()">' . __('Close', $text_domain) . '</a>' . 
           '</p>' . 
         '</div>' .  
         '<script>' . 
         'jQuery(document).ready(' . 
            'function($) {' . 
               'function resize() {' . 
                  'var padding_left = $("#TB_ajaxContent").css("padding-left");' .
                  'var padding = padding_left.substring(0, padding_left.length - 2);' .
                  'var width = $("#TB_window").width() - 2 * padding;' . 
                  'var height = $("#TB_window").height() - 75;' . 
                  "$('#TB_ajaxContent').css({'width' : width, 'height' : height});" .
               '}' .
               '$("#' . $id . '-link").click(' . 
                  'function(event) {' . 
                     'tb_show("' . $title . ' &nbsp; &nbsp; Version ' . $version . ' ", "#TB_inline?inlineId=' . $id . '-popup", null);' . 
                     '$("#' . $id . '-list").load("' . $ajax . '", "' . urlencode($file) . '", resize);' . 
                     'return(false);' . 
                  '}' . 
               ');' . 
               '$(window).resize(resize);' .
            '}' . 
         ');' . 
         '</script>');
   } //link();

} // Class USI_WordPress_Solutions_Versions;

// --------------------------------------------------------------------------------------------------------------------------- // ?>