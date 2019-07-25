=== WP Allowed Hosts ===
Contributors: ucfwebcom
Tags: host, hosts, allow, filter, multisite
Requires at least: 3.7.0
Tested up to: 5.2.2
Stable tag: 1.0.7
License: GPLv3 or later
License URI: http://www.gnu.org/copyleft/gpl-3.0.html

This plugin allows local domains to communicate.

== Description ==

WordPress plugin that allows you to specify a domain names to be checked against the `http_request_host_is_external` filter. Hosts that start with 192.* or 10.* are checked against the allowed hosts based on a new security update WordPress introduced in 3.5.2 (https://github.com/WordPress/WordPress/commit/1ec392175ce5f0320072e7b195a8d091bccddefb). Unfortunately there is no setting available to say which hosts are allowed. :( This plugin gives you that option. The default option for comparing domains is a straight string compare. You have the option to allow the use of regular expressions, which can be used for things like allowing all subdomains to communicate with eachother. This is most helpful when trying to import media content from one WordPress instance to another from within an internal network.

== Installation ==

1. Upload `WP-Allowed-Hosts` to the `wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add hosts (comma seperated) to the Settings > Allowed Hosts

The allowed hosts setting can be enabled at the Network level (multisite) or at an individual site level. If enabled at the Network level then the setting will only be shown under the network site regardless of the plugin being enabled at the site level. The Network level setting takes precedence over the individual site setting.

== Frequently Asked Questions ==

= I enabled the plugin on a site but I don't see the setting? =

The setting is located under WP Admin's Setting > Allow Hosts. If you don't see it you are probably running a Network (multisite) version of wordpress and the plugin is enabled at the network level. Enabling at the Network level will take precedence.

== Changelog ==

= 1.0.7 =
* Update to readme to reflect compatibility.

= 1.0.6 =
* Removed usage of `create_function()` for better compatibility with newer versions of PHP.

= 1.0.5 =
* Fixed bug where delete_option() was being called on plugin activation instead of add_option()
* Added success notifications when network-level settings are modified and saved
* Code standards/cleanup

= 1.0.4 =
* Readme update

= 1.0.3 =
* Added Network level security.

= 1.0.0 =
* First release
