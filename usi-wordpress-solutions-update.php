<?php // ------------------------------------------------------------------------------------------------------------------------ //

// https://www.smashingmagazine.com/2015/08/deploy-wordpress-plugins-with-github-using-transients/

defined('ABSPATH') or die('Accesss not allowed.');

final class USI_WordPress_Solutions_Update {

   const VERSION = '2.2.0 (2019-12-11)';

   private $active;
   private $basename;
   private $file;
   private $plugin;
   private $repository;
   private $username;

   private $authorize_token;
   private $github_response;

   function __construct($file, $username, $repository, $auhtorization = null) {

      $this->file       = $file;
      $this->username   = $username;
      $this->repository = $repository;

      add_action('admin_init', array($this, 'action_admin_init'));

      add_filter('plugins_api', array($this, 'filter_plugins_api'), 10, 3);
      add_filter('pre_set_site_transient_update_plugins', array($this, 'filter_pre_set_site_transient_update_plugins'), 10, 1); // modify_transient
      add_filter('upgrader_post_install', array($this, 'filter_upgrader_post_installs'), 10, 3); // after_install

   } // __construct();

   public function action_admin_init() {

      $this->basename = plugin_basename($this->file);
      $this->active   = is_plugin_active($this->basename);
      $this->plugin   = get_plugin_data($this->file);

   } // action_admin_init();

   public function filter_plugins_api($result, $action, $args) {

      if (!empty($args->slug) && ($args->slug == $this->basename)) {

         $this->get_repository_info();

         $plugin = array(
            'name'              => $this->plugin["Name"],
            'slug'              => $this->basename,
            'version'           => $this->github_response['tag_name'],
            'author'            => $this->plugin["AuthorName"],
            'author_profile'    => $this->plugin["AuthorURI"],
            'last_updated'      => $this->github_response['published_at'],
            'homepage'          => $this->plugin["PluginURI"],
            'short_description' => $this->plugin["Description"],
            'sections'          => array( 
               'Description'    => $this->plugin["Description"],
               'Updates'        => $this->github_response['body'],
            ),
            'download_link'     => $this->github_response['zipball_url']
         );
      usi_log(__METHOD__.':'.__LINE__.':plugin=' . print_r($plugin, true));

         return((object)$plugin);

      }  

      return($result);

   } // filter_plugins_api();

   public function filter_pre_set_site_transient_update_plugins($transient) {
      if (isset($transient->checked))if ($checked = $transient->checked) { // Did Wordpress check for updates?

         $this->get_repository_info(); // Get the repo info

         $out_of_date = version_compare($this->github_response['tag_name'], $checked[ $this->basename ]); // Check if we're out of date

         if ($out_of_date) {

            $new_files = $this->github_response['zipball_url']; // Get the ZIP

            $plugin = array(// setup our plugin info
               'url' => $this->plugin["PluginURI"],
               'slug' => $this->basename,
               'package' => $new_files,
               'new_version' => $this->github_response['tag_name']
            );

            $transient->response[$this->basename] = (object) $plugin; // Return it in response
         }
      }

      return $transient; // Return filtered transient
   } // filter_pre_set_site_transient_update_plugins();

   public function filter_upgrader_post_installs($response, $hook_extra, $result) {
      global $wp_filesystem;

      $install_directory = plugin_dir_path($this->file); // Our plugin directory 
      $wp_filesystem->move($result['destination'], $install_directory); // Move files to the plugin dir
      $result['destination'] = $install_directory; // Set the destination for the rest of the stack

      if ($this->active) { // If it was active
         activate_plugin($this->basename); // Reactivate
      }

      return $result;
   } // filter_upgrader_post_installs();

   private function get_repository_info() {

      if (is_null($this->github_response)) {

         $request_uri = sprintf('https://api.github.com/repos/%s/%s/releases', $this->username, $this->repository);

         if ($this->authorize_token) {
            $request_uri = sprintf('%s?access_token=%s', $request_uri, $this->authorize_token);
         }

         $response = json_decode(wp_remote_retrieve_body(wp_remote_get($request_uri)), true);

         if (is_array($response)) $response = current($response);
         usi_log(__METHOD__.':'.__LINE__.':response=' . print_r($response, true));

         if ($this->authorize_token) {
             $response['zipball_url'] = sprintf('%s?access_token=%s', $response['zipball_url'], $this->authorize_token); // Update our zip url with token
         }
         $this->github_response = $response;
      }
   } // get_repository_info();

} // Class USI_WordPress_Solutions_Update;

// --------------------------------------------------------------------------------------------------------------------------- // ?>
