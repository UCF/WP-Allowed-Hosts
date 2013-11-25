<?php
/*
Plugin Name: WP Allowed Hosts
Description: Allow WordPress to communicate with other domains.
Version: 1.0.2
Author: Brandon Groves
License: GPL3
*/

class AH {
    public function __construct() {
        add_filter('http_request_host_is_external', array($this, 'http_external_host_allowed'), 10, 2);

        $hook = (is_multisite() && isset($_GET['networkwide']) && 1 == $_GET['networkwide']) ? 'network_' : '';
        add_action('{$hook}admin_menu', create_function('', 'new AHSettings();'));
    }

    function http_external_host_allowed($is_external, $host) {

        // Get site option if network activated
        $allowed_hosts = '';
        $allowed_hosts_regex = '';
        if (is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ))) {
            $allowed_hosts = get_site_option('allowed-hosts');
            $allowed_hosts_regex = get_site_option('allowed-hosts-regex');
        } else {
            $allowed_hosts = get_option('allowed-hosts');
            $allowed_hosts_regex = get_option('allowed-hosts-regex');
        }

        $is_allowed = false;
        if (!empty($allowed_hosts)) {
            foreach (explode(',', $allowed_hosts) as $allowed_host) {
                $allowed_host = trim($allowed_host);
                if($allowed_hosts_regex) {
                    if (preg_match('/' . $allowed_host . '/', $host)) {
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
        $capability = 'manage_options',
        $menu_slug  = 'ah-settings-page',
        $file_name  = 'options.php';

    public function __construct() {
        if (is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ))) {
            add_submenu_page(
                'settings.php',
                $this->page_title,
                $this->menu_title,
                $this->capability,
                $this->menu_slug,
                create_function('', 'include(plugin_dir_path(__FILE__).\''.$this->file_name.'\');')
            );
        } else {
            add_options_page(
                $this->page_title,
                $this->menu_title,
                $this->capability,
                $this->menu_slug,
                create_function('', 'include(plugin_dir_path(__FILE__).\''.$this->file_name.'\');')
            );
        }

        add_action('admin_init', array($this, 'register_settings'));
    }

    public function register_settings() {
        register_setting('ah-settings-group', 'allowed-hosts');
        register_setting('ah-settings-group', 'allowed-hosts-regex');
    }

}

$ah = new AH();
?>
