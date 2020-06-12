<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

// https://codex.wordpress.org/Class_Reference/WP_List_Table
// https://gist.github.com/paulund/7659452

if (!class_exists('WP_List_Table')) {
   require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class USI_WordPress_Solutions_User_Sessions extends WP_List_Table {

   const VERSION = '2.7.0 (2020-06-08)';

   public static function action_admin_head() {

      $columns = array(
         'id'      => 5, 
         'user'    => 40, 
         'created' => 20, 
         'expires' => 20, 
         'ip'      => 10, 
      );

      echo USI_WordPress_Solutions_Static::column_style($columns, 'overflow:hidden; text-overflow:ellipsis; white-space:nowrap;');
usi::log();
      USI_WordPress_Solutions_Static::action_admin_head(
         '.usi-wordpress-session-photo{float:left;}' .
         '.usi-wordpress-session-info{float:left; padding-left:5px;}' .
         '.usi-wordpress-session-token{float:clear;}' .
         '.usi-wordpress-session-user{overflow:hidden;}'
      );

   } // action_admin_head();

   public static function action_admin_menu() {

      add_options_page(
         'user-sessions', 
         'user-sessions', 
         'manage_options', 
         'usi-wordpress-solutions-user-sessions', 
         array('USI_WordPress_Solutions_User_Sessions', 'render_list')
      );

      global $menu;
      foreach ($menu as $key => $values) {
         if ('usi-wordpress-solutions-user-sessions' == $values[2]) {
            unset($menu[$key]);
            break;
         }
      }

   } // action_admin_menu();

   public function column_default($item, $column_name) {

      static $session_tokens;

      switch ($column_name) {

      case 'created': 

         $html = null;
         foreach ($session_tokens as $key => $value) {
            $html .= date('Y-m-d H:i:s', $value['login']) . '<br/>';
         }
         return($html);

      case 'expires':

         $html = null;
         foreach ($session_tokens as $key => $value) {
            $html .= date('Y-m-d H:i:s', $value['expiration']) . '<br/>';
         }
         return($html);

      case 'ip': 

         $html = null;
         foreach ($session_tokens as $key => $value) {
            $html .= $value['ip'] . '<br/>';
         }
         return($html);

      case 'id': 

         $session_tokens = get_user_meta($item->ID, 'session_tokens', true);

         return($item->ID);

      case 'user': 

         foreach ($item->roles as $role) {
            break;
         }

         $html = 
            '<div class="usi-wordpress-session-user">' .
              '<div class="usi-wordpress-session-photo">' . 
                get_avatar($item->ID, 32) .
              '</div>' .
              '<div class="usi-wordpress-session-info">' . 
                 ucfirst($role) . ': <a href="' . get_edit_user_link($item->ID) . '">' . $item->display_name . '</a><br/><a href="mailto:' . 
                 $item->user_email . '">' . $item->user_email . '</a>' .
              '</div>' .
            '</div>' .
            '<div class="usi-wordpress-session-token">Session ID:<br/>';

         foreach ($session_tokens as $key => $value) {
            $html .= $key . '<br/>';
         }

         return($html . '</div>');

      default: return(print_r($item, true));

      } 

   } // column_default();

   public function get_columns() {
      return(
         array(
            'id'      => 'ID',
            'user'    => 'User',
            'created' => 'Created',
            'expires' => 'Expires',
            'ip'      => 'Ip',
         ) 
      );
   } // get_columns();

   public function get_hidden_columns() {
      return(array());
   } // get_hidden_columns();

   public function get_sortable_columns() {
      return(array());
   } // get_sortable_columns();

   public function prepare_items() {

      $columns  = $this->get_columns();
      $hidden   = $this->get_hidden_columns();
      $sortable = $this->get_sortable_columns();

      $this->_column_headers = array($columns, $hidden, $sortable);

      $this->items = $this->table_data();

   } // prepare_items();

   public static function render_list() {

      $list = new USI_WordPress_Solutions_User_Sessions();

      $list->prepare_items();

      echo
         '<div class="wrap">' . 
           '<div id="icon-users" class="icon32"></div>' .
           '<h2>Users Currently Logged In</h2>';
            $list->display();
      echo
         '</div>';

   } // render_list();

   private function table_data() {
      return(get_users(array('meta_key' => 'session_tokens', 'meta_compare' => 'EXISTS')));
   } // table_data();

} // USI_WordPress_Solutions_User_Sessions();

add_action('admin_head', array('USI_WordPress_Solutions_User_Sessions', 'action_admin_head'));
add_action('admin_menu', array('USI_WordPress_Solutions_User_Sessions', 'action_admin_menu'));

// --------------------------------------------------------------------------------------------------------------------------- // ?>