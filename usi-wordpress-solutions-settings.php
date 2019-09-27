<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

require_once('usi-wordpress-solutions.php');
require_once('usi-wordpress-solutions-versions.php');

class USI_WordPress_Solutions_Settings {

   const VERSION = '2.1.4 (2019-09-26)';

   const DEBUG_INIT   = 0x01;
   const DEBUG_RENDER = 0x02;

   protected $active_tab = null;
   protected $debug = 0;
   protected $is_tabbed = false;
   protected $logger = null;
   protected $name = null;
   protected $option_name = null;
   protected $options = null;
   protected $page_slug = null;
   protected $prefix = null;
   protected $section_callback_offset = 0;
   protected $section_callbacks = array();
   protected $section_ids = array();
   protected $sections = null;
   protected $text_domain = null;

   function __construct($name, $prefix, $text_domain, & $options, $add_settings_link = true, $add_row_meta = true, $suffix = null) {

      $this->impersonate = !empty(USI_WordPress_Solutions::$options['admin-options']['impersonate']);
      $this->name        = $name;
      $this->option_name = $prefix . '-options' . $suffix;
      $this->options     = & $options;
      $this->page_slug   = self::page_slug($prefix);
      $this->prefix      = $prefix;
      $this->text_domain = $text_domain;

      $script = substr($_SERVER['SCRIPT_NAME'], strrpos($_SERVER['SCRIPT_NAME'], '/') + 1);

      if ('plugins.php' == $script) {

         if ($add_settings_link) add_filter('plugin_action_links', array($this, 'filter_plugin_action_links'), 10, 2);

         if ($add_row_meta) add_filter('plugin_row_meta', array($this, 'filter_plugin_row_meta'), 10, 2);

      } else if (
         (('options-general.php' == $script) && !empty($_GET['page']) && ($_GET['page'] == $this->page_slug)) ||
         (('options.php' == $script) && !empty($_POST['option_page']) && ($_POST['option_page'] == $this->page_slug)) 
         ) {

         $this->sections = $this->sections();

         if ($this->is_tabbed) {
            $prefix_tab  = $this->prefix . '-tab';
            $active_tab  = !empty($_POST[$prefix_tab]) ? $_POST[$prefix_tab] : (!empty($_GET['tab']) ? $_GET['tab'] : null);
            $default_tab = null;
            if ($this->sections) foreach ($this->sections as $section_id => $section) {
               if (!$default_tab) $default_tab = $section_id;
               if ($section_id == $active_tab) {
                  $this->active_tab = $active_tab;
                  break;
               }
            }
            if (!$this->active_tab) $this->active_tab = $default_tab;
         }

         add_action('admin_head', array($this, 'action_admin_head'));

         add_action('admin_init', array($this, 'action_admin_init'));

      }

      add_action('admin_menu', array($this, 'action_admin_menu'));

      if ($this->impersonate) {
         add_action('init', array( $this, 'action_init'));
         add_filter('user_row_actions', array($this, 'filter_user_row_actions'), 10, 2);
      }

      switch (USI_WordPress_Solutions::$options['preferences']['menu-sort']) {
      case 'alpha':
      case 'usi':
         add_filter('custom_menu_order' , '__return_true');
         add_filter('menu_order' , array($this, 'filter_menu_order'));
         break;
      }

   } // __construct();

   function action_admin_head() {
      if ($this->page_slug != ((!empty($_GET['page'])) ? esc_attr($_GET['page']) : '')) return;
      echo '<style>' . PHP_EOL .
          // '.form-table td{padding-bottom:12px; padding-top:2px;} /* 25px; */' . PHP_EOL .
          // '.form-table th{padding-bottom:7px; padding-top:7px;} /* 20px; */' . PHP_EOL .
          '.form-table td{padding-bottom:2px; padding-top:2px;} /* 15px; */' . PHP_EOL .
          '.form-table th{padding-bottom:7px; padding-top:7px;} /* 20px; */' . PHP_EOL .
          'h2{margin-bottom:0.1em; margin-top:2em;} /* 1em; */' . PHP_EOL .
          '</style>' . PHP_EOL;
   } // action_admin_head();

   function action_admin_init() {

      $prefix = $this->prefix;

      if ($this->sections) foreach ($this->sections as $section_id => $section) {

         $this->section_callbacks[] = !empty($section['header_callback']) ? $section['header_callback'] : null;
         $this->section_ids[] = $section_id;

         add_settings_section(
            $section_id, // Section id;
            !$this->is_tabbed && !empty($section['label']) ? $section['label'] : null, // Section title;
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
               $option_name  = $this->option_name . '[' . $section_id . ']['  . $option_id . ']';
               $option_value = (!empty($this->options[$section_id][$option_id]) ?
                  $this->options[$section_id][$option_id] : ('number' == $attributes['type'] ? 0 : null));

               if (self::DEBUG_INIT & $this->debug) call_user_func($this->logger, __METHOD__.':$options[' . $section_id . '][' . $option_id . ']=' . $option_value);
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

      $slug = add_options_page(
         __($this->name . ' Settings', $this->text_domain), // Page <title/> text;
         __($this->name, $this->text_domain), // Sidebar menu text; 
         'manage_options', // Capability required to enable page;
         $this->page_slug, // Menu page slug name;
         array($this, 'page_render') // Render page callback;
      );

      $action_load_help_tab = array($this, 'action_load_help_tab');

      if (is_callable($action_load_help_tab)) add_action('load-'. $slug, $action_load_help_tab);

   } // action_admin_menu();

   function action_init() { 
      if ($this->impersonate && !empty($_REQUEST['action']) && !empty( $_REQUEST['user_id']) && ('impersonate' == $_REQUEST['action'])) {
         if ($user = get_userdata($user_id = $_REQUEST['user_id'])) {
            if (wp_verify_nonce($_REQUEST['_wpnonce'], "impersonate_$user_id")) {
               wp_clear_auth_cookie();
               wp_set_current_user($user_id, $user->user_login);
               wp_set_auth_cookie($user_id);
               do_action('wp_login', $user->user_login, $user);
            }
         }
      }
   } // action_init();

   function debug($logger, $debug = 0xFF) {
      if (is_callable($logger)) {
         $this->debug  = $debug;
         $this->logger = $logger;
      }
   } // debug();

   function fields_render($args) {
      if (self::DEBUG_INIT & $this->debug) call_user_func($this->logger, __METHOD__.':args=' . print_r($args, true));
      self::fields_render_static($args);
   }

   // Statis version so that other classes can use this rendering function;
   static function fields_render_static($args) {

      $notes    = !empty($args['notes']) ? $args['notes'] : null;
      $type     = !empty($args['type'])  ? $args['type']  : 'text';

      $id       = !empty($args['id'])    ? ' id="'    . $args['id']    . '"' : null;
      $class    = !empty($args['class']) ? ' class="' . $args['class'] . '"' : null;
      $name     = !empty($args['name'])  ? ' name="'  . $args['name']  . '"' : null;

      $min      = isset($args['min'])    ? ' min="'   . $args['min']   . '"' : null;
      $max      = isset($args['max'])    ? ' max="'   . $args['max']   . '"' : null;

      $prefix   = isset($args['prefix']) ? $args['prefix'] : '';

      $rows     = isset($args['rows'])   ? ' rows="'  . $args['rows']  . '"' : null;

      $readonly = !empty($args['readonly']) ? ('checkbox' == $type ? ' disabled' : ' readonly') : null;
      $value    = !empty($args['value']) ? esc_attr($args['value']) : ('number' == $type ? 0 : null);

      $maxlen   = !empty($args['maxlength']) ? (is_integer($args['maxlength']) ? ' maxlength="' . $args['maxlength'] . '"' : null) : null;

      $attributes = $id . $class . $name . $min . $max . $maxlen . $readonly . $rows;

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

      case 'hidden':
      case 'number':
      case 'text':
         echo $prefix . '<input type="' . $type . '"' . $attributes . ' value="' . $value . '" />';
         break;

      case 'html':
         echo $args['html'];
         break;

      case 'textarea':
         echo $prefix . '<textarea' . $attributes . '>' . $value . '</textarea>';
         break;

      }

      if ($notes) echo $notes . PHP_EOL;

   } // fields_render();

   function fields_sanitize($input) {

      foreach ($this->sections as $section_id => $section) {
         if (!empty($section['fields_sanitize'])) {
            $object = $section['fields_sanitize'][0];
            $method = $section['fields_sanitize'][1];
            if (method_exists($object, $method)) $input = $object->$method($input, $section_id);
         }
      }
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

   // To include more options on this page, override this function and call parent::page_render($options);
   function page_render($options = null) {

      $page_header   = !empty($options['page_header'])   ? $options['page_header']   : null;
      $title_buttons = !empty($options['title_buttons']) ? $options['title_buttons'] : null;
      $tab_parameter = !empty($options['tab_parameter']) ? $options['tab_parameter'] : null;
      $trailing_code = !empty($options['trailing_code']) ? $options['trailing_code'] : null;
      $wrap_submit   = !empty($options['wrap_submit']);

      $submit_text = null;

      echo PHP_EOL .
         '<div class="wrap">' . PHP_EOL .
         '  <h1>' . ($page_header ? $page_header : __($this->name . ' Settings', $this->text_domain)) . $title_buttons . '</h1>' . PHP_EOL .
         '  <form method="post" action="options.php">' . PHP_EOL;

      if ($this->is_tabbed) {
         echo 
            '    <h2 class="nav-tab-wrapper">' . PHP_EOL;
            if ($this->sections) foreach ($this->sections as $section_id => $section) {
               $active_class = null;
               if ($section_id == $this->active_tab) {
                  $active_class = ' nav-tab-active';
                  $submit_text = isset($section['submit']) ? $section['submit'] : 'Save ' . $section['label'];
               }
               echo '      <a href="options-general.php?page=' . $this->page_slug . '&tab=' . $section_id . $tab_parameter .
                  '" class="nav-tab' . $active_class . '">' .
                  __($section['label'], $this->text_domain) . '</a>' . PHP_EOL;
            }
         echo
            '    </h2>' . PHP_EOL .
            '    <input type="hidden" name="' . $this->prefix . '-tab" value="' . $this->active_tab . '" />' . PHP_EOL;
      }

      settings_fields($this->page_slug);
      do_settings_sections($this->page_slug);

      if ($this->is_tabbed) {

         if ($this->section_callback_offset) echo PHP_EOL . '</div><!--' . $this->page_slug . '-' . 
            $this->section_ids[$this->section_callback_offset - 1] .'-->' . PHP_EOL . PHP_EOL;

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

      echo PHP_EOL .
         '  </form>' . PHP_EOL .
         '</div>' . PHP_EOL . 
         $trailing_code;

   } // page_render();

   public static function page_slug($prefix) {
      return($prefix . '-settings');
   } // page_slug();

   function section_render() {

      if ($this->is_tabbed) {
         if ($this->section_callback_offset) echo PHP_EOL . '</div><!--' . $this->page_slug . '-' . 
            $this->section_ids[$this->section_callback_offset - 1] .'-->';
         $section_id = $this->section_ids[$this->section_callback_offset];
         echo PHP_EOL . PHP_EOL . '<div id="' . $this->page_slug . '-' . $section_id . '"' .
            ($this->active_tab != $section_id ? ' style="display:none;"' : '') . '>' . PHP_EOL;
      }

      $section_callback = $this->section_callbacks[$this->section_callback_offset];
      $object = $section_callback[0];
      $method = $section_callback[1];
      if (method_exists($object, $method)) $object->$method();

      $this->section_callback_offset++;

   } // section_render();

   function sections() { // Should be over ridden by extending class;
      return(null);
   } // sections();

} // Class USI_WordPress_Solutions_Settings;

// --------------------------------------------------------------------------------------------------------------------------- // ?>