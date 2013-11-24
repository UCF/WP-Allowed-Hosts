<div class="wrap">
    <h2>Allowed Hosts</h2>
    <form method="post" action="options.php">
        <?php settings_fields('ah-settings-group'); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Hosts</th>
                <td>
                    <textarea name="allowed-hosts"><?php echo get_option('allowed-hosts'); ?></textarea>
                    <br />
                    <input type="checkbox" name="allowed-hosts-regex" id="allowed-hosts-regex" value="1" <?php checked(get_option('allowed-hosts-regex')); ?> /> <label for="allowed-hosts-regex">Compare hosts using regular expressions</label>
                    <p>Enter domain names that this WordPress instance needs to communicate with. Separate multiple domains by commas. Delimiters are not needed for regular expressions since they are put in for you ('/'). For information about regular expressions please go to <a href="http://www.regular-expressions.info">http://www.regular-expressions.info</a>.</p>
                </td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
</div>
