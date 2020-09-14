<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

// https://codex.wordpress.org/Class_Reference/WP_List_Table
// https://gist.github.com/paulund/7659452

if (!class_exists('WP_List_Table')) {
   require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class USI_WordPress_Solutions_List extends WP_List_Table {

   const VERSION = '2.9.1 (2020-09-14)';

   public function column_default($item, $column_name) {
      switch( $column_name ) {
      case 'id':
      case 'title':
         return($item[$column_name]);
      default:
         return(print_r($item, true));
      } 
   } // column_default();

   public function get_columns() {
      return(
         array(
            'id'    => 'ID',
            'title' => 'Title',
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

      $list = new USI_WordPress_Solutions_List();

      $list->prepare_items();

      echo
         '<div class="wrap">' . 
           '<div id="icon-users" class="icon32"></div>' .
           '<h2>List Table 001</h2>';
            $list->display();
      echo
         '</div>';

   } // render_list();

   private function table_data() {
      return(
         array(
            array('id' => 1, 'title' => '1st Line'),
            array('id' => 2, 'title' => '2nd Line'),
            array('id' => 3, 'title' => '3rd Line'),
            array('id' => 4, 'title' => '4th Line'),
            array('id' => 5, 'title' => '5th Line'),
         )
      );
   } // table_data();

} // USI_WordPress_Solutions_List();

if (is_admin()) {
   add_action('admin_menu', 'add_menu_example_list_table_page');
}

function add_menu_example_list_table_page() {
   add_menu_page(
      'List Table 001', 
      'List Table 001', 
      'manage_options', 
      'usi-wordpress-solutions-user-sessions.php', 
      array('USI_WordPress_Solutions_List', 'render_list')
   );
}

// --------------------------------------------------------------------------------------------------------------------------- // ?>