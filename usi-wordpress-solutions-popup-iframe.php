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

/*
class USI_WordPress_Solutions_Popup_Iframe is used in:

ru-email-inbox.php
usi-test-solutions-settings.php
usi-wordpress-solutions-versions.php
usi-wordpress-solutions-settings-settings.php
*/

class USI_WordPress_Solutions_Popup_Iframe {

   const VERSION = '2.10.1 (2020-11-02)';

   private static $attributes = array();
   private static $scripts    = array();

   private function __construct() {
   } // __construct();

   public static function build($options) {

      if (!empty($options['id'])) {
         $id       = $options['id'];
      } else {
         $id       = 'usi-popup';
      }

      self::$attributes[$id] = ' href="javascript:void(0);"';

      if (empty($options['height'])) {
         $size     = ' height:300px;';
         $frame    = 202;
         self::$attributes[$id] .= ' usi-popup-height="' . $frame . '"';
      } else {
         $height   = explode(',', $options['height']);
         if (1 == count($height)) {
            $size  = ' height:' . $height[0] . ';';
            $frame = (int)substr($height[0], 0, 3) - 98;
            self::$attributes[$id] .= ' usi-popup-height="' . $frame . '"';
         } else {
            $size  = ' min-height:' . $height[0] . '; max-height:' . $height[1] . ';';
         }
      }

      if (empty($options['width'])) {
         $size    .= ' width:300px;';
      } else {
         $width    = explode(',', $options['width']);
         if (1 == count($width)) {
            $size .= ' width:' . $width[0] . ';';
         } else {
            $size .= ' min-width:' . $width[0] . '; max-width:' . $width[1] . ';';
         }
      }

      $close       = !empty($options['close'] ) ?          $options['close']   : null;

      if (empty(self::$scripts[$id])) { // IF popup html not set;

         if (empty(self::$scripts[0])) { // IF popup javascript not set;

            $divider = USI_WordPress_Solutions_Static::divider(0, $id);

            self::$scripts[0] = <<<EOD
$divider<script> 
jQuery(document).ready(
   function($) {

   // Close Popup with cancel/close/delete/ok button;
   $('[usi-popup-close]').click(
      function() {
         var id = $(this).attr('usi-popup-close');
         $('#' + id).fadeOut(300);
      }
   );

   // Close with outside click;
   $('[usi-popup-close-outside]').click(
      function() {
         var id = $(this).find('[usi-popup-close]').attr('usi-popup-close');
         $('#' + id).fadeOut(300);
      }
   )
   .children()
   .click(
      function() {
         return(false);
      }
   );

   // Invoke popup
   $('[usi-popup-open]').click(
      function() {
         var id     = $(this).attr('usi-popup-open');
         var height = $(this).attr('usi-popup-height');
         var iframe = $(this).attr('usi-popup-iframe');
         var title  = $(this).attr('usi-popup-title');
         $('#' + id + '-title').html(title);
         $('#' + id + '-body').html('<iframe src="' + iframe + '" height="' + height + '" width="100%"></iframe>');
         $('#' + id).fadeIn(300);
      }
   );

   } // function();
);
</script>
$divider
EOD;

            USI_WordPress_Solutions::admin_footer_script(self::$scripts[0]);

         } // ENDIF popup javaascript not set;

// The {$id}-head div is equivalent to the WordPress thickbox TB_title div;
// The {$id}-title div is equivalent to the WordPress thickbox TB_ajaxWindowTitle div;
         $divider = USI_WordPress_Solutions_Static::divider(0, $id);
         self::$scripts[$id] = <<<EOD
{$divider}<div id="{$id}" usi-popup-close-outside="{$id}" style="background:rgba(0,0,0,0.7); display:none; height:100%; left:0; position:fixed; top:0; width:100%; z-index:100050;">
  <div id="{$id}-wrap" style="background:#ffffff; box-sizing:border-box; left:50%; position:relative; top:50%; transform:translate(-50%,-50%); {$size}">
    <div id="{$id}-head" style="background:#fcfcfc; border-bottom:1px solid #ddd; height:29px;">
      <div id="{$id}-title" style="float:left; font-weight:600; line-height:29px; overflow:hidden; padding:0 29px 0 10px; text-overflow:ellipsis; white-space: nowrap; width:calc(100%-39px);"></div>
        <button type="button" style="background:#fcfcfc; border:solid 1px #00a0d2; color:#00a0d2; cursor:pointer; height:29px; position:absolute; right:0; top:0;" usi-popup-action="close" usi-popup-close="{$id}" >
          <span class="screen-reader-text">{$close}</span>
          <span class="dashicons dashicons-no"></span>
        </button>
    </div><!--{$id}-head-->
    <div id="{$id}-body" style="border-bottom:1px solid #ddd;"></div>
    <div id="{$id}-foot">
      <span class="button" style="margin:15px 0 0 15px;" usi-popup-action="close" usi-popup-close="{$id}">{$close}</span>
    </div><!--{$id}-foot-->
  </div><!--{$id}-wrap-->
</div>
$divider
EOD;
         USI_WordPress_Solutions::admin_footer_script(self::$scripts[$id]);

      }  // ENDIF popup html not set;

   } // build();

   public static function link($options) {

      if (!empty($options['id'])) {
         $id       = $options['id'];
      } else {
         $id       = 'usi-popup';
      }
      $attributes  = self::$attributes[$id] . ' usi-popup-open="' . $id . '"';

      if (empty($options['link']['text'])) {
         $link     = null;
      } else {
         $link     = esc_attr($options['link']['text']);
         if (!empty($options['link']['class'])) $attributes .= ' class="' . $options['link']['class'] . '"';
         if (!empty($options['link']['style'])) $attributes .= ' style="' . $options['link']['style'] . '"';
      }

      if (!empty($options['tip'])) {
         $attributes .= ' title="' . esc_attr($options['tip']) . '"';
      }

      if (!empty($options['iframe'])) {
         $iframe   = $options['iframe'];
         $attributes .= ' usi-popup-iframe="' . $iframe . '"';
         $type     = 'iframe';
      }

      if (!empty($options['title'])) {
         $title    = esc_attr($options['title']);
      } else {
         $title    = 'WordPress-Solutions Popup';
      }
      $attributes .= ' usi-popup-title="' . $title . '"';

      $extra  = !empty($options['extra'] ) ?    ' ' . $options['extra']   : null;
      $tag    = !empty($options['tag']   ) ?          $options['tag']     : 'a';

      $invoke = $link
         ? apply_filters('usi_wordpress_popup_invoke', "<$tag$attributes$extra>$link</$tag>")
         : null
         ;

      return($invoke);

   } // link();

} // Class USI_WordPress_Solutions_Popup_Iframe;

// --------------------------------------------------------------------------------------------------------------------------- // ?>