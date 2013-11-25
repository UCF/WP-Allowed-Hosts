=== Varnish Dependency Purge ===
Contributors: ucfwebcom
Tags: host, hosts, allow, filter
Requires at least: 3.7.1
Tested up to: 3.7.1
Stable tag: 1.0.2
License: GPLv3 or later
License URI: http://www.gnu.org/copyleft/gpl-3.0.html

This plugin purges allows local domains to communicate.

== Description ==

WordPress plugin that allows you to specify a domain names to be checked against the `http_request_host_is_external` filter. Hosts that start with 192.* or 10.* are checked against the allowed hosts based on a new security update WordPress introduced in 3.5.2 (https://github.com/WordPress/WordPress/commit/1ec392175ce5f0320072e7b195a8d091bccddefb). Unfortunately there is no setting available to say which hosts are allowed. :( This plugin gives you that option. The default option for comparing domains is a straight string compare. You have the option to allow the use of regular expressions, which can be used for things like allowing all subdomains to communicate with eachother. This is most helpful when trying to import media content from one WordPress instance to another from within an internal network.
