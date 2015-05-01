<?php 
/*
* iPay88 gateway add-on for Events Manager Pro
* @package   Events_Manager_iPay88
* @author Christopher Laconsay <claconsay[at]gmail[dot]com>
* @license GPL-2.0+ Christopher Laconsay
* @copyright 2015 claconsay 
*/
?>
    <table class="form-table">
        <tbody>
            <tr valign="top">
                <th scope="row"><?php _e('Gateway Status', 'em-pro') ?></th>
                <td>	
                    <select name="ipay88_status">
                        <?php
                        $statuses = array(
                            'live' => 'Live',
                            'dev' => 'Development'
                        );
                        $ipay88_status = $this->get_option('status');
                        foreach ($statuses as $key => $value) {
                            if ($ipay88_status == $key) {
                                echo '<option value="' . $key . '" selected="selected">' . $value . '</option>';
                            } else {
                                echo '<option value="' . $key . '">' . $value . '</option>';
                            }
                        }
                        ?>
                    </select>
                    <br /><em><?php _e('Set this to Development if you are still testing the gateway.', 'em-pro'); ?></em>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Success Message', 'em-pro') ?></th>
                <td>
                    <input type="text" name="ipay88_booking_feedback" value="<?php esc_attr_e($this->get_option("booking_feedback")); ?>" style='width: 40em;' /><br />
                    <em><?php _e('The message that is shown to a user when a booking is successful whilst being redirected to iPay88 for payment.', 'em-pro'); ?></em>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Success Free Message', 'em-pro') ?></th>
                <td>
                    <input type="text" name="ipay88_booking_feedback_free" value="<?php esc_attr_e($this->get_option("booking_feedback_free")); ?>" style='width: 40em;' /><br />
                    <em><?php _e('In some cases you offer a free ticket, this message will be shown and user won\'t be redirected to payment gateway.', 'em-pro'); ?></em>
                </td>
            </tr>
        </tbody>
    </table>

    <h3><?php echo sprintf(__('%s Settings', 'em-pro'), 'Merchant'); ?></h3>	
    <table class="form-table">
        <tbody>
            <tr valign="top">
                <th scope="row"><?php _e('Merchant Code', 'em-pro') ?></th>
                <td><input type="text" name="ipay88_mercode" value="<?php esc_attr_e($this->get_option("mercode")); ?>" /><br />
                    <em><?php _e('Merchant code assigned by iPay88.', 'em-pro'); ?></em>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Merchant Key', 'em-pro') ?></th>
                <td><input type="text" name="ipay88_mercode" value="<?php esc_attr_e($this->get_option("mercode")); ?>" /><br />
                    <em><?php _e('Merchant key assigned by iPay88.', 'em-pro'); ?></em>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Payment ID', 'em-pro') ?></th>
                <td><input type="text" name="ipay88_payid" value="<?php esc_attr_e($this->get_option("payid")); ?>" /><br />
                    <em><?php _e('ID of payment method to be use. Recommended value: 2', 'em-pro'); ?></em>
                </td>
            </tr>
            <tr valign="top">
		<th scope="row"><?php _e('Currency', 'em-pro') ?></th>
		<td><?php echo esc_html(get_option('dbem_bookings_currency','MYR')); ?><br />
                    <em><?php echo sprintf(__('Set your currency in the <a href="%s">settings</a> page.','dbem'),EM_ADMIN_URL.'&amp;page=events-manager-options'); ?></em></td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Response URL', 'em-pro') ?></th>
                <td><input type="text" name="" value="<?php echo $this->get_payment_return_url(); ?>" disabled style='width: 40em;'/><br />
                    <em><?php _e('From the payment gateway page, transaction results will be sent to this URL and all the booking processes will be handled in this page.', 'em-pro');?></em>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Thank You URL', 'em-pro') ?></th>
                <td><input type="text" name="ipay88_thankyouurl" value="<?php esc_attr_e($this->get_option("thankyouurl")); ?>" style='width: 40em;' /><br />
                    <em><?php _e('Client will be redirected to this page if the payment is approved. Create a new page and add the link here.)', 'em-pro'); ?></em>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Payment Cancelled  URL', 'em-pro') ?></th>
                <td><input type="text" name="ipay88_paycancelled" value="<?php esc_attr_e($this->get_option("paycancelled")); ?>" style='width: 40em;' /><br />
                    <em><?php _e('Client will be redirected to this page in case they cancel or their payment didn\'t go through. Create a new page and add the link here.', 'em-pro'); ?></em>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Delete Bookings Pending Payment', 'em-pro') ?></th>
                <td>
                    <input type="text" name="ipay88_booking_timeout" style="width:50px;" value="<?php esc_attr_e($this->get_option("booking_timeout")); ?>" style='width: 40em;' /> <?php _e('minutes', 'em-pro'); ?><br />
                    <em><?php _e('Once a booking is started and the user is taken to iPay88 Payment Page, Events Manager stores a booking record in the database to identify the incoming payment. If you would like these bookings to expire after x minutes, please enter a value above.', 'em-pro'); ?></em>
                </td>
            </tr>
        </tbody>
    </table>