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

This popup displays a confirmation message for a list of items in a WordPress table.

*/

class USI_WordPress_Solutions_Popup_Action {

   const VERSION = '2.9.10 (2020-10-16)';

   const HEIGHT_HEAD_FOOT = 93;

   private static $scripts = array();

   private function __construct() {
   } // __construct();

   public static function build($options) {

      if (!empty($options['id'])) {
         $id       = $options['id'];
      } else {
         $id       = 'usi-popup';
      }

      if (!empty($options['title'])) {
         $title    = esc_attr($options['title']);
      } else {
         $title    = 'WordPress-Solutions Popup';
      }

      if (empty($options['height'])) {
         $size     = ' height:300px;';
         $max_body = 300;
      } else {
         $height   = explode(',', $options['height']);
         if (1 == count($height)) {
            $size  = ' height:' . $height[0] . ';';
         } else {
            $size  = ' min-height:' . $height[0] . '; max-height:' . $height[1] . ';';
         }
         $max_body = intval($height[0]);
      }
      $max_body   -= self::HEIGHT_HEAD_FOOT;

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

      $cancel = !empty($options['cancel']) ? $options['cancel']  : null;
      $close  = !empty($options['close'] ) ? $options['close']   : null;
      $ok     = !empty($options['ok']    ) ? $options['ok']      : null;

      if (empty(self::$scripts[$id])) { // IF popup html not set;

         if (empty(self::$scripts[0])) { // IF popup javaascript not set;

            $divider = USI_WordPress_Solutions_Static::divider(0, $id);

            $foot = 'const foot  = { ';
            $head = 'const head  = { ';
            $work = 'const work  = { ';
            foreach ($options['actions'] as $name => $values) {
               $foot .= $name . ": '" . $values['foot'] . "', ";
               $head .= $name . ": '" . $values['head'] . "', ";
               $work .= $name . ": '" . $values['work'] . "', ";
            }
            $foot .= '};';
            $head .= '};';
            $work .= '};';

            $select_bulk = "select_bulk = '" . (!empty($options['errors']['select_bulk']) ? $options['errors']['select_bulk']  : 'Please select a bulk action before you click the Apply button.') . "'";
            $select_item = "select_item = '" . (!empty($options['errors']['select_item']) ? $options['errors']['select_item']  : 'Please select some items before you click the Apply button.') . "'";

            $height_head_foot = 'const height_head_foot = ' . self::HEIGHT_HEAD_FOOT . ';';

            self::$scripts[0] = <<<EOD
$divider<script> 
jQuery(document).ready(
   function($) {

      {$foot}
      {$head}
      {$work}

      {$select_bulk}
      {$select_item}

      {$height_head_foot}

      var confirmed = false;

      function close() {
         $('#{$id}').fadeOut(300);
      } // close();

      function info(action, body) {
         return('<p>' + head[action] + '</p>' + body + '<p>' + foot[action] + '</p>');
      } // info();

      function show(action, body, invoke) {

         $('#{$id}-title').html('{$title}');

         $('#{$id}-body').html('<div style="padding:0 15px 0 15px;">' + body + '</div>');

         if ('error' === action) {
            $('#{$id}-work').html('').hide();
         } else {
            $('#{$id}-work').html(work[action]).show().attr('usi-popup-invoke', invoke);
            $('#{$id}-close').html('{$cancel}');
         }

         $('#{$id}').fadeIn(300);

         var height = $('#{$id}-body').height();

         if (height <= ${max_body}) $('#{$id}-wrap').height((height + height_head_foot) + 'px');

         return(false);

      } // show();

      // Close Popup with cancel/close/delete/ok button;
      $('[usi-popup-close]').click(() => close());

      // Close with outside click;
      $('[usi-popup-close-outside]').click(() => close())
      .children()
      .click(() => { return(false); });

      // Invoke popup via row action;
      $('[usi-popup-open]').click(
         function() {
            if (confirmed) { confirmed = false; return(true); }
            var action = $(this).attr('usi-popup-action');
            var body   = $(this).attr('usi-popup-info');
            var id     = $(this).attr('id');
            return(show(action, info(action, body), id));
         }
      ); // Invoke popup via row action;

      // Invoke popup via bulk action;
      $('#doaction,#doaction2').click(
         () => {
            if (confirmed) { confirmed = false; return(true); }
            var action = null;
            var bot    = $('#bulk-action-selector-bottom').val();
            var top    = $('#bulk-action-selector-top').val();
            if (-1 != top) {
               action = top;
            } else if (-1 != bot) {
               action = bot;
            } else {
               return(show('error', '<p>' + select_bulk + '</p>'));
            }
            var ids  = $('.usi-popup-checkbox');
            var list = '';
            var text = '';
            var delete_count = 0;
            for (var i = 0; i < ids.length; i++) {
               if (ids[i].checked) {
                  list += (list.length ? ',' : '') + ids[i].getAttribute('usi-popup-id');
                  text += (delete_count++ ? '<br/>' : '') + ids[i].getAttribute('usi-popup-info');
               }
            }
            if (!delete_count) {
               return(show('error', '<p>' + select_item + '</p>'));
            } else {
               return(show(action, info(action, text), 'doaction'));
            }
         }
      ); // Invoke popup via bulk action;

      // Execute action;
      $('#{$id}-work').click(
         function() {
            var invoke = '#' + $(this).attr('usi-popup-invoke');
            confirmed  = true;
            if ('#doaction' == invoke) {
               $(invoke).trigger('click');
            } else {
               location.href = $(invoke).attr('href');
            }
         }
      ); // Execute action;

   } // function();
);
</script>
$divider
EOD;

            USI_WordPress_Solutions::admin_footer_script(self::$scripts[0]);

         } // ENDIF popup javaascript not set;

         $divider = USI_WordPress_Solutions_Static::divider(0, $id);

         // The {$id}-head div is equivalent to the WordPress thickbox TB_title div;
         // The {$id}-title div is equivalent to the WordPress thickbox TB_ajaxWindowTitle div;

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
    <div id="{$id}-body" style="border-bottom:1px solid #ddd; max-height:{$max_body}px; overflow:auto;"></div>
    <div id="{$id}-foot">
      <div style="display:inline-block; height:13px; width:15px;"></div>
      <span class="button" id="{$id}-work" style="display:none; margin:15px 5px 0 0;"></span>
      <span class="button" id="{$id}-close" style="margin:15px 0 0 0;" usi-popup-close="{$id}">{$ok}</span>
    </div><!--{$id}-foot-->
  </div><!--{$id}-wrap-->
</div>
$divider
EOD;
         USI_WordPress_Solutions::admin_footer_script(self::$scripts[$id]);

      }  // ENDIF popup html not set;

   } // build();

   public static function column_cb($args) {

      $id       = !empty($args['id'])       ? $args['id']       : null;
      $id_field = !empty($args['id_field']) ? $args['id_field'] : null;
      $info     = !empty($args['info'])     ? $args['info']     : null;

      return(
         '<input class="usi-popup-checkbox" name="' . $id_field . '[' . $id . ']" type="checkbox" ' .
         'usi-popup-id="' . $id . '" usi-popup-info="' . $info . '" value="' . $id .'" />'
      );

   } // column_cb();

   public static function row_action($args, $action, $text) {

      $id_field = !empty($args['id_field']) ? $args['id_field'] : null;
      $info     = !empty($args['info'])     ? $args['info']     : null;
      $item     = !empty($args['item'])     ? $args['item']     : null;

      $id       = !empty($item[$id_field])  ? $item[$id_field]  : null;

      $url      = esc_url(
         wp_nonce_url( 
            add_query_arg( 
               array(
                  'action'  => $action,
                  $id_field => $id,
               ), 
               get_admin_url() . (!empty($args['url']) ? $args['url'] : null)
            ), 
            $action . '_' . $id
         )
      );

      return(
         '<a id="usi-popup-' . $action . '-' . $id . '" href="' . $url . '" usi-popup-action="' . $action . 
         '" usi-popup-open="' . $id . '" usi-popup-info="' . $info . '">' . $text . '</a>'
      );

   } // row_action();

} // Class USI_WordPress_Solutions_Popup_Action;

// --------------------------------------------------------------------------------------------------------------------------- // ?>