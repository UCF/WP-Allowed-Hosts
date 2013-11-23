<?php
/*
Plugin Name: WP Allowed Hosts
Description: Allow WordPress to communicate with other domains.
Version: 0.0.1
Author: Brandon Groves
License: GPL3
*/

class AH {
    public function __construct() {
        add_filter('http_request_host_is_external', array($this, 'http_external_host_allowed'), 10, 2);
        add_action('admin_menu', create_function('', 'new AHSettings();'));
    }

    function http_external_host_allowed($is_external, $host) {
        $is_allowed = false;
        $allowed_hosts = get_option('allowed-hosts');
        if (!empty($allowed_hosts)) {
            foreach (explode(',', $allowed_hosts) as $allowed_host) {

                if(get_option('allowed-hosts-regex')) {
                    if (preg_match($allowed_host, $host)) {
                        $is_allowed = true;
                        break;
                    }
                } else {
                    if (strcmp($allowed_host, $host) == 0) {
                        $is_allowed = true;
                        break;
                    }
                }
            }
        }

        return $is_allowed;
    }
}

class AHSettings {
    public
        $page_title = 'Allowed Hosts',
        $menu_title = 'Allowed Hosts',
        $capability = 'administrator',
        $menu_slug  = 'ah-settings-page',
        $file_name  = 'options.php';

    public function __construct() {
        add_options_page(
            $this->page_title,
            $this->menu_title,
            $this->capability,
            $this->menu_slug,
            create_function('', 'include(plugin_dir_path(__FILE__).\''.$this->file_name.'\');')
        );
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function register_settings() {
        register_setting('ah-settings-group', 'allowed-hosts');
        register_setting('ah-settings-group', 'allowed-hosts-regex');
    }

}

$ah = new AH();
?>
