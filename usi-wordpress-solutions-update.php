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
// Update Failed: Download failed. A valid URL was not provided.
// fails in download_package in class-wp-upgrader.php

class USI_WordPress_Solutions_Update_Base {

   const VERSION = '2.3.8 (2020-02-02)';

   protected $access_token;
   protected $active;
   protected $base_name;
   protected $debug = null;
   protected $file;
   protected $plugin;
   protected $repo_name;
   protected $repository;

   function __construct($file) {

      $this->file = $file;

      add_action('admin_init', array($this, 'action_admin_init'));

      add_filter('plugins_api', array($this, 'filter_plugins_api'), 10, 3);
      add_filter('pre_set_site_transient_update_plugins', array($this, 'filter_pre_set_site_transient_update_plugins'), 10, 1);
      add_filter('upgrader_post_install', array($this, 'filter_upgrader_post_installs'), 10, 3);

   } // __construct();

   function __destruct() {
      if ($this->debug) usi_log($this->debug);
   } // __destruct();

   public function action_admin_init() {

      $this->base_name = plugin_basename($this->file);
      $this->active    = is_plugin_active($this->base_name);
      $this->plugin    = get_plugin_data($this->file);

   } // action_admin_init();

   public function filter_plugins_api($result, $action, $args) {

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
               'download_link'     => $this->repository['zipball_url']
            );

            return((object)$plugin);

         }

      }  

      return($result);

   } // filter_plugins_api();

   public function filter_pre_set_site_transient_update_plugins($transient) {

      if (isset($transient->checked) && ($checked = $transient->checked)) {

         $this->get_repository_info();

         if ($this->repository) {
$this->debug .= __METHOD__.':'.__LINE__.':$this->repository=' . print_r($this->repository, true) . PHP_EOL;

            $out_of_date = version_compare($this->repository['tag_name'], $checked[$this->base_name], 'gt');

            if ($out_of_date) {

               $new_files = $this->repository['zipball_url'];

               $plugin = array(
                  'url' => $this->plugin['PluginURI'],
                  'slug' => $this->base_name,
                  'package' => $new_files,
                  'new_version' => $this->repository['tag_name']
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

      $data = null;
$this->debug .= __METHOD__.':'.__LINE__.':$request_uri=' . $request_uri . PHP_EOL;
//      $data = json_decode(wp_remote_retrieve_body(wp_remote_get($request_uri)), true);

      $response = wp_remote_get($request_uri);

$this->debug .= __METHOD__.':'.__LINE__.':$response=' . print_r($response, true) . PHP_EOL;

      $response_code = wp_remote_retrieve_response_code($response);

$this->debug .= __METHOD__.':'.__LINE__.':$response_code=' . $response_code . PHP_EOL;
      if (is_wp_error($response) || (200 !== $response_code)) {
$this->debug .= __METHOD__.':'.__LINE__.':error' . PHP_EOL;
      } else {
         $response_body = wp_remote_retrieve_body($response);
         if (empty($response_body)) {
         } else {
$this->debug .= __METHOD__.':'.__LINE__.':$response_body=' . print_r($response_body, true) . PHP_EOL;
            $data = json_decode($response_body);
         }
      }
      return($data);
   } // get_response();

} // Class USI_WordPress_Solutions_Update_Base;

class USI_WordPress_Solutions_Update_GitHub extends USI_WordPress_Solutions_Update_Base {

// https://developer.github.com/v3/#rate-limiting

   const VERSION = '2.3.8 (2020-02-02)';

   private $username;

   function __construct($file, $username, $repo_name, $access_token = null) {

      parent::__construct($file);

      $this->repo_name = $repo_name;
      $this->username = $username;

   } // __construct();

   protected function get_repository_info() {

      if (!$this->repository) {

         $request_uri = 'https://api.github.com/repos/' . $this->username. '/' . $this->repo_name . '/releases';

         if ($this->access_token) $request_uri .= '?access_token=' . $this->access_token;

$this->debug .= __METHOD__.':'.__LINE__.':$request_uri=' . $request_uri . PHP_EOL;

         $data = json_decode(wp_remote_retrieve_body(wp_remote_get($request_uri)), true);
$this->debug .= __METHOD__.':'.__LINE__.':$data=' . print_r($data, true) . PHP_EOL;
// $data1 = $data;
//         $data2 = $this->get_response($request_uri);

         if (is_array($data)) $data = current($data);

         if ($this->access_token) $data['zipball_url'] .= '?access_token=' . $this->access_token;

         $this->repository = $data;

      }

   } // get_repository_info();

} // Class USI_WordPress_Solutions_Update_GitHub;

class USI_WordPress_Solutions_Update_GitLab extends USI_WordPress_Solutions_Update_Base {

   const VERSION = '2.3.6 (2020-01-30)';

   private $service;

   function __construct($file, $service, $repo_name, $access_token = null) {

      parent::__construct($file);

      $this->service      = $service;
      $this->repo_name    = $repo_name;
      $this->access_token = $access_token;

   } // __construct();

   protected function get_repository_info() {

      if (is_null($this->repository)) {

         $request_uri = $this->service . '/api/v4/projects/' . $this->repo_name . '/repository/tags/';

         if ($this->access_token) $request_uri .= '?private_token=' . $this->access_token;

         $data = $this->get_response($request_uri);

         if (is_array($data)) $data = current($data);
$this->debug .= __METHOD__.':'.__LINE__.':$data=' . print_r($data, true) . PHP_EOL;

         if (!is_object($data)) return;

         $latest_version = property_exists($data, 'name') ? $data->name : '0';

         $message = property_exists($data, 'message') ? $data->message : '0';

         $date = property_exists($data, 'commit') ? $data->commit->created_at : date('Y-m-d');

         $plugin_package = $this->service . '/api/v4/projects/' . $this->repo_name . '/repository/archive.zip?sha=' . $latest_version;

         if ($this->access_token) $plugin_package .= '&private_token=' . $this->access_token;

         $this->repository = array(
            'tag_name'     => $latest_version,
            'published_at' => $date,
            'body'         => $message,
            'zipball_url'  => $plugin_package,
         );
$this->debug .= __METHOD__.':'.__LINE__.':$this->repository=' . print_r($this->repository, true) . PHP_EOL;

      }

   } // get_repository_info();

} // Class USI_WordPress_Solutions_Update_GitLab;

// --------------------------------------------------------------------------------------------------------------------------- // ?>
