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

// https://codex.wordpress.org/Javascript_Reference/ThickBox
// http://codylindley.com/thickbox/

/*
accept  = string|null                   // Accept button text, left most button user clicks if they accept/confirm the popup operation;
action  = string|null                   // WP_List_Table:item mouse over link action;
body    = string|null                   // Text message body for inline popups, automatically generated for WP_List_Table;
bulk    = string|'[]'                   // WP_List_Table:string representation of posible bulk actions array;
cancel  = string|null                   // Cancel button text, second button from left user clicks if they cancel the popup operation;
choice  = string|null                   // WP_List_Table:message displayed if no selection made prior to invoking popup;
class   = string                        // html class added to anchor, usualy used to identofy items to be scanned for selection;
close   = string|null                   // Close button text, usually used to remove information only popups;
direct  = string|null                   // jQuery selector(s) that when clicked start scan of items in document to include in popup operation, must include . and # and may include more than one;
format  = 'br'|'li'                     // List format, use breaks or list tags;
id      = string|null                   // Id of hidden <div> that contains the popup elements;
info    = string|null                   // Information included in the data-info"" attribute for item anchor;
input   = 'file'|'check'                // Input field types, usually checkboxes;
key     = string|null                   // Key included in the data-key"" attribute that uniquely identifies an item anchor;
page    = string|null                   // WP_List_Table:page that contains the list table, used to execute the action if popup is confirmed;
list    = string|null                   // jQuery selector(s) that select the items to include in the popup operation, must include . and # and may include more than one;
link    = string|null                   // Displayed text for an item anchor, what goes between the <a></a> tags;
modal   = true|null                     // Causes popup window to be modal, the title bar and close X are not show, clicking outside the popup does not close the popup;
ok      = string|null                   // OK button text, usually used to remove instruction only popups, like "please make a swlwction...", and user clicks the OK button;
pass    = int|null                      // Number of times or "passes" the build popup builder is called, null means only one time, positive numbers indicate the pass number, inline and script html are only generated on the 1st pass;
prefix  = string|null                   // Text string displayed before body text;
select  = string|null                   // WP_List_Table:message displayed if no bulk action selected prior to invoking popup;
submit  = string|null                   // jQuery selector string that invokes popup and submits the form after popup confirmation;
suffix  = string|null                   // Text string displayed after body text;
table   = true|false                    // The popup is to confirm an individual or bulk WP_List_Table action;
tip     = string|null                   // Tool tip that shows when you hover over an anchor link;
title   = string|'WordPress-Solutions'  // popup title;
type    = 'ajax'|'iframe'|'inline'      // popup implementation type;
url     = sting|null                    // URL for iframe source;
width   = int|300                       // popup width in pixels;
*/

class USI_WordPress_Solutions_Popup {

   const VERSION = '2.7.0 (2020-06-08)';

   private function __construct() {
   } // __construct();

   public static function build($options) {

      $accept = !empty($options['accept']) ?          $options['accept']  : null;
      $action = !empty($options['action']) ?          $options['action']  : null;
      $body   = !empty($options['body']  ) ?          $options['body']    : null;
      $bulk   = !empty($options['bulk']  ) ?          $options['bulk']    : '[]';
      $cancel = !empty($options['cancel']) ?          $options['cancel']  : null;
      $choice = !empty($options['choice']) ?          $options['choice']  : null;
      $class  = !empty($options['class'] ) ? esc_attr($options['class'])  : null;
      $close  = !empty($options['close'] ) ?          $options['close']   : null;
      $direct = !empty($options['direct']) ? esc_attr($options['direct']) : null;
      $format = !empty($options['format']) ?          $options['format']  : 'br';
      $height = !empty($options['height']) ?     (int)$options['height']  : 300;
      $id     = !empty($options['id']    ) ?          $options['id']      : null;
      $key_id = !empty($options['key_id']) ?          $options['key_id']  : null;
      $info   = !empty($options['info']  ) ? esc_attr($options['info'])   : null;
      $key    = !empty($options['key']   ) ? esc_attr($options['key'])    : null;
      $link   = !empty($options['link']  ) ? esc_attr($options['link'])   : null;
      $list   = !empty($options['list']  ) ?          $options['list']    : null;
      $modal  = !empty($options['modal'] ) ?         ($options['modal'])  : null;
      $ok     = !empty($options['ok']    ) ?          $options['ok']      : null;
      $page   = !empty($options['page']  ) ?          $options['page']    : null;
      $pass   = !empty($options['pass']  ) ?     (int)$options['pass']    : 0;
      $prefix = !empty($options['prefix']) ?          $options['prefix']  : null;
      $select = !empty($options['select']) ?          $options['select']  : null;
      $submit = !empty($options['submit']) ?          $options['submit']  : null;
      $suffix = !empty($options['suffix']) ?          $options['suffix']  : null;
      $table  = !empty($options['table'] ) ?          $options['table']   : null;
      $tip    = !empty($options['tip']   ) ? esc_attr($options['tip'])    : null;
      $title  = !empty($options['title'] ) ? esc_attr($options['title'])  : 'WordPress-Solutions Popup';
      $type   = !empty($options['type']  ) ?          $options['type']    : 'inline';
      $url    = !empty($options['url']   ) ?          $options['url']     : null;
      $width  = !empty($options['width'] ) ?     (int)$options['width']   : 300;

      $anchor = $inline = $script = $submit1 = null;
      $offset = 72;

      $iframe = 'iframe' == $type;

      if ($link) {
         $anchor = '<a class="' . $class . '"' .
            ($action && $pass ? ' data-action="'  . $action  . '"' : '') .
            ($info   && $pass ? ' data-info="'    . $info  . '"' : '') .
            ($key    && $pass ? ' data-key="'     . $key   . '"' : '') .
            ($iframe && $pass ? ' data-title="'   . $title . '"' : '') .
            ($iframe && $pass ? ' data-url="'     . $url   . '"' : '') . 
            ' href=""' . ($tip ? ' title="' . $tip . '"' : '') . '>' . $link . '</a>';
      }

      if (true === $modal) {
         $modal   = '&modal=true';
         $offset -= 29;
      }

      if ($submit) $submit1 = explode(',', $submit)[0];

      $table = $table ? 'true' : 'false';

      if (1 >= $pass) {

         if ($id) $inline = '<div id="' . $id . '" style="display:none"></div><!--' . $id . '-->';

         if ($pass) $url = null;

         $script = <<<EOD
<script> 
jQuery(document).ready(
   function($) {

      var is_active = false;
      var is_iframe = false;

      var log    = true;

      var accept = '{$accept}';
      var body   = '{$body}';
      var cancel = '{$cancel}';
      var choice = '{$choice}';
      var close  = '{$close}';
      var format = '{$format}';
      var ok     = '{$ok}';
      var prefix = '{$prefix}';
      var select = '{$select}';
      var suffix = '{$suffix}';
      var title  = '{$title}';
      var type   = '{$type}';
      var url    = '{$url}';

      var show_accept = false;
      var show_body   = false;
      var show_cancel = false;
      var show_close  = false;
      var show_ok     = false;
      var show_prefix = false;
      var show_select = false;
      var show_suffix = false;

      var action      = null;
      var html        = '';
      var id_list     = null;
      var style       = '';
      var table       = {$table};

      // Get bulk action for table confirmation popups;
      function bulk() {
         var actions = {$bulk};
         var top     = $('#bulk-action-selector-top').val();
         var bot     = $('#bulk-action-selector-bottom').val();
         if (top || bot) {
            for (var i  = 0; i < actions.length; i++) {
               if (top == actions[i]) return(top);
               if (bot == actions[i]) return(bot);
            }
         }
         // RETURN with direct action if any;
         return(action);
      } // bulk();

      function hide(text) {
         // Use <span> since not invoking link to another page;
         html += '<span class="button" onclick="tb_remove();"' + style + '>' + text + '</span> &nbsp;';
         style = '';
      } // hide():

      function init() {
         action      = null;
         show_accept = 0 < accept.length;
         show_body   = 0 < body.length;
         show_cancel = 0 < cancel.length;
         show_close  = 0 < close.length;
         show_ok     = 0 < ok.length;
         show_prefix = 0 < prefix.length;
         show_select = 0 < select.length;
         show_suffix = 0 < suffix.length;
         // Style pads actions buttons at bottom of iframe to look like inline popup;
         style       = ' style="margin-left:15px;"'
      } // init();

      function show() {

         if (log) console.log('show:begin');

         // Addresses bug in thickbox that doesn't allow height/wdith option on some admin pages;
         var old_position = tb_position;
         tb_position = function() {
            $('#TB_window').css(
               {
                  height : TB_HEIGHT + 'px', 
                  marginLeft : '-' + parseInt((TB_WIDTH / 2), 10) + 'px', 
                  marginTop : '-' + parseInt((TB_HEIGHT / 2), 10) + 'px',
                  width : TB_WIDTH + 'px', 
               }
            );
         }; // tb_position();

         if ('iframe' == type) {
            is_iframe = show_cancel || show_close || show_ok;
            tb_show(title, url + '?TB_iframe=true&height={$height}{$modal}&width={$width}', null);
         } else if ('inline' == type) {
            tb_show(title, 'TB_inline?inlineId={$id}&height={$height}{$modal}&width={$width}', null);
            style = '';
            $('#TB_ajaxContent').html(view());
            work();
         }

         tb_position = old_position;

         if (log) console.log('show:end');

      } // show();

      function view() {

         if (log) console.log('view:begin');

         html  = '';
         if (show_prefix) html += '<p>' + prefix + '</p>';
         if (show_body)   html += '<ul>' + body   + '</ul>';
         if (show_suffix) html += '<p>' + suffix + '</p>';
         html += '<hr><p>';
         if (show_accept) {
            if (table) {
               action = bulk();
               html  += '<a class="button" href="?page={$page}&action=' + action + '&{$key_id}=' + id_list + '">' + accept + '</a> &nbsp; ';
            } else {
               html  += '<span id="usi-wordpress-solutions-popup-confirm" class="button">' + accept + '</span> &nbsp; ';
            }
            style     = '';
         }
         if (show_cancel) hide(cancel);
         if (show_ok)     hide(ok);
         if (show_close)  hide(close);
         html += '</p>';

         if (log) console.log('view:end:html=' + html);

         return(html);

      } // view();

      // Re-submit form, this function must be called after accept button loaded into DOM;
      function work() {
         $('#usi-wordpress-solutions-popup-confirm').click(
            () => {
               is_active = true;
               tb_remove();
               $('{$submit1}').click();
               return(false);
            }
         );
      } // work();

      // Extend iframe thickbox to hold buttons at bottom;
      $('body').on(
         'thickbox:iframe:loaded', 
         () => {
            if (is_iframe) {
               is_iframe = false;
               $('#TB_iframeContent').height($('#TB_iframeContent').height() - {$offset});
               $('#TB_iframeContent').after(view());
               work();
            }
         }
      );

      // Invoke popup indirectly by clicking on submit button that scans document for elements;
      $('{$submit}').click(
         (event) => {

            if (log) console.log("$('{$submit}').click():begin");

            if (is_active) {
               is_active = false;
               if (log) console.log("$('{$submit}').click():is_active:end");
               return(true);
            }

            init();

            var t = event.target;
            if (t.getAttribute('data-title')) title = t.getAttribute('data-title');
            if (t.getAttribute('data-url')  ) url   = t.getAttribute('data-url');

            if ('inline' == type) {
               action    = bulk();
               if (table && !action) {
                  body   = select;
                  show_prefix = show_suffix = show_cancel = show_close = show_accept = false;
               } else {
                  body    = '';
                  id_list = '';
                  var ids = $('{$list}');
                  if (log) console.log("$('{$submit}').click():$('{$list}').length=" + ids.length);
                  try {
                  for (var i = 0; i < ids.length; i++) {
                     if (ids[i].checked) {
                        var key  = ids[i].getAttribute('data-key');
                        var info = ids[i].getAttribute('data-info');
                        if (!key && !info) {
                           //Take attributes from anchor tag in next field;
                           key  = ids[i].parentElement.nextSibling.firstChild.getAttribute('data-key');
                           info = ids[i].parentElement.nextSibling.firstChild.getAttribute('data-info');
                        }
                        if (log) console.log("$('{$submit}').click():$('{$list}').key=" + key + ' info=' + info);
                        id_list += (id_list.length ? ',' : '')  + key;
                        if ('li' == format) {
                           body    += '<li>' + info + '</li>';
                        } else {
                           body    += (body ? '<br/>' : '') + info;
                        }
                     } else if (ids[i].files && ids[i].files[0] && ids[i].files[0].name) {
                        var name = ' &nbsp; ' + ids[i].files[0].name;
                        if (log) console.log("$('{$submit}').click():$('{$list}').name=" + name);
                        if ('li' == format) {
                           body    += '<li>' + name + '</li>';
                        } else {
                           body    += (body ? '<br/>' : '') + name;
                        }
                     }
                  }
                  } catch (exception) {
                     console.log(exception.message);
                  }
                  if (body) {
                     show_ok = false;
                  } else {
                     body    = choice;
                     show_prefix = show_suffix = show_cancel = show_close = show_accept = false;
                  }
               }
               show_body = 0 < body.length;
            }

            show();

            if (log) console.log("$('{$submit}').click():end");

            return(false);

         }
      );

      // Invoke popup directly by click on element;
      $('{$direct}').click(
         function(event) {

            if (log) console.log("$('{$direct}').click():begin");

            init();

            var t = event.target;
            if (t.getAttribute('data-action')) action = t.getAttribute('data-action');
            if (t.getAttribute('data-title'))  title  = t.getAttribute('data-title');
            if (t.getAttribute('data-url')  )  url    = t.getAttribute('data-url');

            if ('inline' == type) {
               body      = t.getAttribute('data-info');
               show_body = 0 < body.length;
               show_ok   = false;
               id_list   = t.getAttribute('data-key');
            }

            show();

            if (log) console.log("$('{$direct}').click():begin");

            return(false);

         }
      ); // $('{$direct}').click();

   } // function();
);
</script>
EOD;

      }

      return(array('anchor' => $anchor, 'inline' => $inline, 'script' => $script));

   } // build();

} // Class USI_WordPress_Solutions_Popup;

// --------------------------------------------------------------------------------------------------------------------------- // ?>