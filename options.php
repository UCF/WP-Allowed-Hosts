<div class="wrap">
    <h2>Allowed Hosts</h2>
    <form method="post" action="options.php">
        <?php settings_fields('vdp-settings-group'); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Hosts</th>
                <td>
                    <textarea name="varnish-nodes"><?php echo get_option('allowed-domains'); ?></textarea>
                    <p>Enter domain names that this WordPress instance needs to communicate with. Regular expressions can be used. Separate multiple domains by commas.</p>
                </td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
</div>
