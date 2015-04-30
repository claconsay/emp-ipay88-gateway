/*
* iPay88 gateway add-on for Events Manager Pro
* @package   Events_Manager_iPay88
* @author Christopher Laconsay <claconsay[at]gmail[dot]com>
* @license GPL-2.0+ Christopher Laconsay
* @copyright 2015 claconsay 
*/

<?php 
/*
 * TODO
 * - modify settings page according to iPay88
 */
?>
    <table class="form-table">
        <tbody>
            <tr valign="top">
                <th scope="row"><?php _e('Test Mode', 'em-pro') ?></th>
                <td>	
                    <select name="sentry_testmode">
                        <?php
                        $methods = array(
                            'True' => 'True',
                            'False' => 'False'
                        );
                        $sentry_testmode = get_option('em_' . $this->gateway . '_testmode');
                        foreach ($methods as $key => $value) {
                            if ($sentry_testmode == $key) {
                                echo '<option value="' . $key . '" selected="selected">' . $value . '</option>';
                            } else {
                                echo '<option value="' . $key . '">' . $value . '</option>';
                            }
                        }
                        ?>
                    </select>
                    <br /><em><?php _e('Set to "True" if still on development.', 'em-pro'); ?></em>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Success Message', 'em-pro') ?></th>
                <td>
                    <input type="text" name="sentry_booking_feedback" value="<?php esc_attr_e(get_option('em_' . $this->gateway . "_booking_feedback")); ?>" style='width: 40em;' /><br />
                    <em><?php _e('The message that is shown to a user when a booking is successful whilst being redirected to Sentry for payment.', 'em-pro'); ?></em>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Success Free Message', 'em-pro') ?></th>
                <td>
                    <input type="text" name="sentry_booking_feedback_free" value="<?php esc_attr_e(get_option('em_' . $this->gateway . "_booking_feedback_free")); ?>" style='width: 40em;' /><br />
                    <em><?php _e('In some cases you offer a free ticket, this message will be shown and user won\'t be redirected to payment gateway.', 'em-pro'); ?></em>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Thank You Message', 'em-pro') ?></th>
                <td>
                    <input type="text" name="sentry_booking_feedback_thanks" value="<?php esc_attr_e(get_option('em_' . $this->gateway . "_booking_feedback_thanks")); ?>" style='width: 40em;' /><br />
                    <em><?php _e('If you choose to return users to the default Events Manager thank you page after a user has paid on Sentry, you can customize the thank you message here.', 'em-pro'); ?></em>
                </td>
            </tr>

        </tbody>
    </table>

    <h3><?php echo sprintf(__('%s Settings', 'em-pro'), 'Merchant'); ?></h3>	
    <table class="form-table">
        <tbody>
            <tr valign="top">
                <th scope="row"><?php _e('Version', 'em-pro') ?></th>
                <td><input type="text" name="sentry_version" value="<?php esc_attr_e(get_option('em_' . $this->gateway . "_version")); ?>" /><br /><em><?php _e('This is the version of the SENTRY Payment Gateway.', 'em-pro'); ?></em>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php _e('Merchant ID', 'em-pro') ?></th>
                <td><input type="text" name="sentry_merchantid" value="<?php esc_attr_e(get_option('em_' . $this->gateway . "_merchantid")); ?>" /><br /><em><?php _e('(will be provided by Maybank)', 'em-pro'); ?></em>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Acquirer ID', 'em-pro') ?></th>
                <td><input type="text" name="sentry_acquirerid" value="<?php esc_attr_e(get_option('em_' . $this->gateway . "_acquirerid")); ?>" /><br /><em><?php _e('(will be provided by Maybank)', 'em-pro'); ?></em>
                    <br />
                </td>
            </tr>	
            <tr valign="top">
                <th scope="row"><?php _e('Password', 'em-pro') ?></th>
                <td><input type="text" name="sentry_password" value="<?php esc_attr_e(get_option('em_' . $this->gateway . "_password")); ?>" /><br /><em><?php _e('This is the password included in the mailer that your provider supplied.', 'em-pro'); ?></em>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Currency', 'em-pro') ?></th>
                <td><?php echo esc_html(get_option('dbem_bookings_currency', 'USD')); ?><br /><i><?php echo sprintf(__('Set your currency in the <a href="%s">settings</a> page.', 'dbem'), EM_ADMIN_URL . '&amp;page=events-manager-options'); ?></i></td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Signature Method', 'em-pro') ?></th>
                <td>	
                    <select name="sentry_sigmethod">
                        <?php
                        $methods = array(
                            'MD5' => 'MD5',
                            'SHA1' => 'SHA-1'
                        );
                        $sentry_sigmethod = get_option('em_' . $this->gateway . '_sigmethod');
                        foreach ($methods as $key => $value) {
                            if ($sentry_sigmethod == $key) {
                                echo '<option value="' . $key . '" selected="selected">' . $value . '</option>';
                            } else {
                                echo '<option value="' . $key . '">' . $value . '</option>';
                            }
                        }
                        ?>
                    </select>
                    <br /><em><?php _e('Encryption method to be use.', 'em-pro'); ?></em>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Response URL', 'em-pro') ?></th>
                <td><input type="text" name="sentry_reponseurl" value="<?php esc_attr_e(get_option('em_' . $this->gateway . "_reponseurl")); ?>" style='width: 40em;' /><br /><em><?php _e('URL that will handle the response from Maybank. Must be in https. If unsure, please fill it in with the default value ', 'em-pro');
                        echo '<strong>' . $this->get_payment_return_url() . '<strong>'; ?></em>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Thank You URL', 'em-pro') ?></th>
                <td><input type="text" name="sentry_thankyouurl" value="<?php esc_attr_e(get_option('em_' . $this->gateway . "_thankyouurl")); ?>" style='width: 40em;' /><br /><em><?php _e('If you have used the default Reponse URL, user will be redirected to this page if payment is succesful. Create a new page and add the link here.)', 'em-pro'); ?></em>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Payment Cancelled  URL', 'em-pro') ?></th>
                <td><input type="text" name="sentry_paycancelled" value="<?php esc_attr_e(get_option('em_' . $this->gateway . "_paycancelled")); ?>" style='width: 40em;' /><br /><em><?php _e('User will be redirected to this page when they cancel the payment.', 'em-pro'); ?></em>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('RedirectLink URL', 'em-pro') ?></th>
                <td><input type="text" name="sentry_redirectlink" value="<?php esc_attr_e(get_option('em_' . $this->gateway . "_redirectlink")); ?>" style='width: 40em;' /><br /><em><?php _e('URL where merchant data will be sent (will be provided by Maybank)', 'em-pro'); ?></em>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('RedirectLink URL: Test Mode', 'em-pro') ?></th>
                <td><input type="text" name="sentry_redirectlink_test" value="<?php esc_attr_e(get_option('em_' . $this->gateway . "_redirectlink_test")); ?>" style='width: 40em;' /><br /><em><?php _e('Booking will be sent to this URL if set to test mode.', 'em-pro'); ?></em>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Delete Bookings Pending Payment', 'em-pro') ?></th>
                <td>
                    <input type="text" name="sentry_booking_timeout" style="width:50px;" value="<?php esc_attr_e(get_option('em_' . $this->gateway . "_booking_timeout")); ?>" style='width: 40em;' /> <?php _e('minutes', 'em-pro'); ?><br />
                    <em><?php _e('Once a booking is started and the user is taken to Sentry Payment Page, Events Manager stores a booking record in the database to identify the incoming payment. If you would like these bookings to expire after x minutes, please enter a value above.', 'em-pro'); ?></em>
                </td>
            </tr>
        </tbody>
    </table>