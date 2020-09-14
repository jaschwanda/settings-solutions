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

// https://kinsta.com/blog/wordpress-user-roles/

class USI_WordPress_Solutions_Capabilities {

   const VERSION = '2.9.5 (2020-09-14)';

   public $section = null;

   protected $roles = null;

   private $capabilities = null;
   private $disable_save = true;
   private $name = null;
   private $prefix = null;
   private $role = null;
   private $role_id = null;
   private $prefix_select_user = null;
   private $text_domain = null;
   private $user = null;
   private $user_roles = array();
   private $user_id = null;

   function __construct($parent) {

      $this->capabilities = $parent->capabilities();
      $this->name         = $parent->name();
      $this->options      = $parent->options();
      $this->prefix       = $parent->prefix();
      $this->roles        = $parent->roles();
      $this->text_domain  = $parent->text_domain();

      $this->section      = array(
         'fields_sanitize' => array($this, 'fields_sanitize'),
         'footer_callback' => array($this, 'section_footer'),
         'header_callback' => array($this, 'render_section'),
         'label' => __('Capabilities', $this->text_domain),
         'settings' => array(),
      );

      // Get the role and selected user, if none given then get the last ones modified by the user;

      $this->prefix_select_user = $this->prefix . '-select-user';

      $current_user_id = get_current_user_id();

      $role_id_option_name = $this->prefix . '-options-role-id';
      $user_id_option_name = $this->prefix . '-options-user-id';

      $option_role_id = get_user_option($role_id_option_name, $current_user_id);
      $option_user_id = get_user_option($user_id_option_name, $current_user_id);

      if (empty($option_role_id)) $option_role_id = 'administrator';
      if (empty($option_user_id)) $option_user_id = $current_user_id;

      $this->role_id = (!empty($_REQUEST['role_id']) ? $_REQUEST['role_id'] : $option_role_id);
      $this->user_id = (!empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : $option_user_id);

      if ($this->role_id != $option_role_id) update_user_option($current_user_id, $role_id_option_name, $this->role_id);
      if ($this->user_id != $option_user_id) update_user_option($current_user_id, $user_id_option_name, $this->user_id);

      $this->role = get_role($this->role_id);
      $this->user = new WP_User($this->user_id);
      $this->user_roles = array();
      if (!empty($this->user->roles) && is_array($this->user->roles)) {
         foreach ($this->user->roles as $role_id) {
            $this->user_roles[] = get_role($role_id);
         }
      }

      if (!empty($this->capabilities)) {
         foreach ($this->capabilities as $name => $capability) {
            $has_cap  = false;
            $notes    = null;
            $readonly = false;
            $slug     = self::capability_slug($this->prefix, $name);
            // IF setting capabilities for a role;
            if ($this->role) { 
               $has_cap = $this->role->has_cap($slug);
               $this->disable_save = false;
            // ELSEIF setting capabilities for a user;
            } else if ($this->prefix_select_user == $this->role_id) {
               foreach ($this->user_roles as $user_role) {
                  $has_cap = $user_role->has_cap($slug);
                  if ($has_cap) {
                     $notes    = sprintf(__("Set by user's %s role settings", $this->text_domain), ucfirst($user_role->name));
                     $readonly = true;
                     break;
                  }
                  $this->disable_save = false;
               }
               // IF capability not set by role, check if set for user;
               if (!$has_cap) $has_cap = $this->user->has_cap($slug);
            } // ENDIF setting capabilities for a user;
            $parent->set_options('capabilities', $name, $has_cap);
            $this->section['settings'][$name] = array(
               'readonly' => $readonly, 
               'label' => explode('|', $capability)[0], 
               'notes' => $notes, 
               'type' => 'checkbox'
            );
         }
      }

   } // __construct();

   public static function capability_slug($prefix, $capability) {
      return(strtolower(str_replace('-', '_', $prefix . '_' . $capability)));
   } // capability_slug();

   public static function current_user_can($prefix, $capability) {
      return(current_user_can(self::capability_slug($prefix, $capability)));
   } // current_user_can();

   function fields_sanitize($input) {
      if (!empty($this->capabilities)) {
         foreach ($this->capabilities as $name => $capability) {
            $slug = self::capability_slug($this->prefix, $name);
            // IF setting capabilities for a role;
            if ($this->role) { 
               if (empty($input['capabilities'][$name])) {
                  $this->role->remove_cap($slug);
               } else {
                  $this->role->add_cap($slug);
               }
            // ELSEIF setting capabilities for a user;
            } else if ($this->user && ($this->prefix_select_user == $this->role_id)) {
               foreach ($this->user_roles as $user_role) {
                  if ($user_role->has_cap($slug)) break 1;
               }
               // Capability not set by role, check if set for user;
               if (empty($input['capabilities'][$name])) {
                  $this->user->remove_cap($slug);
               } else {
                  $this->user->add_cap($slug);
               }
            } // ENDIF setting capabilities for a user;
         }
      }

      return($input);

   } // fields_sanitize();

   public static function init($prefix, $capabilities) {
      $roles = wp_roles();
      foreach ($capabilities as $capability_name => $capability) {
         $slug     = self::capability_slug($prefix, $capability_name);
         $defaults = explode('|', $capability);
         foreach ($roles->role_objects as $role_name => $role) {
            if (in_array($role_name, $defaults)) {
               $role->add_cap($slug);
            } else {
               $role->remove_cap($slug); // Remove capabilties from previous role, if any;
            }
         }
      }
   } // init();

   public static function remove($prefix, $capabilities) {
      $roles  = wp_roles();
      foreach ($capabilities as $capability_name => $capability) {
         $slug = self::capability_slug($prefix, $capability_name);
         foreach ($roles->role_objects as $role_name => $role) {
            $role->remove_cap($slug);
         }
      }
   } // remove();

   function render_section() {

      echo 
         '    <p>' . sprintf(__('The %s plugin enables you to set the role capabilites system wide or for a specific user on a user-by-user basis. Select the role or specific user you would like to edit and then check or uncheck the desired capabilites for that role or user.', $this->text_domain), $this->name) . '</p>' . PHP_EOL .
         '    <label>' . __('Capabilities for', $this->text_domain) . ' : </label>' . PHP_EOL .
         '    <input type="hidden" name="' . $this->prefix . '-role_id" value="' . $this->role_id . '" />' . PHP_EOL .
         '    <input type="hidden" name="' . $this->prefix . '-user_id" value="' . $this->user_id . '" />' . PHP_EOL .
         '    <select id="' . $this->prefix . '-role-select">' . PHP_EOL . 
         '      <option value="' . $this->prefix . '-select-user">' . __('Select User', $this->text_domain) . '</option>';
      wp_dropdown_roles($this->role_id);
      echo PHP_EOL . '    </select>' . PHP_EOL;
      if ($this->prefix_select_user == $this->role_id) {
         wp_dropdown_users(array('id' => $this->prefix . '-user-select', 'selected' => $this->user_id));
         $this->user = new WP_User($this->user_id);
         if (!empty($this->user->roles) && is_array($this->user->roles)) {
            global $wp_roles;
            $comma = ' (';
            foreach ($this->user->roles as $role) {
               echo $comma . $wp_roles->roles[$role]['name'];
               $comma = ', ';
            }
            echo ')';
         } else {
            echo ' (No role for this site)';
         }
      } else {
      //   echo '<a class="thickbox" href="' . plugins_url(null, __FILE__) . '/usi-wordpress-solutions-capabilities-list.php' .
      //   '?role=administrator" title="Administrator Capabilities">(Administrator)</a>';
      }
      echo PHP_EOL . 
         '<script>' . PHP_EOL .
         'jQuery(document).ready(function($) {' . PHP_EOL .
         "   var url = 'options-general.php?page=" . USI_WordPress_Solutions_Settings::page_slug($this->prefix) . "&tab=capabilities&role_id='" . PHP_EOL .
         "   $('#{$this->prefix}-role-select').change(function(){window.location.href = url + $(this).val() + '&user_id={$this->user_id}';});" . PHP_EOL .
         "   $('#{$this->prefix}-user-select').change(function(){window.location.href = url + '{$this->role_id}' + '&user_id=' + $(this).val();});" . PHP_EOL .
         '});' . PHP_EOL .
         '</script>' . PHP_EOL;
   } // render_section();

   function section_footer() {
      submit_button(
         __('Save Capabilities', $this->text_domain),
         'primary', 
         'submit', 
         true, 
         $this->disable_save ? 'disabled' : null
      ); 
      return(null);
   } // section_footer();

} // Class USI_WordPress_Solutions_Capabilities;

// --------------------------------------------------------------------------------------------------------------------------- // ?>