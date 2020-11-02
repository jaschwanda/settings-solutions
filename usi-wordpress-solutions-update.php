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

// https://www.smashingmagazine.com/2015/08/deploy-wordpress-plugins-with-github-using-transients/

class USI_WordPress_Solutions_Update {

   const VERSION = '2.10.1 (2020-11-02)';

   protected $access_token = null;
   protected $active = null;
   protected $base_name = null;
   protected $debug = null;
   protected $file = null;
   protected $forced = false;
   protected $log = false;
   protected $notice = null;
   protected $plugin = null;
   protected $repo_name = null;
   protected $repository = null;

   function __construct($file) {

      $this->file = $file;

      $log        = USI_WordPress_Solutions_Diagnostics::get_log(USI_WordPress_Solutions::$options);

      $this->log  = USI_WordPress_Solutions::DEBUG_UPDATE == (USI_WordPress_Solutions::DEBUG_UPDATE & $log);

      if ($this->log) usi::log('$file=', $file);

      add_action('admin_init', array($this, 'action_admin_init'));

      add_filter('plugins_api', array($this, 'filter_plugins_api'), 10, 3);
      add_filter('pre_set_site_transient_update_plugins', array($this, 'filter_pre_set_site_transient_update_plugins'), 10, 1);
      add_filter('upgrader_post_install', array($this, 'filter_upgrader_post_installs'), 10, 3);

   } // __construct();

   public function action_admin_init() {

      $this->base_name = plugin_basename($this->file);
      $this->active    = is_plugin_active($this->base_name);
      $this->plugin    = get_plugin_data($this->file);

   } // action_admin_init();

   public function action_admin_notices() {
      global $pagenow;
      if ('plugins.php' == $pagenow) {
        echo '<div class="notice notice-warning is-dismissible"><p>' . $this->notice . '</p></div>';
      }
   } // action_admin_notices();

   public function filter_plugins_api($result, $action, $args) {

      if ($this->log) usi::log('$result=', $result, '\n$action=', $action, '\$args=', $args);

      if (!empty($args->slug) && ($args->slug == $this->base_name)) {

         $this->get_repository_info();

         if ($this->repository) {

            $plugin = array(
               'name'              => $this->plugin['Name'],
               'slug'              => $this->base_name,
               'version'           => $this->repository['tag_name'],
               'author'            => $this->plugin['AuthorName'],
               'author_profile'    => $this->plugin['AuthorURI'],
               'last_updated'      => $this->repository['published_at'],
               'homepage'          => $this->plugin['PluginURI'],
               'short_description' => $this->plugin['Description'],
               'sections'          => array( 
                  'Description'    => $this->plugin['Description'],
                  'Updates'        => $this->repository['body'],
               ),
               'download_link'     => $this->repository['download_link']
            );

            if ($this->log) usi::log('$plugin=', $plugin);

            return((object)$plugin);

         }

      }  

      return($result);

   } // filter_plugins_api();

   public function filter_pre_set_site_transient_update_plugins($transient) {

      if ($this->log) usi::log('$transient=', $transient);

      if (isset($transient->checked) && ($checked = $transient->checked)) {

         $this->get_repository_info();

         if (!empty($this->repository['tag_name']) && !empty($checked[$this->base_name])) {

            $out_of_date = version_compare($this->repository['tag_name'], $checked[$this->base_name], 'gt');

            if ($this->log) usi::log('tag_name=', $this->repository['tag_name'], '\n$checked=', $checked, '\n$forced=', ($this->forced ? 'YES' : 'no'), '\$out_of_date=', ($out_of_date ? 'YES' : 'no'));

            if ($out_of_date || $this->forced) {

               $plugin = array(
                  'new_version' => $this->repository['tag_name'],
                  'package'     => $this->repository['download_link'],
                  'slug'        => $this->base_name,
                  'url'         => $this->plugin['PluginURI'],
               );

               $transient->response[$this->base_name] = (object)$plugin;

            }

         }

      }

      return($transient);

   } // filter_pre_set_site_transient_update_plugins();

   public function filter_upgrader_post_installs($response, $hook_extra, $result) {

      global $wp_filesystem;

      $install_directory = plugin_dir_path($this->file);
      $wp_filesystem->move($result['destination'], $install_directory);
      $result['destination'] = $install_directory;

      if ($this->active) activate_plugin($this->base_name);

      return($result);

   } // filter_upgrader_post_installs();

   protected function get_response($request_uri) {

      $response      = wp_remote_get($request_uri);

      $response_code = wp_remote_retrieve_response_code($response);

      if (!is_wp_error($response) && (200 === $response_code)) {
         $response_body = wp_remote_retrieve_body($response);
         if (!empty($response_body)) {
            return(json_decode($response_body, true));
         }
      } else {
         $this->notice = __('While fetching an update for ', USI_WordPress_Solutions::TEXTDOMAIN) .
            '<b>' . basename($this->base_name, '.php') . '</b>';
         $this->response_error($response, $response_code);
         add_action('admin_notices', array($this, 'action_admin_notices'));
      }

      return(null);

   } // get_response();

   protected function response_error($response, $response_code) {
      $this->notice .= '. . .<!-- ' . print_r($response, true) . '-->';
   } // response_error();

} // Class USI_WordPress_Solutions_Update;

class USI_WordPress_Solutions_Update_GitHub extends USI_WordPress_Solutions_Update {

   // https://developer.github.com/v3/#rate-limiting

   const VERSION = '2.9.5 (2020-09-14)';

   private $user_name;

   function __construct($file, $user_name, $repo_name, $access_token = null, $forced = false) {

      parent::__construct($file);

      $this->repo_name    = $repo_name;
      $this->user_name    = $user_name;
      $this->access_token = $access_token;
      $this->forced       = $forced;

   } // __construct();

   protected function get_repository_info() {

      if (!$this->repository) {

         $request_uri = 'https://api.github.com/repos/' . $this->user_name. '/' . $this->repo_name . '/releases';

         if ($this->access_token) $request_uri .= '?access_token=' . $this->access_token;

         $data = $this->get_response($request_uri);

         if (is_array($data)) $data = current($data);

         if ($this->access_token) $data['zipball_url'] .= '?access_token=' . $this->access_token;

         $this->repository  = array(
            'body'          => $data['body'],
            'download_link' => $data['zipball_url'],
            'published_at'  => $data['published_at'],
            'tag_name'      => $data['tag_name'],
         );

         if ($this->log) usi::log('$request_uri=', $request_uri, '\n$data=', $data, '\$repository=', $this->repository);

      }

   } // get_repository_info();

} // Class USI_WordPress_Solutions_Update_GitHub;

class USI_WordPress_Solutions_Update_GitLab extends USI_WordPress_Solutions_Update {

   const VERSION = '2.9.5 (2020-09-14)';

   private $service;

   function __construct($file, $service, $repo_name, $access_token = null, $forced = false) {

      parent::__construct($file);

      $this->service      = $service;
      $this->repo_name    = $repo_name;
      $this->access_token = $access_token;
      $this->forced       = $forced;

      add_filter('upgrader_pre_download', array($this, 'filter_upgrader_pre_download'), 10, 3);

   } // __construct();

   function filter_http_request_host_is_external($allow, $host, $url) {
      remove_filter('http_request_host_is_external', array($this, 'filter_http_request_host_is_external'), 10);
      return(false !== stripos($host, 'gitlab'));
   } // filter_http_request_host_is_external();

   public function filter_upgrader_pre_download($reply, $package, $that) {

      add_filter('http_request_host_is_external', array($this, 'filter_http_request_host_is_external'), 10, 3);

      return($reply);

   } // filter_upgrader_pre_download();

   protected function get_repository_info() {

      if (!$this->repository) {

         $request_uri = $this->service . '/api/v4/projects/' . $this->repo_name . '/repository/tags/';

         if ($this->access_token) $request_uri .= '?private_token=' . $this->access_token;

         $data = $this->get_response($request_uri);

         if (is_array($data)) $data = current($data);

         $tag_name      = $data['name'];

         $download_link = $this->service . '/api/v4/projects/' . $this->repo_name . '/repository/archive.zip?sha=' . $tag_name;

         if ($this->access_token) $download_link .= '&private_token=' . $this->access_token;

         $this->repository  = array(
            'body'          => !empty($data['message']) ? $data['message'] : null,
            'download_link' => $download_link,
            'published_at'  => !empty($data['commit']['created_at']) ? $data['commit']['created_at'] : date('Y-m-d'),
            'tag_name'      => $tag_name,
         );

         if ($this->log) usi::log('$request_uri=', $request_uri, '\n$data=', $data, '\n$tag_name=', $tag_name, '\n$download_link=', $download_link, '\$repository=', $this->repository);

      }

   } // get_repository_info();

   protected function response_error($response, $response_code) {
      if (!empty($response['body'])) {
         $body  = json_decode($response['body'], true);
         $error = !empty($body['error']) ? $body['error'] : '';
         $description = !empty($body['error_description']) ? $body['error_description'] : '';
         if ($error && $description) {
            $this->notice .= ', GitLab returns with error <b>' . $error . ', ' . $description . '</b>';
            return;
         }
      }
      parent::response_error($response, $response_code);
   } // response_error();

} // Class USI_WordPress_Solutions_Update_GitLab;

// --------------------------------------------------------------------------------------------------------------------------- // ?>
