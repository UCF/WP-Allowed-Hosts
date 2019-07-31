<?php
/*
Plugin Name: WP Allowed Hosts
Description: Allow WordPress to communicate with other domains.
Version: 1.0.7
Author: Brandon Groves
License: GPL3
*/

class AH {

	public static $ALLOWED_HOSTS_NAME = 'allowed-hosts';
	public static $ALLOWED_HOSTS_REGEX_NAME = 'allowed-hosts-regex';

	public function __construct() {
		// Makes sure the plugin is defined before trying to use it
		if ( !function_exists( 'is_plugin_active_for_network' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}
		$hook = ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) ? 'network_' : '';
		add_action( "{$hook}admin_menu", function() { new AHSettings(); } );

		add_filter( 'http_request_host_is_external', array( $this, 'http_external_host_allowed' ), 10, 2 );
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
	}

	function activate() {
		if ( is_multisite() && isset( $_GET['networkwide'] ) && 1 == $_GET['networkwide'] ) {
			add_site_option( AH::$ALLOWED_HOSTS_NAME, '' );
			add_site_option( AH::$ALLOWED_HOSTS_REGEX_NAME, 0 );
		} else {
			add_option( AH::$ALLOWED_HOSTS_NAME, '' );
			add_option( AH::$ALLOWED_HOSTS_REGEX_NAME, 0 );
		}
	}

	function deactivate() {
		if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
			delete_site_option( AH::$ALLOWED_HOSTS_NAME );
			delete_site_option( AH::$ALLOWED_HOSTS_REGEX_NAME );
		} else {
			delete_option( AH::$ALLOWED_HOSTS_NAME );
			delete_option( AH::$ALLOWED_HOSTS_REGEX_NAME );
		}
	}

	function http_external_host_allowed( $is_external, $host ) {
		// Get site option if network activated
		$allowed_hosts = '';
		$allowed_hosts_regex = '';
		if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
			$allowed_hosts = get_site_option( 'allowed-hosts' );
			$allowed_hosts_regex = get_site_option( 'allowed-hosts-regex' );
		} else {
			$allowed_hosts = get_option( 'allowed-hosts' );
			$allowed_hosts_regex = get_option( 'allowed-hosts-regex' );
		}

		$is_allowed = false;
		if ( !empty( $allowed_hosts ) ) {
			foreach ( explode( ',', $allowed_hosts ) as $allowed_host ) {
				$allowed_host = trim( $allowed_host );
				if ( $allowed_hosts_regex ) {
					if ( preg_match( '/' . $allowed_host . '/', $host ) ) {
						$is_allowed = true;
						break;
					}
				} else {
					if ( strcmp( $allowed_host, $host ) == 0 ) {
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
		if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
			add_submenu_page(
				'settings.php',
				$this->page_title,
				$this->menu_title,
				$this->capability,
				$this->menu_slug,
				array( $this, 'settings_page' )
			);
		} else {
			add_options_page(
				$this->page_title,
				$this->menu_title,
				$this->capability,
				$this->menu_slug,
				array( $this, 'settings_page' )
			);
		}

		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function settings_page() {
		if ( !current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have permission to access this page.' ) );
		}

		// Save options manually only for multisite. POST action to options.php
		// in display_options_page will save the setting for individual websites
		if ( is_multisite()
			&& is_plugin_active_for_network( plugin_basename( __FILE__ ) )
			&& isset( $_POST['submit'] )
			&& isset( $_POST['_wpnonce'] )
			&& wp_verify_nonce( $_POST['_wpnonce'], 'allow-hosts-update' )
		) {
			// Remove slashes added by PHP or by WordPress
			$allowed_hosts = ( !get_magic_quotes_gpc() && !function_exists( 'wp_magic_quotes' ) ) ? $_POST['allowed-hosts'] : stripslashes( $_POST['allowed-hosts'] );
			$allowed_hosts_regex = (int)$_POST['allowed-hosts-regex'];

			// Update values.  Return whether or not the value in the db changed.
			$allowed_hosts_changed = update_site_option( AH::$ALLOWED_HOSTS_NAME, $allowed_hosts );
			$allowed_hosts_regex_changed = update_site_option( AH::$ALLOWED_HOSTS_REGEX_NAME, $allowed_hosts_regex );

			// If the value in the db changed for any field, display a success message
			if ( $allowed_hosts_changed || $allowed_hosts_regex_changed ) {
				echo '<div class="updated"><p>' . __( 'Settings saved.' ) . '</p></div>';
			}
		}

		$this->display_options_page();
	}

	public function display_options_page() {
		if ( !current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have permission to access this page.' ) );
		}

		$allowed_hosts = '';
		$allowed_hosts_regex = '';
		$post_action = '';
		if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
			$allowed_hosts = get_site_option( 'allowed-hosts' );
			$allowed_hosts_regex = get_site_option( 'allowed-hosts-regex' );
		} else {
			$allowed_hosts = get_option( 'allowed-hosts' );
			$allowed_hosts_regex = get_option( 'allowed-hosts-regex' );
			$post_action = 'action="options.php"';
		}

		ob_start();
	?>
        <div class="wrap">
            <h2>Allowed Hosts</h2>
            <form method="post" <?php echo $post_action; ?>>
            <?php ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) ? wp_nonce_field( 'allow-hosts-update' ) : settings_fields( 'ah-settings-group' ); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Hosts</th>
                        <td>
                            <textarea name="allowed-hosts"><?php echo $allowed_hosts; ?></textarea>
                            <br><br>
                            <input type="checkbox" name="allowed-hosts-regex" id="allowed-hosts-regex" value="1" <?php checked( $allowed_hosts_regex ); ?> />
                            <label for="allowed-hosts-regex">Compare hosts using regular expressions</label>
                            <br><br>
                            <p class="description">
                            	Enter domain names that this WordPress instance needs to communicate with. Separate
                            	multiple domains by commas. Delimiters are not needed for regular expressions since
                            	they are put in for you ('/'). For information about regular expressions please go to
                            	<a href="http://www.regular-expressions.info">http://www.regular-expressions.info</a>.
                            </p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
	<?php
		echo ob_get_clean();
	}

	public function register_settings() {
		register_setting( 'ah-settings-group', 'allowed-hosts' );
		register_setting( 'ah-settings-group', 'allowed-hosts-regex' );
	}

}

$ah = new AH();
?>
