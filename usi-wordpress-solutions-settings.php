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

// Reference: https://developer.wordpress.org/plugins/settings/using-settings-api/
// Reference: https://digwp.com/2016/05/wordpress-admin-notices/

require_once('usi-wordpress-solutions.php');
require_once('usi-wordpress-solutions-history.php');
require_once('usi-wordpress-solutions-static.php');
require_once('usi-wordpress-solutions-versions.php');

class USI_WordPress_Solutions_Settings {

   const VERSION = '2.5.1 (2020-05-07)';

   const DEBUG_INIT   = 0x01;
   const DEBUG_RENDER = 0x02;

   private static $one_per_line = false;

   protected $active_tab = null;
   protected $capability = 'manage_options';
   protected $capabilities = null;
   protected $debug = 0;
   protected $enctype = null;
   protected $hide = null;
   protected $icon_url = null;
   protected $is_tabbed = false;
   protected $name = null;
   protected $option_name = null;
   protected $options = null;
   protected $override_do_settings_fields = true;
   protected $override_do_settings_sections = true;
   protected $page = null;
   protected $page_slug = null;
   protected $position = null;
   protected $prefix = null;
   protected $query = null;
   protected $roles = null;
   protected $section_callback_offset = 0;
   protected $section_callbacks = array();
   protected $section_ids = array();
   protected $sections = null;
   protected $text_domain = null;

   function __construct($config) {

      $this->impersonate = !empty(USI_WordPress_Solutions::$options['admin-options']['impersonate']);

      if (!empty($config['prefix'])) $this->prefix = $config['prefix'];

      $this->option_name = $this->prefix . '-options' . (!empty($config['suffix']) ? $config['suffix'] : '');
      $this->page_slug   = self::page_slug($this->prefix);

      $add_settings_link = empty($config['no_settings_link']);

      if (!empty($config['debug']))        $this->debug        = $config['debug'];
      if (!empty($config['capability']))   $this->capability   = $config['capability'];
      if (!empty($config['capabilities'])) $this->capabilities = $config['capabilities'];
      if (!empty($config['hide']))         $this->hide         = $config['hide'];
      if (!empty($config['icon_url']))     $this->icon_url     = $config['icon_url'];
      if (!empty($config['name']))         $this->name         = $config['name'];
      if (!empty($config['page']))         $this->page         = $config['page'];
      if (!empty($config['options']))      $this->options      = & $config['options'];
      if (!empty($config['position']))     $this->position     = $config['position'];
      if (!empty($config['query']))        $this->query        = $config['query'];
      if (!empty($config['roles']))        $this->roles        = $config['roles'];
      if (!empty($config['text_domain']))  $this->text_domain  = $config['text_domain'];

      $script = substr($_SERVER['SCRIPT_NAME'], strrpos($_SERVER['SCRIPT_NAME'], '/') + 1);

      global $pagenow;
if ($script != $pagenow) die("$script != $pagenow");
      if ('plugins.php' == $script) {

         if ($add_settings_link) add_filter('plugin_action_links', array($this, 'filter_plugin_action_links'), 10, 2);

         $filter_plugin_row_meta = array($this, 'filter_plugin_row_meta');

         if (is_callable($filter_plugin_row_meta)) add_filter('plugin_row_meta', $filter_plugin_row_meta, 10, 2);

      } else if (
         ((('admin.php' == $script) || ('options-general.php' == $script)) && !empty($_GET['page']) && ($_GET['page'] == $this->page_slug)) ||
         (('options.php' == $script) && !empty($_POST['option_page']) && ($_POST['option_page'] == $this->page_slug)) 
         ) {

         add_action('admin_head', array($this, 'action_admin_head'));

         add_action('admin_init', array($this, 'action_admin_init'));

      } else if (!empty(USI_WordPress_Solutions::$options['diagnostics']['visible-grid'])) {

         switch ($pagenow) {
         case 'profile.php':
         case 'user-edit.php': 
         case 'users.php': 
            add_action('admin_head', array($this, 'action_admin_head'));
            break;
         }

      }

      add_action('admin_menu', array($this, 'action_admin_menu'));

      // Add notices for custom options pages, WordPress does settings pages automatically;
      if ('menu' == $this->page) add_action('admin_notices', array($this, 'action_admin_notices'));

      if ($this->impersonate) {
         add_action('init', array( $this, 'action_init'));
         add_filter('user_row_actions', array($this, 'filter_user_row_actions'), 10, 2);
      }

      if (!empty(USI_WordPress_Solutions::$options['preferences']['menu-sort'])) {
         switch (USI_WordPress_Solutions::$options['preferences']['menu-sort']) {
         case 'alpha':
         case 'usi':
            add_filter('custom_menu_order' , '__return_true');
            add_filter('menu_order' , array($this, 'filter_menu_order'));
            break;
         }
      }

      if (!empty($config['file'])) register_activation_hook($config['file'], array($this, 'hook_activation'));

   } // __construct();

   function action_admin_head($css = null) {
      USI_WordPress_Solutions_Static::action_admin_head($css);
   } // action_admin_head();

   function action_admin_init() {

      $prefix = $this->prefix;

      $this->sections_load();

      if ($this->sections) foreach ($this->sections as $section_id => $section) {

         $this->section_callbacks[] = !empty($section['header_callback']) ? $section['header_callback'] : null;
         $this->section_ids[] = $section_id;

         add_settings_section(
            $section_id, // Section id;
            !$this->is_tabbed && !empty($section['label']) ? $section['label'] : (!empty($section['title']) ? $section['title'] : ''), // Section title;
            array($this, 'section_render'), // Render section callback;
            $this->page_slug // Settings page menu slug;
         );

         if (!empty($section['after_add_settings_section'])) {
            $object = $section['after_add_settings_section'][0];
            $method = $section['after_add_settings_section'][1];
            if (method_exists($object, $method)) $section['settings'] = $object->$method($section['settings']);
         }

         if (!empty($section['settings'])) {
            foreach ($section['settings'] as $option_id => $attributes) {
               $option_name  = (!empty($attributes['name']) ? $attributes['name'] : $this->option_name . '[' . $section_id . '][' . $option_id . ']');
               $option_value = (!empty($this->options[$section_id][$option_id]) ?
                  $this->options[$section_id][$option_id] : (('number' == $attributes['type']) ? 0 : null));

               if (self::DEBUG_INIT & $this->debug) usi::log('$options[' . $section_id . '][' . $option_id . ']=' . $option_value);
               if (empty($attributes['skip'])) {
                  add_settings_field(
                     $option_id, // Option name;
                     !empty($attributes['label']) ? $attributes['label'] : null, // Field title; 
                     array($this, 'fields_render'), // Render field callback;
                     $this->page_slug, // Settings page menu slug;
                     $section_id, // Section id;
                     array_merge($attributes, 
                        array(
                           'name'  => $option_name,
                           'value' => $option_value
                        )
                     )
                  );
               }
            }
         }
      }

      register_setting(
         $this->page_slug, // Settings group name, must match the group name in settings_fields();
         $this->option_name, // Option name;
         array($this, 'fields_sanitize') // Sanitize field callback;
      );

   } // action_admin_init();

   function action_admin_menu() { 

      // IF custom settings page;
      if ('menu' == $this->page) {

         $slug = add_menu_page(
            __($this->name . ' Settings', $this->text_domain), // Page <title/> text;
            __($this->name, $this->text_domain), // Sidebar menu text; 
            $this->capability, // Capability required to enable page;
            $this->page_slug, // Menu page slug name;
            array($this, 'page_render'), // Render page callback;
            $this->icon_url, // URL of icon for menu item;
            $this->position // Position in menu order;
         );

      } else { // ELSE standard settings page;

         $slug = add_options_page(
            __($this->name . ' Settings', $this->text_domain), // Page <title/> text;
            __($this->name, $this->text_domain), // Sidebar menu text; 
            $this->capability, // Capability required to enable page;
            $this->page_slug, // Menu page slug name;
            array($this, 'page_render') // Render page callback;
         );

      } // ENDIF standard settings page;

      $action_load_help_tab = array($this, 'action_load_help_tab');

      if (is_callable($action_load_help_tab)) add_action('load-'. $slug, $action_load_help_tab);

      if ($this->hide) {
         global $menu;
         foreach ($menu as $key => $values) {
            if ($values[2] == $this->page_slug) {
               unset($menu[$key]);
               break;
            }
         }
      }

   } // action_admin_menu();

   // Add settings error/success status for custom settings pages;
   function action_admin_notices() {
      if (isset($_GET['settings-updated'])) {
         settings_errors($this->page_slug);
      }
   } // action_admin_notices();

   function action_init() { 
      if ($this->impersonate && !empty($_REQUEST['action']) && !empty( $_REQUEST['user_id']) && ('impersonate' == $_REQUEST['action'])) {
         if ($user = get_userdata($user_id = $_REQUEST['user_id'])) {
            if (wp_verify_nonce($_REQUEST['_wpnonce'], "impersonate_$user_id")) {
               if (!empty(USI_WordPress_Solutions::$options['admin-options']['history'])) {
                  $old_user = get_userdata($old_user_id = get_current_user_id());
                  USI_WordPress_Solutions_History::history($old_user_id, 'user', 
                     'User <' . $old_user->user_login . '> impersonating user <' . $user->user_login . '>', $user_id, $_REQUEST);
               }
               wp_clear_auth_cookie();
               wp_set_current_user($user_id, $user->user_login);
               wp_set_auth_cookie($user_id);
               do_action('wp_login', $user->user_login, $user);
            }
         }
      }
   } // action_init();

   public function capabilities() { 
      return($this->capabilities); 
   } // capabilities();

   // This function riped from wp-admin/includes/template.php;
   function do_settings_fields($page, $section) {
      global $wp_settings_fields;

      $i  = $this->is_tabbed ? '  ' : '';
      $i2 = '  ' . $i;
      $i3 = '  ' . $i2;
      $i4 = '  ' . $i3;
      $i5 = '  ' . $i4;
      $n  = PHP_EOL;

      if (!isset($wp_settings_fields[$page][$section])) return;

      foreach ((array)$wp_settings_fields[$page][$section] as $field) {
         $class = '';

         if (!empty($field['args']['class'])) $class = ' class="' . esc_attr( $field['args']['class'] ) . '"';

         if (!self::$one_per_line) {
            echo "$i4<tr{$class}>$n$i5";
            if (!empty($field['args']['label_for'])) {
               echo '<th scope="row"><label for="' . esc_attr($field['args']['label_for']) . '">' . $field['title'] . '</label></th>';
            } else if (empty( $field['args']['alt_html'])) {
               echo '<th scope="row">' . $field['title'] . '</th>';
            } else {
               echo '<th scope="row">' . $field['args']['alt_html'] . '</th>';
            }
            echo $n . $i5 . '<td>';
         }
         call_user_func($field['callback'], $field['args']);
         if (!self::$one_per_line) {
            echo '</td>' . $n;
            echo $i4 . '</tr>' . $n;
         }
      }
   } // do_settings_fields();

   // This function riped from wp-admin/includes/template.php;
   function do_settings_sections($page) {
      $i  = $this->is_tabbed ? '  ' : '';
      $i2 = '  ' . $i;
      $i3 = '  ' . $i2;
      $n  = PHP_EOL;
      global $wp_settings_sections, $wp_settings_fields;
      if (!isset($wp_settings_sections[$page])) return;
      foreach ((array)$wp_settings_sections[$page] as $section) {
         if ($section['title']) echo "$i3<h2>{$section['title']}</h2>\n";
         if ($section['callback']) call_user_func($section['callback'], $section);
         if (!isset($wp_settings_fields) || !isset($wp_settings_fields[$page]) || !isset($wp_settings_fields[$page][$section['id']])) continue;
         echo $i3 . '<table class="form-table" role="presentation">' . $n;
         if ($this->override_do_settings_fields) {
            $this->do_settings_fields($page, $section['id']);
         } else {
            do_settings_fields($page, $section['id']);
         }
         echo $i3 . '</table>' . $n;
      }
   } // do_settings_sections();

   function fields_render($args) {
      if (self::DEBUG_RENDER & $this->debug) usi::log('args=', $args);
      self::fields_render_static($args);
   }

   // Static version so that other classes can use this rendering function;
   public static function fields_render_static($args) {

      if (isset($args['one_per_line'])) self::$one_per_line = empty($args['one_per_line']);

      $notes    = !empty($args['notes'])   ? $args['notes'] : null;
      $type     = !empty($args['type'])    ? $args['type']  : 'text';

      $id       = !empty($args['id'])      ? ' id="'    . $args['id']      . '"' : null;
      $class    = !empty($args['f-class']) ? ' class="' . $args['f-class'] . '"' : null;
      $name     = !empty($args['name'])    ? ' name="'  . $args['name']    . '"' : null;
      $attr     = !empty($args['attr'])    ? ' '        . $args['attr']          : null;

      $min      = isset($args['min'])      ? ' min="'   . $args['min']     . '"' : null;
      $max      = isset($args['max'])      ? ' max="'   . $args['max']     . '"' : null;

      $prefix   = isset($args['prefix'])   ? $args['prefix'] : '';

      $rows     = isset($args['rows'])     ? ' rows="'  . $args['rows']  . '"' : null;

      $readonly = !empty($args['readonly']) ? ('checkbox' == $type ? ' disabled' : ' readonly') : null;
      $value    = !empty($args['value']) ? esc_attr($args['value']) : (('number' == $type) ? 0 : null);

      $maxlen   = !empty($args['maxlength']) ? (is_integer($args['maxlength']) ? ' maxlength="' . $args['maxlength'] . '"' : null) : null;

      $attributes = $id . $class . $name . $attr . $min . $max . $maxlen . $readonly . $rows;

      switch ($type) {

      case 'radio':
         foreach ($args['choices'] as $choice) {
            $label = !empty($choice['label']);
            echo $prefix . (!empty($choice['prefix']) ? $choice['prefix'] : '') .
               ($label ? '<label>' : '') . '<input type="radio"' . $attributes . ' value="' . esc_attr($choice['value']) . '"' . 
               checked($choice['value'], $value, false) . ' />' . $choice['notes'] . ($label ? '</label>' : '') .
               (!empty($choice['suffix']) ? $choice['suffix'] : '');
         }
         break;

      case 'checkbox':
         // Not sure why we have to convert 'true' to true, but checked() sometimes wouldn't check otherwise;
         echo $prefix . '<input type="checkbox"' . $attributes . ' value="true"' . checked('true' == $value ? true : $value, true, false) . ' />';
         break;

      case 'null-number':
         $type = 'number';
      case 'file':
      case 'hidden':
      case 'number':
      case 'text':
         echo $prefix . '<input type="' . $type . '"' . $attributes . ' value="' . $value . '" />';
         break;

      case 'html':
         echo $args['html'];
         break;

      case 'select':
         echo $prefix . self::fields_render_select($attributes, $args['options'], $value);
         break;

      case 'textarea':
         echo $prefix . '<textarea' . $attributes . '>' . $value . '</textarea>';
         break;

      }

      if ($notes) echo $notes;

      if (!empty($args['more'])) {
         foreach ($args['more'] as $more) {
            self::fields_render_static($more);
         }
      }

   } // fields_render();

   public static function fields_render_select($attributes, $rows, $value = null) {
      $html = '<select' . $attributes . '>';
      foreach ($rows as $row) {
         $html .= '<option ' . ($row[0] === $value ? 'selected ' : '') . 'value="' . $row[0] . '">' . $row[1] . '</option>';
      }
      return($html . '</select>');
   } // fields_render_select();

   function fields_sanitize($input) {

      foreach ($this->sections as $section_id => $section) {
         if (!empty($section['fields_sanitize'])) {
            $object = $section['fields_sanitize'][0];
            $method = $section['fields_sanitize'][1];
            if (method_exists($object, $method)) {
               $input = $object->$method($input, $section_id);
            }
         }
      }

      USI_WordPress_Solutions_History::history(get_current_user_id(), 'code', 
         'Modified <' . $this->name . '> settings', 0, $input);

      return($input);

   } // fields_sanitize();

   function filter_menu_order($menu_order) {
      global $submenu;
      $keys = array();
      $names = array();
      $options = array();
      if (!empty($submenu['options-general.php'])) {
         switch (USI_WordPress_Solutions::$options['preferences']['menu-sort']) {
         case 'alpha': $match = '/./'; break;
         case 'usi':   $match = '/^usi\-\w+-settings/'; break;
         }
         foreach ($submenu['options-general.php'] as $key => $option) {
            if (!empty($option[2]) && preg_match($match, $option[2])) {
               $keys[] = $key;
               $names[] = $option[0];
               $options[] = $option;
               unset($submenu['options-general.php'][$key]);
            }
         }
      }
      asort($names);
      foreach ($names as $index => $value) {
         $submenu['options-general.php'][$keys[$index]] = $options[$index];
      }
      return($menu_order);
   } // filter_menu_order();

   function filter_plugin_action_links($links, $file) {
      if (false !== strpos($file, $this->text_domain)) {
         $links[] = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=' . 
            $this->page_slug . '">' . __('Settings', $this->text_domain) . '</a>';
      }
      return($links);
   } // filter_plugin_action_links();

   function filter_user_row_actions(array $actions, WP_User $user) {
      $current_user = wp_get_current_user();
      if ($current_user && $current_user->roles) {
         for ($ith = 0; $ith < count($current_user->roles); $ith++) {
            if ('administrator' == $current_user->roles[$ith]) {
               if ($user->ID == $current_user->ID) return($actions);
               $actions['impersonate'] = sprintf(
                  '<a href="%s">%s</a>',
                  esc_url(
                     wp_nonce_url( 
                        add_query_arg( 
                           array(
                              'action'  => 'impersonate',
                              'user_id' => $user->ID,
                           ), 
                           get_admin_url() . 'user-edit.php?user_id=' . $user->ID
                        ), 
                        'impersonate_' . $user->ID
                     )
                  ),
                  esc_html__('Impersonate', 'user-switching')
               );
               return($actions); 
            }
         }
      }
      return($actions);
   } // filter_user_row_actions();

   function hook_activation() {

      if (current_user_can('activate_plugins')) {

         check_admin_referer('activate-plugin_' . (isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : ''));

         if (!empty($this->capabilities)) {
            require_once('usi-wordpress-solutions-capabilities.php');
            USI_WordPress_Solutions_Capabilities::init($this->prefix, $this->capabilities);
         }

      }

   } // hook_activation();

   public function name() { 
      return($this->name); 
   } // name();

   public function options() { 
      return($this->options); 
   } // options();

   // To include more options on this page, override this function and call parent::page_render($options);
   function page_render($options = null) {

      $i  = '  ';
      $i2 = '    ';
      $i3 = '      ';
      $n  = PHP_EOL;
      $n2 = PHP_EOL . PHP_EOL;

      $page_header   = !empty($options['page_header'])   ? $options['page_header']   : null;
      $title_buttons = !empty($options['title_buttons']) ? $options['title_buttons'] : null;
      $tab_parameter = !empty($options['tab_parameter']) ? $options['tab_parameter'] : null;
      $trailing_code = !empty($options['trailing_code']) ? $options['trailing_code'] : null;
      $wrap_submit   = !empty($options['wrap_submit']);

      $submit_text   = null;

      echo 
         $n . '<div class="wrap">' . $n .
         $i . '<h1>' . ($page_header ? $page_header : __($this->name . ' Settings', $this->text_domain)) . $title_buttons . '</h1>' . $n .
         $i . USI_WordPress_Solutions_Static::divider(2) .
         $i . USI_WordPress_Solutions_Static::divider(2, $this->name) .
         $i . USI_WordPress_Solutions_Static::divider(2) .
         $i . '<form id="myForm" action="options.php"' . $this->enctype . ' method="post">' . $n;

      if ($this->is_tabbed) {
         echo 
            $i2 . '<h2 class="nav-tab-wrapper">' . $n;
            if ($this->sections) foreach ($this->sections as $section_id => $section) {
               if (!empty($section['not_tabbed'])) continue;
               $active_class = null;
               if ($section_id == $this->active_tab) {
                  $active_class = ' nav-tab-active';
                  $submit_text = isset($section['submit']) ? $section['submit'] : 'Save ' . $section['label'];
               }
               echo $i3 . '<a href="' . ('menu' == $this->page ? 'admin' : 'options-general') . '.php?page=' . 
                  $this->page_slug . '&tab=' . $section_id . $tab_parameter . ($this->query ? $this->query : '') . 
                  '" class="nav-tab' . $active_class . '">' . __($section['label'], $this->text_domain) . '</a>' . $n;
            }
         echo
            $i2 . '</h2>' . $n2 .
            $i2 . '<input type="hidden" name="' . $this->prefix . '-tab" value="' . $this->active_tab . '" />' . $n2;
      }

      settings_fields($this->page_slug);
      if ($this->override_do_settings_sections) {
         $this->do_settings_sections($this->page_slug);
      } else {
         do_settings_sections($this->page_slug);
      }

      if ($this->is_tabbed) {

         if ($this->section_callback_offset) {
            $section_name = $this->page_slug . '-' . $this->section_ids[$this->section_callback_offset - 1];
            echo $i2 . '</div>' . USI_WordPress_Solutions_Static::divider(10, $section_name);
         }

         if ($this->sections) foreach ($this->sections as $section_id => $section) {
            if ($section_id == $this->active_tab) {
               if (!empty($section['footer_callback'])) {
                  $object = $section['footer_callback'][0];
                  $method = $section['footer_callback'][1];
                  if (method_exists($object, $method)) $submit_text = $object->$method();
               }
            }
         }

      } else {

         // Call the first footer callback function found for submit button HTML;
         if ($this->sections) foreach ($this->sections as $section_id => $section) {
            if (!empty($section['footer_callback'])) {
               $object = $section['footer_callback'][0];
               $method = $section['footer_callback'][1];
               if (method_exists($object, $method)) $submit_text = $object->$method();
               break;
            }
         }

      }

      if ($wrap_submit) echo '<p class="submit">';

      if ($submit_text) submit_button($submit_text, 'primary', 'submit', !$wrap_submit); 

      if (!empty($options['submit_button'])) echo $options['submit_button'];

      if ($wrap_submit) echo '</p>';

      echo 
         $n .
         $i . '</form>' . $n .
         $i . USI_WordPress_Solutions_Static::divider(2) .
         $i . USI_WordPress_Solutions_Static::divider(2, $this->name) .
         $i . USI_WordPress_Solutions_Static::divider(2) .
         $trailing_code;

   } // page_render();

   public static function page_slug($prefix) {
      return($prefix . '-settings');
   } // page_slug();

   public function prefix() { 
      return($this->prefix); 
   } // prefix();

   public function roles() { 
      return($this->roles); 
   } // roles();

   function sections_load() {

      $this->sections = $this->sections();

      // Convert capabilities object to array();
      if (!empty($this->sections['capabilities']) && is_object($this->sections['capabilities'])) {
         $this->sections['capabilities'] = $this->sections['capabilities']->section;
      }

      // Convert diagnostics object to array();
      if (!empty($this->sections['diagnostics']) && is_object($this->sections['diagnostics'])) {
         $this->sections['diagnostics'] = $this->sections['diagnostics']->section;
      }

      // Convert updates object to array();
      if (!empty($this->sections['updates']) && is_object($this->sections['updates'])) {
         $this->sections['updates'] = $this->sections['updates']->section;
      }

      $labels = false;
      $notes  = 0;

      if ($this->sections) foreach ($this->sections as $section_id => & $section) {
         if (isset($section['localize_labels'])) $labels = ('yes' == $section['localize_labels']);
         if (isset($section['localize_notes'])) $notes   = (int)$section['localize_notes'];
         // The WordPress do_settings_sections() function renders the title before WordPress calls the section_render() function
         // which means the title is rendered before the previous tab <div> is closed, so we save the title under the usi-title 
         // property name, unset the title, then render the usi-title in the section_render() function when we want to;
         if (isset($section['title'])) {
            $section['usi-title'] = __($section['title'], $this->text_domain);
            unset($section['title']);
         }
         foreach ($section['settings'] as $name => & $setting) {
            if ($labels && !empty($setting['label'])) $setting['label'] = __($setting['label'], $this->text_domain);
            if ($notes  && !empty($setting['notes'])) {
               switch ($notes) {
               case 1: $setting['notes'] =  __($setting['notes'], $this->text_domain); break;                              // __();
               case 2: $setting['notes'] = ' &nbsp; <i>' . __($setting['notes'], $this->text_domain) . '</i>'; break;      // &nbsp; <i>__()</i>;
               case 3: $setting['notes'] = '<p class="description">' . __($setting['notes'], $this->text_domain) . '</p>'; // <p class="description">__()</p>;
               }
            }
         }
         unset($setting);
      }
      unset($section);

      if ($this->is_tabbed) {
         $prefix_tab  = $this->prefix . '-tab';
         $active_tab  = !empty($_POST[$prefix_tab]) ? $_POST[$prefix_tab] : (!empty($_GET['tab']) ? $_GET['tab'] : null);
         $default_tab = null;
         if ($this->sections) foreach ($this->sections as $section_id => $section) {
            if (!empty($section['not_tabbed'])) continue;
            if (!$default_tab) $default_tab = $section_id;
            if ($section_id == $active_tab) {
               $this->active_tab = $active_tab;
               break;
            }
         }
         if (!$this->active_tab) $this->active_tab = $default_tab;
      }

   } // sections_load();

   function section_render() {

      $i  = '  ';
      $i2 = '    ';
      $i3 = '      ';
      $n  = PHP_EOL;
      $n2 = PHP_EOL . PHP_EOL;

      $section_id = $this->section_ids[$this->section_callback_offset];

      if ($this->is_tabbed) {

         if ($this->section_callback_offset) {
            $old_section_id = $this->section_ids[$this->section_callback_offset - 1];
            $section_name   = $this->page_slug . '-' . $old_section_id;
            if (empty($this->sections[$section_id]['not_tabbed'])) {
               echo $i2 . '</div>' . USI_WordPress_Solutions_Static::divider(10, $section_name);
            } else {
               echo $i3 .  USI_WordPress_Solutions_Static::divider(6, $section_name);
            }
         }

         $section_name = $this->page_slug . '-' . $section_id;
         if (empty($this->sections[$section_id]['not_tabbed'])) {
            echo $n2 . $i2 .  USI_WordPress_Solutions_Static::divider(4, $section_name);
            echo $i2 . '<div id="' . $section_name . '"' . ($this->active_tab != $section_id ? ' style="display:none;"' : '') . '>' . $n;
         } else {
            echo $i3 .  USI_WordPress_Solutions_Static::divider(6, $section_name);
         }
      }

      if (!empty($this->sections[$section_id]['usi-title'])) echo "      <h2>{$this->sections[$section_id]['usi-title']}</h2>\n";

      $section_callback = $this->section_callbacks[$this->section_callback_offset];
      $object = $section_callback[0];
      $method = $section_callback[1];
      if (method_exists($object, $method)) $object->$method();

      $this->section_callback_offset++;

   } // section_render();

   function sections() { // Should be over ridden by extending class;
      return(null);
   } // sections();

   public function set_options($section, $name, $value) { 
      $this->options[$section][$name] = $value;
   } // set_options();

   public function text_domain() { 
      return($this->text_domain); 
   } // text_domain();

} // Class USI_WordPress_Solutions_Settings;

// --------------------------------------------------------------------------------------------------------------------------- // ?>